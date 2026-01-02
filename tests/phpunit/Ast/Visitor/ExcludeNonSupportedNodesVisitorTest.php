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

namespace Infection\Tests\Ast\Visitor;

use Infection\Ast\Metadata\Annotation;
use Infection\Ast\NodeVisitor\ExcludeNonSupportedNodesVisitor;
use Infection\PhpParser\Visitor\ReflectionVisitor;
use Infection\Tests\Ast\AstTestCase;
use Infection\Tests\Ast\Visitor\MarkTraversedNodesAsVisitedVisitor\MarkTraversedNodesAsVisitedVisitor;
use Infection\Tests\Ast\Visitor\RemoveUndesiredAttributesVisitor\RemoveUndesiredAttributesVisitor;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\ParentConnectingVisitor;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(ExcludeNonSupportedNodesVisitor::class)]
final class ExcludeNonSupportedNodesVisitorTest extends AstTestCase
{
    #[DataProvider('nodeProvider')]
    public function test_it_annotates_non_supported_nodes(
        string $code,
        string $expected,
    ): void {
        $nodes = $this->createParser()->parse($code);

        $traverser = new NodeTraverser(
            new ParentConnectingVisitor(),
            new ReflectionVisitor(),
            new ExcludeNonSupportedNodesVisitor(),
            new MarkTraversedNodesAsVisitedVisitor(),
        );
        $traverser->traverse($nodes);

        // Do the clean-up in a separate traverse to not interfere
        // with visitors depending on each others attributes.
        $traverser = new NodeTraverser(
            new RemoveUndesiredAttributesVisitor(
                Annotation::NOT_SUPPORTED,
                MarkTraversedNodesAsVisitedVisitor::VISITED_ATTRIBUTE,
            ),
        );
        $traverser->traverse($nodes);

        $actual = $this->dumper->dump($nodes);

        $this->assertSame($expected, $actual);
    }

