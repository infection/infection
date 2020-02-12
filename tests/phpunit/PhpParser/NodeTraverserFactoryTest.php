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

use function array_map;
use Infection\PhpParser\NodeTraverserFactory;
use Infection\PhpParser\Visitor\FullyQualifiedClassNameVisitor;
use Infection\PhpParser\Visitor\IgnoreNode\AbstractMethodIgnorer;
use Infection\PhpParser\Visitor\IgnoreNode\InterfaceIgnorer;
use Infection\PhpParser\Visitor\NonMutableNodesIgnorerVisitor;
use Infection\PhpParser\Visitor\ParentConnectorVisitor;
use Infection\PhpParser\Visitor\ReflectionVisitor;
use Infection\Tests\Fixtures\PhpParser\FakeIgnorer;
use Infection\Tests\Fixtures\PhpParser\FakeVisitor;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionProperty;

final class NodeTraverserFactoryTest extends TestCase
{
    private static $visitorsReflection;

    public function test_it_can_create_a_traverser(): void
    {
        $traverser = (new NodeTraverserFactory())->create(new FakeVisitor(), []);

        $visitors = array_map(
            'get_class',
            self::getVisitorReflection()->getValue($traverser)
        );

        $this->assertSame(
            [
                NonMutableNodesIgnorerVisitor::class,
                NameResolver::class,
                ParentConnectorVisitor::class,
                FullyQualifiedClassNameVisitor::class,
                ReflectionVisitor::class,
                FakeVisitor::class,
            ],
            $visitors
        );
    }

    public function test_it_can_create_a_traverser_with_node_ignorers(): void
    {
        $traverser = (new NodeTraverserFactory())->create(
            new FakeVisitor(),
            [
                new FakeIgnorer(),
                new FakeIgnorer(),
            ]
        );

        $visitors = self::getVisitorReflection()->getValue($traverser);

        $visitorClasses = array_map('get_class', $visitors);

        $this->assertSame(
            [
                NonMutableNodesIgnorerVisitor::class,
                NameResolver::class,
                ParentConnectorVisitor::class,
                FullyQualifiedClassNameVisitor::class,
                ReflectionVisitor::class,
                FakeVisitor::class,
            ],
            $visitorClasses
        );

        $nodeIgnorersReflection = (new ReflectionClass(NonMutableNodesIgnorerVisitor::class))->getProperty('nodeIgnorers');
        $nodeIgnorersReflection->setAccessible(true);

        $actualNodeIgnorers = array_map(
            'get_class',
            $nodeIgnorersReflection->getValue($visitors[0])
        );

        $this->assertSame(
            [
                FakeIgnorer::class,
                FakeIgnorer::class,
                InterfaceIgnorer::class,
                AbstractMethodIgnorer::class,
            ],
            $actualNodeIgnorers
        );
    }

    private static function getVisitorReflection(): ReflectionProperty
    {
        if (self::$visitorsReflection !== null) {
            return self::$visitorsReflection;
        }

        self::$visitorsReflection = (new ReflectionClass(NodeTraverser::class))->getProperty('visitors');
        self::$visitorsReflection->setAccessible(true);

        return self::$visitorsReflection;
    }
}
