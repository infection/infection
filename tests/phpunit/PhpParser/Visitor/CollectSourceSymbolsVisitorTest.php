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

use Infection\PhpParser\NodeTraverserFactory;
use Infection\PhpParser\Visitor\CollectSourceSymbolsVisitor;
use Infection\Tests\PhpParser\Visitor\VisitorTestCase\VisitorTestCase;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(CollectSourceSymbolsVisitor::class)]
final class CollectSourceSymbolsVisitorTest extends VisitorTestCase
{
    /**
     * @param list<string> $expected
     */
    #[DataProvider('symbolsProvider')]
    public function test_it_collects_source_symbols(
        string $code,
        array $expected,
    ): void {
        $nodes = $this->parse($code);

        $visitor = new CollectSourceSymbolsVisitor();

        $traverser = NodeTraverserFactory::createSourceSymbolTraverser($visitor);
        $traverser->traverse($nodes);

        $actual = $visitor->getSymbols();

        $this->assertSame($expected, $actual);
        $this->assertNoActiveScope($visitor);
    }

    public static function symbolsProvider(): iterable
    {
        yield 'global class and directly declared methods' => [
            <<<'PHP'
                <?php

                class Example extends ParentClass
                {
                    use Behavior;

                    public const VALUE = 1;

                    public string $property;

                    public function run() {}

                    protected function prepare() {}

                    private static function finish() {}
                }
                PHP,
            [
                'Example',
                'Example::run',
                'Example::prepare',
                'Example::finish',
            ],
        ];

        yield 'namespaced class-like declarations skip interfaces and abstract methods' => [
            <<<'PHP'
                <?php

                namespace App;

                interface Contract
                {
                    public function run();
                }

                trait Behavior
                {
                    public function shared() {}
                }

                abstract class Example
                {
                    abstract public function run();

                    public function concrete() {}
                }

                enum Status
                {
                    case Ready;

                    public function label() {}
                }
                PHP,
            [
                'App\Behavior',
                'App\Behavior::shared',
                'App\Example',
                'App\Example::concrete',
                'App\Status',
                'App\Status::label',
            ],
        ];

        yield 'nested declarations preserve source order and enclosing class context' => [
            <<<'PHP'
                <?php

                namespace App {
                    class Example
                    {
                        public function first()
                        {
                            class Inner
                            {
                                public function nested() {}
                            }

                            new class {
                                public function hidden() {}
                            };
                        }

                        public function last() {}
                    }
                }

                namespace Other {
                    class Example
                    {
                        public function run() {}
                    }
                }
                PHP,
            [
                'App\Example',
                'App\Example::first',
                'App\Inner',
                'App\Inner::nested',
                'App\Example::last',
                'Other\Example',
                'Other\Example::run',
            ],
        ];

        yield 'no named source symbols' => [
            <<<'PHP'
                <?php

                const VALUE = 1;

                function helper() {}

                $closure = function () {};
                $arrow = fn () => 1;

                new class {
                    public function hidden() {}
                };
                PHP,
            [],
        ];
    }

    public function test_it_defers_resolving_symbol_names_until_they_are_requested(): void
    {
        $class = $this->createStub(Class_::class);
        $method = new ClassMethod('run');

        $expected = ['App\Example', 'App\Example::run'];

        $visitor = new CollectSourceSymbolsVisitor();

        $visitor->enterNode($class);
        $visitor->enterNode($method);
        $visitor->leaveNode($method);
        $visitor->leaveNode($class);

        // We supply the name after traversal. Returning it below confirms that
        // symbol names are resolved when requested, rather than during traversal.
        $class->namespacedName = new Name('App\Example');

        $actual = $visitor->getSymbols();

        $this->assertSame($expected, $actual);
    }

    public function test_it_restores_enclosing_scopes_after_an_anonymous_class(): void
    {
        $visitor = new CollectSourceSymbolsVisitor();

        // Nodes representing the PHP code:
        //
        // ```
        // class Outer
        // {
        //     public function run()
        //     {
        //         new class {
        //             public function hidden() {}
        //         };
        //     }
        // }
        // ```
        //
        $class = new Class_('Outer');
        $class->namespacedName = new Name('App\Outer');
        $method = new ClassMethod('run');
        $anonymousClass = new Class_(null);
        $anonymousMethod = new ClassMethod('hidden');

        $visitor->beforeTraverse([]);
        $visitor->enterNode($class);
        $visitor->enterNode($method);
        $visitor->enterNode($anonymousClass);
        $visitor->enterNode($anonymousMethod);

        $this->assertEquals(
            $anonymousClass,
            $visitor->findClass(),
            'An anonymous class must shadow the enclosing named class during symbol selection.',
        );
        $this->assertEquals(
            $anonymousMethod,
            $visitor->findMethod(),
            'Selection must use the innermost declared method.',
        );

        $visitor->leaveNode($anonymousMethod);

        $this->assertEquals(
            $method,
            $visitor->findMethod(),
        );

        $visitor->leaveNode($anonymousClass);

        $this->assertEquals(
            $class,
            $visitor->findClass(),
            'Leaving a nested class must restore the enclosing class.',
        );
        $this->assertEquals(
            $method,
            $visitor->findMethod(),
            'Leaving a nested method must restore the enclosing method.',
        );
        $visitor->beforeTraverse([]);

        $this->assertNoActiveScope($visitor);
    }

    public function test_it_memoizes_symbol_names_until_a_declaration_is_added(): void
    {
        $visitor = new CollectSourceSymbolsVisitor();

        $nameMock = $this->createMock(Name::class);
        $nameMock
            ->expects($this->exactly(3))
            ->method('toString')
            ->with()
            ->willReturn('App\Example')
        ;

        $class = new Class_('Example');
        $class->namespacedName = $nameMock;

        $this->assertSame(
            [],
            $visitor->getSymbols(),
        );

        $visitor->enterNode($class);

        $this->assertSame(
            ['App\Example'],
            $visitor->getSymbols(),
        );
        $this->assertSame(
            ['App\Example'],
            $visitor->getSymbols(),
        );

        // Adding the method here invalidates the while symbol list.
        $visitor->enterNode(new ClassMethod('run'));

        $this->assertSame(
            ['App\Example', 'App\Example::run'],
            $visitor->getSymbols(),
        );
        $this->assertSame(
            ['App\Example', 'App\Example::run'],
            $visitor->getSymbols(),
        );
    }

    private function assertNoActiveScope(CollectSourceSymbolsVisitor $visitor): void
    {
        $this->assertNull(
            $visitor->findClass(),
            'Class context must be cleared after leaving the outermost class or resetting traversal.',
        );
        $this->assertNull(
            $visitor->findMethod(),
            'Method context must be cleared after leaving the outermost method or resetting traversal.',
        );
    }
}
