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

namespace Infection\Tests\PhpParser\Visitor\ExcludeNonSelectedSourceNodesVisitor;

use Infection\Configuration\SourceSymbol\SourceSymbolSelector;
use Infection\PhpParser\Visitor\ExcludeNonSelectedSourceNodesVisitor;
use Infection\PhpParser\Visitor\LabelNodesAsEligibleVisitor;
use Infection\PhpParser\Visitor\MarkTraversedNodesAsVisitedVisitor;
use Infection\PhpParser\Visitor\NameResolverFactory;
use Infection\Source\Matcher\SourceSymbolMatcher;
use Infection\Tests\PhpParser\Visitor\VisitorTestCase\VisitorTestCase;
use PhpParser\Node;
use PhpParser\NodeFinder;
use PhpParser\NodeTraverser;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(ExcludeNonSelectedSourceNodesVisitor::class)]
#[CoversClass(SourceSymbolMatcher::class)]
final class ExcludeNonSelectedSourceNodesVisitorTest extends VisitorTestCase
{
    public function test_it_applies_method_selection_to_lexically_nested_function_like_nodes(): void
    {
        $nodes = $this->parse(<<<'PHP'
            <?php

            function outside(): int
            {
                return 0;
            }

            final class Example
            {
                public function selected(): void
                {
                    $closure = static function (): int {
                        return 1;
                    };
                    $arrow = static fn (): int => 2;

                    function nestedInSelected(): int
                    {
                        return 3;
                    }
                }

                public function unselected(): void
                {
                    $closure = static function (): int {
                        return 4;
                    };
                    $arrow = static fn (): int => 5;

                    function nestedInUnselected(): int
                    {
                        return 6;
                    }
                }
            }
            PHP);

        (new NodeTraverser(
            new LabelNodesAsEligibleVisitor(),
            NameResolverFactory::create(),
            new ExcludeNonSelectedSourceNodesVisitor(
                new SourceSymbolMatcher([
                    new SourceSymbolSelector('Example', 'selected', null),
                ]),
            ),
        ))->traverse($nodes);

        $functionLikes = (new NodeFinder())->findInstanceOf($nodes, Node\FunctionLike::class);

        $this->assertFalse(LabelNodesAsEligibleVisitor::isEligible($functionLikes[0]), 'top-level named function');
        $this->assertTrue(LabelNodesAsEligibleVisitor::isEligible($functionLikes[1]), 'selected method');
        $this->assertTrue(LabelNodesAsEligibleVisitor::isEligible($functionLikes[2]), 'closure in selected method');
        $this->assertTrue(LabelNodesAsEligibleVisitor::isEligible($functionLikes[3]), 'arrow function in selected method');
        $this->assertTrue(LabelNodesAsEligibleVisitor::isEligible($functionLikes[4]), 'named function in selected method');
        $this->assertFalse(LabelNodesAsEligibleVisitor::isEligible($functionLikes[5]), 'unselected method');
        $this->assertFalse(LabelNodesAsEligibleVisitor::isEligible($functionLikes[6]), 'closure in unselected method');
        $this->assertFalse(LabelNodesAsEligibleVisitor::isEligible($functionLikes[7]), 'arrow function in unselected method');
        $this->assertFalse(LabelNodesAsEligibleVisitor::isEligible($functionLikes[8]), 'named function in unselected method');
    }

    /**
     * @param list<SourceSymbolSelector> $selectors
     */
    #[DataProvider('scenarioProvider')]
    public function test_it_excludes_non_selected_source_nodes(
        string $code,
        array $selectors,
        string $expected,
    ): void {
        $nodes = $this->parse($code);

        (new NodeTraverser(
            new LabelNodesAsEligibleVisitor(),
            NameResolverFactory::create(),
            new ExcludeNonSelectedSourceNodesVisitor(
                new SourceSymbolMatcher($selectors),
            ),
            new MarkTraversedNodesAsVisitedVisitor(),
        ))->traverse($nodes);

        $actual = $this->dumper->dump($nodes);

        $this->assertSame($expected, $actual);
    }

