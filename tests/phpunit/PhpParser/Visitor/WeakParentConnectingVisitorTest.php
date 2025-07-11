<?php
/**
 * This code is licensed under the BSD 3-Clause License.
 *
 * Copyright (c) 2017, Maks Rafalko
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * * Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 *
 * * Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 * * Neither the name of the copyright holder nor the names of its
 *   contributors may be used to endorse or promote products derived from
 *   this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

declare(strict_types=1);

namespace Infection\Tests\PhpParser\Visitor;

use function gc_collect_cycles;
use Infection\PhpParser\Visitor\ParentConnector;
use Infection\PhpParser\Visitor\WeakParentConnectingVisitor;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Nop;
use PhpParser\NodeTraverser;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use WeakReference;

#[CoversClass(WeakParentConnectingVisitor::class)]
final class WeakParentConnectingVisitorTest extends TestCase
{
    public function test_it_always_uses_weak_references(): void
    {
        $parent = new Class_('TestClass');
        $child = new ClassMethod('testMethod');
        $parent->stmts = [$child];

        $traverser = new NodeTraverser();
        $traverser->addVisitor(new WeakParentConnectingVisitor());
        $traverser->traverse([$parent]);

        $this->assertSame($parent, ParentConnector::getParent($child));
    }

    public function test_it_creates_weak_references_that_can_be_garbage_collected(): void
    {
        $parent = new Class_('TestClass');
        $child = new ClassMethod('testMethod');
        $parent->stmts = [$child];

        $traverser = new NodeTraverser();
        $traverser->addVisitor(new WeakParentConnectingVisitor());
        $traverser->traverse([$parent]);

        $this->assertTrue($child->hasAttribute('weak_parent'));
        $weakRef = $child->getAttribute('weak_parent');
        $this->assertInstanceOf(WeakReference::class, $weakRef);
        $this->assertSame($parent, $weakRef->get());

        $parent = null;
        gc_collect_cycles();

        $this->assertNull($weakRef->get());
    }

    public function test_it_works_with_parent_connector_find_parent(): void
    {
        $parent = new Class_('TestClass');
        $child = new ClassMethod('testMethod');
        $parent->stmts = [$child];

        $traverser = new NodeTraverser();
        $traverser->addVisitor(new WeakParentConnectingVisitor());
        $traverser->traverse([$parent]);

        $this->assertSame($parent, ParentConnector::findParent($child));
    }

    public function test_it_handles_nested_structures(): void
    {
        $grandParent = new Class_('GrandParent');
        $parent = new ClassMethod('parent');
        $child = new Nop();

        $grandParent->stmts = [$parent];
        $parent->stmts = [$child];

        $traverser = new NodeTraverser();
        $traverser->addVisitor(new WeakParentConnectingVisitor());
        $traverser->traverse([$grandParent]);

        $this->assertSame($grandParent, ParentConnector::getParent($parent));
        $this->assertSame($parent, ParentConnector::getParent($child));
    }
}