    public static function nodeProvider(): iterable
    {
        yield 'nominal' => [
            <<<'PHP'
                <?php

                namespace Infection\Tests\Virtual;

                if ($GLOBALS['skip'] === true) {
                    return;
                }

                function greet(
                    string $greeting = 'Hello world!',
                ): void {
                    if (mt_rand(0, 10) < 5) {
                        echo $greeting;
                    }
                }

                class GreatService {
                    const ALPHABET = [
                        'α',
                        'β',
                        'γ',
                        'δ',
                        'ε',
                        'ζ',
                    ];

                    function yieldSomeLetters() {
                        for ($i = 0; $i < 10; $i++) {
                            $key = array_rand(self::ALPHABET);

                            yield $key => self::ALPHABET[$key];
                        }
                    }
                }

                \class_alias('Humbug\Infection\Tests\Virtual\GreatService', 'Infection\Tests\Virtual\GreatService', \false);

                PHP,
            <<<'OUT'
                array(
                    0: Stmt_Namespace(
                        name: Name(
                            NOT_SUPPORTED: 5
                        )
                        stmts: array(
                            0: Stmt_If(
                                cond: Expr_BinaryOp_Identical(
                                    left: Expr_ArrayDimFetch(
                                        var: Expr_Variable(
                                            NOT_SUPPORTED: 5
                                        )
                                        dim: Scalar_String(
                                            NOT_SUPPORTED: 5
                                        )
                                        NOT_SUPPORTED: 5
                                    )
                                    right: Expr_ConstFetch(
                                        name: Name(
                                            NOT_SUPPORTED: 5
                                        )
                                        NOT_SUPPORTED: 5
                                    )
                                    NOT_SUPPORTED: 5
                                )
                                stmts: array(
                                    0: Stmt_Return(
                                        NOT_SUPPORTED: 5
                                    )
                                )
                                NOT_SUPPORTED: 5
                            )
                            1: Stmt_Function(
                                name: Identifier(
                                    NOT_SUPPORTED: 5
                                )
                                params: array(
                                    0: Param(
                                        type: Identifier(
                                            NOT_SUPPORTED: 5
                                        )
                                        var: Expr_Variable(
                                            NOT_SUPPORTED: 5
                                        )
                                        default: Scalar_String(
                                            NOT_SUPPORTED: 5
                                        )
                                        NOT_SUPPORTED: 5
                                    )
                                )
                                returnType: Identifier(
                                    NOT_SUPPORTED: 5
                                )
                                stmts: array(
                                    0: Stmt_If(
                                        cond: Expr_BinaryOp_Smaller(
                                            left: Expr_FuncCall(
                                                name: Name(
                                                    NOT_SUPPORTED: 5
                                                )
                                                args: array(
                                                    0: Arg(
                                                        value: Scalar_Int(
                                                            NOT_SUPPORTED: 5
                                                        )
                                                        NOT_SUPPORTED: 5
                                                    )
                                                    1: Arg(
                                                        value: Scalar_Int(
                                                            NOT_SUPPORTED: 5
                                                        )
                                                        NOT_SUPPORTED: 5
                                                    )
                                                )
                                                NOT_SUPPORTED: 5
                                            )
                                            right: Scalar_Int(
                                                NOT_SUPPORTED: 5
                                            )
                                            NOT_SUPPORTED: 5
                                        )
                                        stmts: array(
                                            0: Stmt_Echo(
                                                exprs: array(
                                                    0: Expr_Variable(
                                                        NOT_SUPPORTED: 5
                                                    )
                                                )
                                                NOT_SUPPORTED: 5
                                            )
                                        )
                                        NOT_SUPPORTED: 5
                                    )
                                )
                                NOT_SUPPORTED: 5
                            )
                            2: Stmt_Class(
                                name: Identifier(
                                    NOT_SUPPORTED: 5
                                )
                                stmts: array(
                                    0: Stmt_ClassConst(
                                        consts: array(
                                            0: Const(
                                                name: Identifier(
                                                    NOT_SUPPORTED: 5
                                                )
                                                value: Expr_Array(
                                                    items: array(
                                                        0: ArrayItem(
                                                            value: Scalar_String(
                                                                NOT_SUPPORTED: 5
                                                            )
                                                            NOT_SUPPORTED: 5
                                                        )
                                                        1: ArrayItem(
                                                            value: Scalar_String(
                                                                NOT_SUPPORTED: 5
                                                            )
                                                            NOT_SUPPORTED: 5
                                                        )
                                                        2: ArrayItem(
                                                            value: Scalar_String(
                                                                NOT_SUPPORTED: 5
                                                            )
                                                            NOT_SUPPORTED: 5
                                                        )
                                                        3: ArrayItem(
                                                            value: Scalar_String(
                                                                NOT_SUPPORTED: 5
                                                            )
                                                            NOT_SUPPORTED: 5
                                                        )
                                                        4: ArrayItem(
                                                            value: Scalar_String(
                                                                NOT_SUPPORTED: 5
                                                            )
                                                            NOT_SUPPORTED: 5
                                                        )
                                                        5: ArrayItem(
                                                            value: Scalar_String(
                                                                NOT_SUPPORTED: 5
                                                            )
                                                            NOT_SUPPORTED: 5
                                                        )
                                                    )
                                                    NOT_SUPPORTED: 5
                                                )
                                                NOT_SUPPORTED: 5
                                            )
                                        )
                                        NOT_SUPPORTED: 5
                                    )
                                    1: Stmt_ClassMethod(
                                        name: Identifier
                                        stmts: array(
                                            0: Stmt_For(
                                                init: array(
                                                    0: Expr_Assign(
                                                        var: Expr_Variable
                                                        expr: Scalar_Int
                                                    )
                                                )
                                                cond: array(
                                                    0: Expr_BinaryOp_Smaller(
                                                        left: Expr_Variable
                                                        right: Scalar_Int
                                                    )
                                                )
                                                loop: array(
                                                    0: Expr_PostInc(
                                                        var: Expr_Variable
                                                    )
                                                )
                                                stmts: array(
                                                    0: Stmt_Expression(
                                                        expr: Expr_Assign(
                                                            var: Expr_Variable
                                                            expr: Expr_FuncCall(
                                                                name: Name
                                                                args: array(
                                                                    0: Arg(
                                                                        value: Expr_ClassConstFetch(
                                                                            class: Name
                                                                            name: Identifier
                                                                        )
                                                                    )
                                                                )
                                                            )
                                                        )
                                                    )
                                                    1: Stmt_Expression(
                                                        expr: Expr_Yield(
                                                            key: Expr_Variable
                                                            value: Expr_ArrayDimFetch(
                                                                var: Expr_ClassConstFetch(
                                                                    class: Name
                                                                    name: Identifier
                                                                )
                                                                dim: Expr_Variable
                                                            )
                                                        )
                                                    )
                                                )
                                            )
                                        )
                                    )
                                )
                                NOT_SUPPORTED: 5
                            )
                            3: Stmt_Expression(
                                expr: Expr_FuncCall(
                                    name: Name_FullyQualified(
                                        NOT_SUPPORTED: 5
                                    )
                                    args: array(
                                        0: Arg(
                                            value: Scalar_String(
                                                NOT_SUPPORTED: 5
                                            )
                                            NOT_SUPPORTED: 5
                                        )
                                        1: Arg(
                                            value: Scalar_String(
                                                NOT_SUPPORTED: 5
                                            )
                                            NOT_SUPPORTED: 5
                                        )
                                        2: Arg(
                                            value: Expr_ConstFetch(
                                                name: Name_FullyQualified(
                                                    NOT_SUPPORTED: 5
                                                )
                                                NOT_SUPPORTED: 5
                                            )
                                            NOT_SUPPORTED: 5
                                        )
                                    )
                                    NOT_SUPPORTED: 5
                                )
                                NOT_SUPPORTED: 5
                            )
                        )
                        NOT_SUPPORTED: 5
                    )
                )
                OUT,
        ];
    }
}
