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

namespace Infection\Tests\NewSrc\PhpParser\Visitor;

use Infection\Tests\NewSrc\PhpParser\AstTestCase;
use Infection\Tests\NewSrc\PhpParser\Visitor\LabelAridCodeVisitor\CommentAridNodeDetector;
use newSrc\AST\NodeVisitor\AddNodesSymbolsVisitor;
use newSrc\AST\NodeVisitor\DetectAridCodeVisitor;
use newSrc\AST\NodeVisitor\NameResolverFactory;
use newSrc\AST\SymbolResolver;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\ParentConnectingVisitor;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(AddNodesSymbolsVisitor::class)]
#[CoversClass(SymbolResolver::class)]
final class AddNodesSymbolsVisitorTest extends AstTestCase
{
    #[DataProvider('nodeProvider')]
    public function test_it_annotates_excluded_nodes_and_stops_the_traversal(
        string $code,
        string $expected,
    ): void {
        $nodes = $this->createParser()->parse($code);

        $traverser = new NodeTraverser(
            NameResolverFactory::create(),
            new ParentConnectingVisitor(),
            new AddNodesSymbolsVisitor(
                new SymbolResolver(),
            ),
        );
        $traverser->traverse($nodes);

        $actual = $this->dumper->dump(
            $nodes,
            onlyVisitedNodes: false,
        );

        $this->assertSame($expected, $actual);
    }