    public static function scenarioProvider(): iterable
    {
        yield 'nodes outside the selected method' => [
            <<<'PHP'
                <?php
                namespace App;

                final class Mailer
                {
                    public function send(): int
                    {
                        return 1;
                    }

                    public function receive(): int
                    {
                        return 2;
                    }
                }
                PHP,
            [
                new SourceSymbolSelector(
                    'Mailer',
                    'send',
                    null,
                ),
            ],
            <<<'AST'
                array(
                    0: Stmt_Namespace(
                        name: Name(
                            eligible: false
                        )
                        stmts: array(
                            0: Stmt_Class(
                                name: Identifier(
                                    eligible: false
                                )
                                stmts: array(
                                    0: Stmt_ClassMethod(
                                        name: Identifier(
                                            eligible: true
                                        )
                                        returnType: Identifier(
                                            eligible: true
                                        )
                                        stmts: array(
                                            0: Stmt_Return(
                                                expr: Scalar_Int(
                                                    eligible: true
                                                    kind: KIND_DEC (10)
                                                    rawValue: 1
                                                )
                                                eligible: true
                                            )
                                        )
                                        eligible: true
                                    )
                                    1: Stmt_ClassMethod(
                                        name: Identifier(
                                            eligible: false
                                        )
                                        returnType: Identifier(
                                            eligible: false
                                        )
                                        stmts: array(
                                            0: Stmt_Return(
                                                expr: Scalar_Int(
                                                    eligible: false
                                                    kind: KIND_DEC (10)
                                                    rawValue: 2
                                                )
                                                eligible: false
                                            )
                                        )
                                        eligible: false
                                    )
                                )
                                eligible: false
                            )
                        )
                        eligible: false
                        kind: 1
                    )
                )
                AST,
        ];

        yield 'a short class name selects every matching declaration' => [
            <<<'PHP'
                <?php

                namespace App {
                    final class Differ
                    {
                        public function diff(): int
                        {
                            return 1;
                        }
                    }
                }

                namespace Vendor {
                    final class Differ
                    {
                        public function diff(): int
                        {
                            return 2;
                        }
                    }
                }
                PHP,
            [
                new SourceSymbolSelector(
                    'Differ',
                    'diff',
                    null,
                ),
            ],
            <<<'AST'
                array(
                    0: Stmt_Namespace(
                        name: Name(
                            eligible: false
                        )
                        stmts: array(
                            0: Stmt_Class(
                                name: Identifier(
                                    eligible: false
                                )
                                stmts: array(
                                    0: Stmt_ClassMethod(
                                        name: Identifier(
                                            eligible: true
                                        )
                                        returnType: Identifier(
                                            eligible: true
                                        )
                                        stmts: array(
                                            0: Stmt_Return(
                                                expr: Scalar_Int(
                                                    eligible: true
                                                    kind: KIND_DEC (10)
                                                    rawValue: 1
                                                )
                                                eligible: true
                                            )
                                        )
                                        eligible: true
                                    )
                                )
                                eligible: false
                            )
                        )
                        eligible: false
                        kind: 2
                    )
                    1: Stmt_Namespace(
                        name: Name(
                            eligible: false
                        )
                        stmts: array(
                            0: Stmt_Class(
                                name: Identifier(
                                    eligible: false
                                )
                                stmts: array(
                                    0: Stmt_ClassMethod(
                                        name: Identifier(
                                            eligible: true
                                        )
                                        returnType: Identifier(
                                            eligible: true
                                        )
                                        stmts: array(
                                            0: Stmt_Return(
                                                expr: Scalar_Int(
                                                    eligible: true
                                                    kind: KIND_DEC (10)
                                                    rawValue: 2
                                                )
                                                eligible: true
                                            )
                                        )
                                        eligible: true
                                    )
                                )
                                eligible: false
                            )
                        )
                        eligible: false
                        kind: 2
                    )
                )
                AST,
        ];

        yield 'multiple selectors are combined as a union' => [
            <<<'PHP'
                <?php
                namespace App;

                final class Differ
                {
                    public function diff(): int
                    {
                        return 1;
                    }

                    public function diffToArray(): array
                    {
                        return [];
                    }

                    public function other(): int
                    {
                        return 2;
                    }
                }
                PHP,
            [
                new SourceSymbolSelector(
                    'Differ',
                    'diff',
                    null,
                ),
                new SourceSymbolSelector(
                    'App\\Differ',
                    'diffToArray',
                    null,
                ),
            ],
            <<<'AST'
                array(
                    0: Stmt_Namespace(
                        name: Name(
                            eligible: false
                        )
                        stmts: array(
                            0: Stmt_Class(
                                name: Identifier(
                                    eligible: false
                                )
                                stmts: array(
                                    0: Stmt_ClassMethod(
                                        name: Identifier(
                                            eligible: true
                                        )
                                        returnType: Identifier(
                                            eligible: true
                                        )
                                        stmts: array(
                                            0: Stmt_Return(
                                                expr: Scalar_Int(
                                                    eligible: true
                                                    kind: KIND_DEC (10)
                                                    rawValue: 1
                                                )
                                                eligible: true
                                            )
                                        )
                                        eligible: true
                                    )
                                    1: Stmt_ClassMethod(
                                        name: Identifier(
                                            eligible: true
                                        )
                                        returnType: Identifier(
                                            eligible: true
                                        )
                                        stmts: array(
                                            0: Stmt_Return(
                                                expr: Expr_Array(
                                                    eligible: true
                                                    kind: KIND_SHORT (2)
                                                )
                                                eligible: true
                                            )
                                        )
                                        eligible: true
                                    )
                                    2: Stmt_ClassMethod(
                                        name: Identifier(
                                            eligible: false
                                        )
                                        returnType: Identifier(
                                            eligible: false
                                        )
                                        stmts: array(
                                            0: Stmt_Return(
                                                expr: Scalar_Int(
                                                    eligible: false
                                                    kind: KIND_DEC (10)
                                                    rawValue: 2
                                                )
                                                eligible: false
                                            )
                                        )
                                        eligible: false
                                    )
                                )
                                eligible: false
                            )
                        )
                        eligible: false
                        kind: 1
                    )
                )
                AST,
        ];

        yield 'an anonymous class does not inherit the enclosing symbol context' => [
            <<<'PHP'
                <?php
                namespace App;

                final class Differ
                {
                    public function diff(): int
                    {
                        $value = new class {
                            public function nested(): int
                            {
                                return 1;
                            }
                        };

                        return 2;
                    }
                }
                PHP,
            [
                new SourceSymbolSelector(
                    'Differ',
                    'diff',
                    null,
                ),
            ],
            <<<'AST'
                array(
                    0: Stmt_Namespace(
                        name: Name(
                            eligible: false
                        )
                        stmts: array(
                            0: Stmt_Class(
                                name: Identifier(
                                    eligible: false
                                )
                                stmts: array(
                                    0: Stmt_ClassMethod(
                                        name: Identifier(
                                            eligible: true
                                        )
                                        returnType: Identifier(
                                            eligible: true
                                        )
                                        stmts: array(
                                            0: Stmt_Expression(
                                                expr: Expr_Assign(
                                                    var: Expr_Variable(
                                                        eligible: true
                                                    )
                                                    expr: Expr_New(
                                                        class: Stmt_Class(
                                                            stmts: array(
                                                                0: Stmt_ClassMethod(
                                                                    name: Identifier(
                                                                        eligible: false
                                                                    )
                                                                    returnType: Identifier(
                                                                        eligible: false
                                                                    )
                                                                    stmts: array(
                                                                        0: Stmt_Return(
                                                                            expr: Scalar_Int(
                                                                                eligible: false
                                                                                kind: KIND_DEC (10)
                                                                                rawValue: 1
                                                                            )
                                                                            eligible: false
                                                                        )
                                                                    )
                                                                    eligible: false
                                                                )
                                                            )
                                                            eligible: false
                                                        )
                                                        eligible: true
                                                    )
                                                    eligible: true
                                                )
                                                eligible: true
                                            )
                                            1: Stmt_Return(
                                                expr: Scalar_Int(
                                                    eligible: true
                                                    kind: KIND_DEC (10)
                                                    rawValue: 2
                                                )
                                                eligible: true
                                            )
                                        )
                                        eligible: true
                                    )
                                )
                                eligible: false
                            )
                        )
                        eligible: false
                        kind: 1
                    )
                )
                AST,
        ];
    }
}
