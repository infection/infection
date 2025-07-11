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

namespace Infection\Tests\PhpParser;

use Infection\PhpParser\NodeTraverserFactory;
use Infection\PhpParser\Visitor\ParentConnector;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Expression;
use PhpParser\NodeVisitorAbstract;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(NodeTraverserFactory::class)]
#[CoversClass(ParentConnector::class)]
final class NodeTraverserIntegrationTest extends TestCase
{
    public function test_factory_creates_traverser_with_weak_references_that_work_with_parent_connector(): void
    {
        $factory = new NodeTraverserFactory();

        // Create a simple visitor that doesn't interfere with traversal
        $visitor = new class extends NodeVisitorAbstract {
            // No-op visitor
        };

        $traverser = $factory->create($visitor, []);

        // Create a parent-child AST structure
        $variable = new Variable('x');
        $parent = new Expression($variable);
        $child = $parent->expr; // The Variable inside the Expression

        $ast = [$parent];

        // Traverse the AST - this should set up parent connections using WeakReferences
        $traverser->traverse($ast);

        // Now test that ParentConnector works with the WeakReferences set up by the factory
        $retrievedParent = ParentConnector::findParent($child);

        $this->assertSame($parent, $retrievedParent, 'ParentConnector should retrieve the parent set by NodeTraverserFactory');

        // Also test the getParent method
        $retrievedParentViaGet = ParentConnector::getParent($child);
        $this->assertSame($parent, $retrievedParentViaGet, 'ParentConnector::getParent should work with factory-created traverser');
    }
}