    public static function nodeProvider(): iterable
    {
        // TODO: a lot more cases to add

        yield 'namespace declarations' => [
            <<<'PHP'
                <?php

                namespace {
                    $x0 = '';
                }

                namespace Infection\Tests\Namespace1 {
                    $x1 = '';
                }

                namespace Infection\Tests\Namespace2 {
                    $x2 = '';
                }

                PHP,
            <<<'OUT'
            array(
                0: Stmt_Namespace(
                    stmts: array(
                        0: Stmt_Expression(
                            expr: Expr_Assign(
                                var: Expr_Variable(
                                    SYMBOL: array(
                                    )
                                )
                                expr: Scalar_String(
                                    kind: KIND_SINGLE_QUOTED (1)
                                    rawValue: ''
                                    SYMBOL: array(
                                    )
                                )
                                SYMBOL: array(
                                )
                            )
                            SYMBOL: array(
                            )
                        )
                    )
                    kind: 2
                    SYMBOL: array(
                    )
                )
                1: Stmt_Namespace(
                    name: Name(
                        SYMBOL: array(
                            0: Infection\Tests\Namespace1
                        )
                    )
                    stmts: array(
                        0: Stmt_Expression(
                            expr: Expr_Assign(
                                var: Expr_Variable(
                                    SYMBOL: array(
                                        0: Infection\Tests\Namespace1
                                    )
                                )
                                expr: Scalar_String(
                                    kind: KIND_SINGLE_QUOTED (1)
                                    rawValue: ''
                                    SYMBOL: array(
                                        0: Infection\Tests\Namespace1
                                    )
                                )
                                SYMBOL: array(
                                    0: Infection\Tests\Namespace1
                                )
                            )
                            SYMBOL: array(
                                0: Infection\Tests\Namespace1
                            )
                        )
                    )
                    kind: 2
                    SYMBOL: array(
                        0: Infection\Tests\Namespace1
                    )
                )
                2: Stmt_Namespace(
                    name: Name(
                        SYMBOL: array(
                            0: Infection\Tests\Namespace2
                        )
                    )
                    stmts: array(
                        0: Stmt_Expression(
                            expr: Expr_Assign(
                                var: Expr_Variable(
                                    SYMBOL: array(
                                        0: Infection\Tests\Namespace2
                                    )
                                )
                                expr: Scalar_String(
                                    kind: KIND_SINGLE_QUOTED (1)
                                    rawValue: ''
                                    SYMBOL: array(
                                        0: Infection\Tests\Namespace2
                                    )
                                )
                                SYMBOL: array(
                                    0: Infection\Tests\Namespace2
                                )
                            )
                            SYMBOL: array(
                                0: Infection\Tests\Namespace2
                            )
                        )
                    )
                    kind: 2
                    SYMBOL: array(
                        0: Infection\Tests\Namespace2
                    )
                )
            )
            OUT,
        ];

        yield 'namespaced function declaration' => [
            <<<'PHP'
                <?php

                namespace Infection\Tests\Virtual;

                $x0 = '';

                function namespaced_function_example() {
                    $x1 = '';
                    $x2 = '';
                }

                $x3 = '';

                PHP,
            <<<'OUT'
            array(
                0: Stmt_Namespace(
                    name: Name(
                        SYMBOL: array(
                            0: Infection\Tests\Virtual
                        )
                    )
                    stmts: array(
                        0: Stmt_Expression(
                            expr: Expr_Assign(
                                var: Expr_Variable(
                                    SYMBOL: array(
                                        0: Infection\Tests\Virtual
                                    )
                                )
                                expr: Scalar_String(
                                    kind: KIND_SINGLE_QUOTED (1)
                                    rawValue: ''
                                    SYMBOL: array(
                                        0: Infection\Tests\Virtual
                                    )
                                )
                                SYMBOL: array(
                                    0: Infection\Tests\Virtual
                                )
                            )
                            SYMBOL: array(
                                0: Infection\Tests\Virtual
                            )
                        )
                        1: Stmt_Function(
                            name: Identifier(
                                SYMBOL: array(
                                    0: Infection\Tests\Virtual
                                    1: namespaced_function_example
                                )
                            )
                            stmts: array(
                                0: Stmt_Expression(
                                    expr: Expr_Assign(
                                        var: Expr_Variable(
                                            SYMBOL: array(
                                                0: Infection\Tests\Virtual
                                                1: namespaced_function_example
                                            )
                                        )
                                        expr: Scalar_String(
                                            kind: KIND_SINGLE_QUOTED (1)
                                            rawValue: ''
                                            SYMBOL: array(
                                                0: Infection\Tests\Virtual
                                                1: namespaced_function_example
                                            )
                                        )
                                        SYMBOL: array(
                                            0: Infection\Tests\Virtual
                                            1: namespaced_function_example
                                        )
                                    )
                                    SYMBOL: array(
                                        0: Infection\Tests\Virtual
                                        1: namespaced_function_example
                                    )
                                )
                                1: Stmt_Expression(
                                    expr: Expr_Assign(
                                        var: Expr_Variable(
                                            SYMBOL: array(
                                                0: Infection\Tests\Virtual
                                                1: namespaced_function_example
                                            )
                                        )
                                        expr: Scalar_String(
                                            kind: KIND_SINGLE_QUOTED (1)
                                            rawValue: ''
                                            SYMBOL: array(
                                                0: Infection\Tests\Virtual
                                                1: namespaced_function_example
                                            )
                                        )
                                        SYMBOL: array(
                                            0: Infection\Tests\Virtual
                                            1: namespaced_function_example
                                        )
                                    )
                                    SYMBOL: array(
                                        0: Infection\Tests\Virtual
                                        1: namespaced_function_example
                                    )
                                )
                            )
                            SYMBOL: array(
                                0: Infection\Tests\Virtual
                                1: namespaced_function_example
                            )
                        )
                        2: Stmt_Expression(
                            expr: Expr_Assign(
                                var: Expr_Variable(
                                    SYMBOL: array(
                                        0: Infection\Tests\Virtual
                                    )
                                )
                                expr: Scalar_String(
                                    kind: KIND_SINGLE_QUOTED (1)
                                    rawValue: ''
                                    SYMBOL: array(
                                        0: Infection\Tests\Virtual
                                    )
                                )
                                SYMBOL: array(
                                    0: Infection\Tests\Virtual
                                )
                            )
                            SYMBOL: array(
                                0: Infection\Tests\Virtual
                            )
                        )
                    )
                    kind: 1
                    SYMBOL: array(
                        0: Infection\Tests\Virtual
                    )
                )
            )
            OUT,
        ];

        yield 'class declaration' => [
            <<<'PHP'
                <?php

                namespace Infection\Tests\Virtual;

                $x0 = '';

                class A {
                    private $x1 = '';
                }

                $x2 = '';

                PHP,
            <<<'OUT'
            array(
                0: Stmt_Namespace(
                    name: Name(
                        SYMBOL: array(
                            0: Infection\Tests\Virtual
                        )
                    )
                    stmts: array(
                        0: Stmt_Expression(
                            expr: Expr_Assign(
                                var: Expr_Variable(
                                    SYMBOL: array(
                                        0: Infection\Tests\Virtual
                                    )
                                )
                                expr: Scalar_String(
                                    kind: KIND_SINGLE_QUOTED (1)
                                    rawValue: ''
                                    SYMBOL: array(
                                        0: Infection\Tests\Virtual
                                    )
                                )
                                SYMBOL: array(
                                    0: Infection\Tests\Virtual
                                )
                            )
                            SYMBOL: array(
                                0: Infection\Tests\Virtual
                            )
                        )
                        1: Stmt_Class(
                            name: Identifier(
                                SYMBOL: array(
                                    0: Infection\Tests\Virtual
                                    1: Infection\Tests\Virtual\A
                                )
                            )
                            stmts: array(
                                0: Stmt_Property(
                                    props: array(
                                        0: PropertyItem(
                                            name: VarLikeIdentifier(
                                                SYMBOL: array(
                                                    0: Infection\Tests\Virtual
                                                    1: Infection\Tests\Virtual\A
                                                )
                                            )
                                            default: Scalar_String(
                                                kind: KIND_SINGLE_QUOTED (1)
                                                rawValue: ''
                                                SYMBOL: array(
                                                    0: Infection\Tests\Virtual
                                                    1: Infection\Tests\Virtual\A
                                                )
                                            )
                                            SYMBOL: array(
                                                0: Infection\Tests\Virtual
                                                1: Infection\Tests\Virtual\A
                                            )
                                        )
                                    )
                                    SYMBOL: array(
                                        0: Infection\Tests\Virtual
                                        1: Infection\Tests\Virtual\A
                                    )
                                )
                            )
                            SYMBOL: array(
                                0: Infection\Tests\Virtual
                                1: Infection\Tests\Virtual\A
                            )
                        )
                        2: Stmt_Expression(
                            expr: Expr_Assign(
                                var: Expr_Variable(
                                    SYMBOL: array(
                                        0: Infection\Tests\Virtual
                                    )
                                )
                                expr: Scalar_String(
                                    kind: KIND_SINGLE_QUOTED (1)
                                    rawValue: ''
                                    SYMBOL: array(
                                        0: Infection\Tests\Virtual
                                    )
                                )
                                SYMBOL: array(
                                    0: Infection\Tests\Virtual
                                )
                            )
                            SYMBOL: array(
                                0: Infection\Tests\Virtual
                            )
                        )
                    )
                    kind: 1
                    SYMBOL: array(
                        0: Infection\Tests\Virtual
                    )
                )
            )
            OUT,
        ];

        yield 'class method declarations' => [
            <<<'PHP'
                <?php

                namespace Infection\Tests\Virtual;

                $x0 = '';

                class A {
                    function __construct() {
                        $x1 = '';
                    }

                    function methodA() {
                        $x2 = '';
                    }
                }

                $x3 = '';

                PHP,
            <<<'OUT'
            array(
                0: Stmt_Namespace(
                    name: Name(
                        SYMBOL: array(
                            0: Infection\Tests\Virtual
                        )
                    )
                    stmts: array(
                        0: Stmt_Expression(
                            expr: Expr_Assign(
                                var: Expr_Variable(
                                    SYMBOL: array(
                                        0: Infection\Tests\Virtual
                                    )
                                )
                                expr: Scalar_String(
                                    kind: KIND_SINGLE_QUOTED (1)
                                    rawValue: ''
                                    SYMBOL: array(
                                        0: Infection\Tests\Virtual
                                    )
                                )
                                SYMBOL: array(
                                    0: Infection\Tests\Virtual
                                )
                            )
                            SYMBOL: array(
                                0: Infection\Tests\Virtual
                            )
                        )
                        1: Stmt_Class(
                            name: Identifier(
                                SYMBOL: array(
                                    0: Infection\Tests\Virtual
                                    1: Infection\Tests\Virtual\A
                                )
                            )
                            stmts: array(
                                0: Stmt_ClassMethod(
                                    name: Identifier(
                                        SYMBOL: array(
                                            0: Infection\Tests\Virtual
                                            1: Infection\Tests\Virtual\A
                                            2: Infection\Tests\Virtual\A::__construct()
                                        )
                                    )
                                    stmts: array(
                                        0: Stmt_Expression(
                                            expr: Expr_Assign(
                                                var: Expr_Variable(
                                                    SYMBOL: array(
                                                        0: Infection\Tests\Virtual
                                                        1: Infection\Tests\Virtual\A
                                                        2: Infection\Tests\Virtual\A::__construct()
                                                    )
                                                )
                                                expr: Scalar_String(
                                                    kind: KIND_SINGLE_QUOTED (1)
                                                    rawValue: ''
                                                    SYMBOL: array(
                                                        0: Infection\Tests\Virtual
                                                        1: Infection\Tests\Virtual\A
                                                        2: Infection\Tests\Virtual\A::__construct()
                                                    )
                                                )
                                                SYMBOL: array(
                                                    0: Infection\Tests\Virtual
                                                    1: Infection\Tests\Virtual\A
                                                    2: Infection\Tests\Virtual\A::__construct()
                                                )
                                            )
                                            SYMBOL: array(
                                                0: Infection\Tests\Virtual
                                                1: Infection\Tests\Virtual\A
                                                2: Infection\Tests\Virtual\A::__construct()
                                            )
                                        )
                                    )
                                    SYMBOL: array(
                                        0: Infection\Tests\Virtual
                                        1: Infection\Tests\Virtual\A
                                        2: Infection\Tests\Virtual\A::__construct()
                                    )
                                )
                                1: Stmt_ClassMethod(
                                    name: Identifier(
                                        SYMBOL: array(
                                            0: Infection\Tests\Virtual
                                            1: Infection\Tests\Virtual\A
                                            2: Infection\Tests\Virtual\A::methodA()
                                        )
                                    )
                                    stmts: array(
                                        0: Stmt_Expression(
                                            expr: Expr_Assign(
                                                var: Expr_Variable(
                                                    SYMBOL: array(
                                                        0: Infection\Tests\Virtual
                                                        1: Infection\Tests\Virtual\A
                                                        2: Infection\Tests\Virtual\A::methodA()
                                                    )
                                                )
                                                expr: Scalar_String(
                                                    kind: KIND_SINGLE_QUOTED (1)
                                                    rawValue: ''
                                                    SYMBOL: array(
                                                        0: Infection\Tests\Virtual
                                                        1: Infection\Tests\Virtual\A
                                                        2: Infection\Tests\Virtual\A::methodA()
                                                    )
                                                )
                                                SYMBOL: array(
                                                    0: Infection\Tests\Virtual
                                                    1: Infection\Tests\Virtual\A
                                                    2: Infection\Tests\Virtual\A::methodA()
                                                )
                                            )
                                            SYMBOL: array(
                                                0: Infection\Tests\Virtual
                                                1: Infection\Tests\Virtual\A
                                                2: Infection\Tests\Virtual\A::methodA()
                                            )
                                        )
                                    )
                                    SYMBOL: array(
                                        0: Infection\Tests\Virtual
                                        1: Infection\Tests\Virtual\A
                                        2: Infection\Tests\Virtual\A::methodA()
                                    )
                                )
                            )
                            SYMBOL: array(
                                0: Infection\Tests\Virtual
                                1: Infection\Tests\Virtual\A
                            )
                        )
                        2: Stmt_Expression(
                            expr: Expr_Assign(
                                var: Expr_Variable(
                                    SYMBOL: array(
                                        0: Infection\Tests\Virtual
                                    )
                                )
                                expr: Scalar_String(
                                    kind: KIND_SINGLE_QUOTED (1)
                                    rawValue: ''
                                    SYMBOL: array(
                                        0: Infection\Tests\Virtual
                                    )
                                )
                                SYMBOL: array(
                                    0: Infection\Tests\Virtual
                                )
                            )
                            SYMBOL: array(
                                0: Infection\Tests\Virtual
                            )
                        )
                    )
                    kind: 1
                    SYMBOL: array(
                        0: Infection\Tests\Virtual
                    )
                )
            )
            OUT,
        ];
    }
}
