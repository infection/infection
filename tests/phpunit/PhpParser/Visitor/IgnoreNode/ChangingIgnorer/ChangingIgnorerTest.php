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

namespace Infection\Tests\PhpParser\Visitor\IgnoreNode\ChangingIgnorer;

use Infection\PhpParser\Visitor\IgnoreNode\ChangingIgnorer;
use Infection\PhpParser\Visitor\NonMutableNodesIgnorerVisitor;
use Infection\Tests\PhpParser\Visitor\VisitorTestCase;
use Infection\Tests\TestingUtility\PhpParser\Visitor\MarkTraversedNodesAsVisitedVisitor\MarkTraversedNodesAsVisitedVisitor;
use PhpParser\NodeTraverser;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(ChangingIgnorer::class)]
final class ChangingIgnorerTest extends VisitorTestCase
{
    /**
     * @param array<positive-int|0> $ignoredNodeIds
     */
    #[DataProvider('nodeProvider')]
    public function test_it_annotates_ignored_nodes(
        string $code,
        array $ignoredNodeIds,
        string $expected,
    ): void {
        $nodes = $this->parse($code);

        $changingIgnorer = new ChangingIgnorer();

        $this->addIdsToNodes($nodes);
        (new NodeTraverser(
            new MarkNodesAsIgnoredVisitor(
                $ignoredNodeIds,
                $changingIgnorer,
            ),
            new NonMutableNodesIgnorerVisitor([$changingIgnorer]),
            new MarkTraversedNodesAsVisitedVisitor(),
        ))->traverse($nodes);

        $actual = $this->dumper->dump($nodes);

        $this->assertSame($expected, $actual);
    }

    public static function nodeProvider(): iterable
    {
        $codeSample = <<<'PHP'
            <?php

            class Service
            {
                public function method1() {}

                public function method2() {
                    if (true) {
                        echo 'something';
                    } else {
                        echo 'yell at the clouds';
                    }
                }

                public function method3() {}
            }

            PHP;

        // Sanity check
        yield 'no code ignored' => [
            $codeSample,
            [],
            <<<'AST'
                array(
                    0: Stmt_Class(
                        name: Identifier(
                            nodeId: 1
                        )
                        stmts: array(
                            0: Stmt_ClassMethod(
                                name: Identifier(
                                    nodeId: 3
                                )
                                nodeId: 2
                            )
                            1: Stmt_ClassMethod(
                                name: Identifier(
                                    nodeId: 5
                                )
                                stmts: array(
                                    0: Stmt_If(
                                        cond: Expr_ConstFetch(
                                            name: Name(
                                                nodeId: 8
                                            )
                                            nodeId: 7
                                        )
                                        stmts: array(
                                            0: Stmt_Echo(
                                                exprs: array(
                                                    0: Scalar_String(
                                                        kind: KIND_SINGLE_QUOTED (1)
                                                        rawValue: 'something'
                                                        nodeId: 10
                                                    )
                                                )
                                                nodeId: 9
                                            )
                                        )
                                        else: Stmt_Else(
                                            stmts: array(
                                                0: Stmt_Echo(
                                                    exprs: array(
                                                        0: Scalar_String(
                                                            kind: KIND_SINGLE_QUOTED (1)
                                                            rawValue: 'yell at the clouds'
                                                            nodeId: 13
                                                        )
                                                    )
                                                    nodeId: 12
                                                )
                                            )
                                            nodeId: 11
                                        )
                                        nodeId: 6
                                    )
                                )
                                nodeId: 4
                            )
                            2: Stmt_ClassMethod(
                                name: Identifier(
                                    nodeId: 15
                                )
                                nodeId: 14
                            )
                        )
                        nodeId: 0
                    )
                )
                AST,
        ];

        yield 'ignore various statements' => [
            $codeSample,
            [
                2,  // first method
                9,  // body of the `if` statement of the second method
            ],
            <<<'AST'
                array(
                    0: Stmt_Class(
                        name: Identifier(
                            nodeId: 1
                        )
                        stmts: array(
                            0: <skipped>
                            1: Stmt_ClassMethod(
                                name: Identifier(
                                    nodeId: 5
                                )
                                stmts: array(
                                    0: Stmt_If(
                                        cond: Expr_ConstFetch(
                                            name: Name(
                                                nodeId: 8
                                            )
                                            nodeId: 7
                                        )
                                        stmts: array(
                                            0: <skipped>
                                        )
                                        else: Stmt_Else(
                                            stmts: array(
                                                0: Stmt_Echo(
                                                    exprs: array(
                                                        0: Scalar_String(
                                                            kind: KIND_SINGLE_QUOTED (1)
                                                            rawValue: 'yell at the clouds'
                                                            nodeId: 13
                                                        )
                                                    )
                                                    nodeId: 12
                                                )
                                            )
                                            nodeId: 11
                                        )
                                        nodeId: 6
                                    )
                                )
                                nodeId: 4
                            )
                            2: Stmt_ClassMethod(
                                name: Identifier(
                                    nodeId: 15
                                )
                                nodeId: 14
                            )
                        )
                        nodeId: 0
                    )
                )
                AST,
        ];
    }
}
