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

namespace Infection\Tests\TestingUtility\PhpParser\Visitor\AddIdToTraversedNodesVisitor;

use Infection\Tests\PhpParser\Visitor\VisitorTestCase;
use PhpParser\NodeTraverser;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(AddIdToTraversedNodesVisitor::class)]
final class AddIdToTraversedNodesVisitorTest extends VisitorTestCase
{
    #[DataProvider('nodeProvider')]
    public function test_it_adds_an_id_to_each_node(
        string $code,
        string $expected,
    ): void {
        $nodes = $this->parser->parse($code);

        $this->assertNotNull($nodes);

        (new NodeTraverser(new AddIdToTraversedNodesVisitor()))->traverse($nodes);

        $this->assertSame(
            $expected,
            $this->dumper->dump($nodes, onlyVisitedNodes: false),
        );
    }

    public static function nodeProvider(): iterable
    {
        yield [
            <<<'PHP'
                <?php

                namespace Infection\Tests\Virtual;

                $engine = new Engine(
                    static fn () => 'first',
                    static fn () => 'second',
                    static fn () => 'third',
                );

                PHP,
            <<<'AST'
                array(
                    0: Stmt_Namespace(
                        name: Name(
                            nodeId: 1
                        )
                        stmts: array(
                            0: Stmt_Expression(
                                expr: Expr_Assign(
                                    var: Expr_Variable(
                                        nodeId: 4
                                    )
                                    expr: Expr_New(
                                        class: Name(
                                            nodeId: 6
                                        )
                                        args: array(
                                            0: Arg(
                                                value: Expr_ArrowFunction(
                                                    expr: Scalar_String(
                                                        kind: KIND_SINGLE_QUOTED (1)
                                                        rawValue: 'first'
                                                        nodeId: 9
                                                    )
                                                    nodeId: 8
                                                )
                                                nodeId: 7
                                            )
                                            1: Arg(
                                                value: Expr_ArrowFunction(
                                                    expr: Scalar_String(
                                                        kind: KIND_SINGLE_QUOTED (1)
                                                        rawValue: 'second'
                                                        nodeId: 12
                                                    )
                                                    nodeId: 11
                                                )
                                                nodeId: 10
                                            )
                                            2: Arg(
                                                value: Expr_ArrowFunction(
                                                    expr: Scalar_String(
                                                        kind: KIND_SINGLE_QUOTED (1)
                                                        rawValue: 'third'
                                                        nodeId: 15
                                                    )
                                                    nodeId: 14
                                                )
                                                nodeId: 13
                                            )
                                        )
                                        nodeId: 5
                                    )
                                    nodeId: 3
                                )
                                nodeId: 2
                            )
                        )
                        kind: 1
                        nodeId: 0
                    )
                )
                AST,
        ];
    }
}
