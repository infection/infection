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

namespace Infection\Tests\PhpParser\Visitor\NonMutableNodesIgnorerVisitor;

use Infection\PhpParser\Visitor\NonMutableNodesIgnorerVisitor;
use Infection\Tests\PhpParser\Visitor\VisitorTestCase;
use Infection\Tests\TestingUtility\PhpParser\Visitor\MarkTraversedNodesAsVisitedVisitor\MarkTraversedNodesAsVisitedVisitor;
use PhpParser\NodeTraverser;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(NonMutableNodesIgnorerVisitor::class)]
final class NonMutableNodesIgnorerVisitorTest extends VisitorTestCase
{
    /**
     * @param array<positive-int|0> $ignoredNodeIds
     */
    #[DataProvider('nodeProvider')]
    public function test_it_annotates_the_next_nodes(
        string $code,
        array $ignoredNodeIds,
        string $expected,
    ): void {
        $nodes = $this->parse($code);

        $this->addIdsToNodes($nodes);
        (new NodeTraverser(
            new NonMutableNodesIgnorerVisitor([
                new IdNodeIgnorer($ignoredNodeIds),
            ]),
            new MarkTraversedNodesAsVisitedVisitor(),
        ))->traverse($nodes);

        $actual = $this->dumper->dump($nodes);

        $this->assertSame($expected, $actual);
    }

    public static function nodeProvider(): iterable
    {
        $codeSample = <<<'PHP'
            <?php

            $a = 1;

            class Greeter
            {
                var $b = 2;

                public function greet1(
                    $c = 3,
                ): void
                {
                    $d = 4;
                }

                public function greet2(
                    $e = 5,
                ): void
                {
                    $f = 6;
                }
            }
            PHP;

        // Sanity check
        yield 'no code ignored' => [
            $codeSample,
            [],
            <<<'AST'
                array(
                    0: Stmt_Expression(
                        expr: Expr_Assign(
                            var: Expr_Variable(
                                nodeId: 2
                            )
                            expr: Scalar_Int(
                                rawValue: 1
                                kind: KIND_DEC (10)
                                nodeId: 3
                            )
                            nodeId: 1
                        )
                        nodeId: 0
                    )
                    1: Stmt_Class(
                        name: Identifier(
                            nodeId: 5
                        )
                        stmts: array(
                            0: Stmt_Property(
                                props: array(
                                    0: PropertyItem(
                                        name: VarLikeIdentifier(
                                            nodeId: 8
                                        )
                                        default: Scalar_Int(
                                            rawValue: 2
                                            kind: KIND_DEC (10)
                                            nodeId: 9
                                        )
                                        nodeId: 7
                                    )
                                )
                                nodeId: 6
                            )
                            1: Stmt_ClassMethod(
                                name: Identifier(
                                    nodeId: 11
                                )
                                params: array(
                                    0: Param(
                                        var: Expr_Variable(
                                            nodeId: 13
                                        )
                                        default: Scalar_Int(
                                            rawValue: 3
                                            kind: KIND_DEC (10)
                                            nodeId: 14
                                        )
                                        nodeId: 12
                                    )
                                )
                                returnType: Identifier(
                                    nodeId: 15
                                )
                                stmts: array(
                                    0: Stmt_Expression(
                                        expr: Expr_Assign(
                                            var: Expr_Variable(
                                                nodeId: 18
                                            )
                                            expr: Scalar_Int(
                                                rawValue: 4
                                                kind: KIND_DEC (10)
                                                nodeId: 19
                                            )
                                            nodeId: 17
                                        )
                                        nodeId: 16
                                    )
                                )
                                nodeId: 10
                            )
                            2: Stmt_ClassMethod(
                                name: Identifier(
                                    nodeId: 21
                                )
                                params: array(
                                    0: Param(
                                        var: Expr_Variable(
                                            nodeId: 23
                                        )
                                        default: Scalar_Int(
                                            rawValue: 5
                                            kind: KIND_DEC (10)
                                            nodeId: 24
                                        )
                                        nodeId: 22
                                    )
                                )
                                returnType: Identifier(
                                    nodeId: 25
                                )
                                stmts: array(
                                    0: Stmt_Expression(
                                        expr: Expr_Assign(
                                            var: Expr_Variable(
                                                nodeId: 28
                                            )
                                            expr: Scalar_Int(
                                                rawValue: 6
                                                kind: KIND_DEC (10)
                                                nodeId: 29
                                            )
                                            nodeId: 27
                                        )
                                        nodeId: 26
                                    )
                                )
                                nodeId: 20
                            )
                        )
                        nodeId: 4
                    )
                )
                AST,
        ];

        yield 'ignore some code' => [
            $codeSample,
            [
                10, // method greet()
            ],
            <<<'AST'
                array(
                    0: Stmt_Expression(
                        expr: Expr_Assign(
                            var: Expr_Variable(
                                nodeId: 2
                            )
                            expr: Scalar_Int(
                                rawValue: 1
                                kind: KIND_DEC (10)
                                nodeId: 3
                            )
                            nodeId: 1
                        )
                        nodeId: 0
                    )
                    1: Stmt_Class(
                        name: Identifier(
                            nodeId: 5
                        )
                        stmts: array(
                            0: Stmt_Property(
                                props: array(
                                    0: PropertyItem(
                                        name: VarLikeIdentifier(
                                            nodeId: 8
                                        )
                                        default: Scalar_Int(
                                            rawValue: 2
                                            kind: KIND_DEC (10)
                                            nodeId: 9
                                        )
                                        nodeId: 7
                                    )
                                )
                                nodeId: 6
                            )
                            1: <skipped>
                            2: Stmt_ClassMethod(
                                name: Identifier(
                                    nodeId: 21
                                )
                                params: array(
                                    0: Param(
                                        var: Expr_Variable(
                                            nodeId: 23
                                        )
                                        default: Scalar_Int(
                                            rawValue: 5
                                            kind: KIND_DEC (10)
                                            nodeId: 24
                                        )
                                        nodeId: 22
                                    )
                                )
                                returnType: Identifier(
                                    nodeId: 25
                                )
                                stmts: array(
                                    0: Stmt_Expression(
                                        expr: Expr_Assign(
                                            var: Expr_Variable(
                                                nodeId: 28
                                            )
                                            expr: Scalar_Int(
                                                rawValue: 6
                                                kind: KIND_DEC (10)
                                                nodeId: 29
                                            )
                                            nodeId: 27
                                        )
                                        nodeId: 26
                                    )
                                )
                                nodeId: 20
                            )
                        )
                        nodeId: 4
                    )
                )
                AST,
        ];
    }
}
