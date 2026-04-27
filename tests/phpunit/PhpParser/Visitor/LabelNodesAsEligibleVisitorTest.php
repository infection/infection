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

use Infection\PhpParser\Visitor\LabelNodesAsEligibleVisitor;
use Infection\PhpParser\Visitor\MarkTraversedNodesAsVisitedVisitor;
use Infection\Tests\PhpParser\Visitor\VisitorTestCase\VisitorTestCase;
use Infection\Tests\TestingUtility\PhpParser\Visitor\SkipNodesVisitor\SkipNodesVisitor;
use PhpParser\NodeTraverser;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(LabelNodesAsEligibleVisitor::class)]
final class LabelNodesAsEligibleVisitorTest extends VisitorTestCase
{
    /**
     * @param list<int> $skippedNodeIds
     */
    #[DataProvider('nodeProvider')]
    public function test_it_labels_visited_nodes_as_eligible(
        string $code,
        array $skippedNodeIds,
        string $expected,
    ): void {
        $nodes = $this->parse($code);

        $this->addIdsToNodes($nodes);

        $traverser = new NodeTraverser(
            new SkipNodesVisitor($skippedNodeIds),
            new LabelNodesAsEligibleVisitor(),
            new MarkTraversedNodesAsVisitedVisitor(),
        );
        $traverser->traverse($nodes);

        $actual = $this->dumper->dump($nodes);

        $this->assertSame($expected, $actual);
    }

    public static function nodeProvider(): iterable
    {
        yield 'no node skipped' => [
            <<<'PHP'
                <?php

                namespace Infection\Tests\Virtual;

                $engine = new Engine(
                    static fn () => 'first',
                    static fn () => 'second',
                    static fn () => 'third',
                );

                PHP,
            [],
            <<<'AST'
                array(
                    0: Stmt_Namespace(
                        name: Name(
                            eligible: true
                            nodeId: 1
                        )
                        stmts: array(
                            0: Stmt_Expression(
                                expr: Expr_Assign(
                                    var: Expr_Variable(
                                        eligible: true
                                        nodeId: 4
                                    )
                                    expr: Expr_New(
                                        class: Name(
                                            eligible: true
                                            nodeId: 6
                                        )
                                        args: array(
                                            0: Arg(
                                                value: Expr_ArrowFunction(
                                                    expr: Scalar_String(
                                                        eligible: true
                                                        kind: KIND_SINGLE_QUOTED (1)
                                                        nodeId: 9
                                                        rawValue: 'first'
                                                    )
                                                    eligible: true
                                                    nodeId: 8
                                                )
                                                eligible: true
                                                nodeId: 7
                                            )
                                            1: Arg(
                                                value: Expr_ArrowFunction(
                                                    expr: Scalar_String(
                                                        eligible: true
                                                        kind: KIND_SINGLE_QUOTED (1)
                                                        nodeId: 12
                                                        rawValue: 'second'
                                                    )
                                                    eligible: true
                                                    nodeId: 11
                                                )
                                                eligible: true
                                                nodeId: 10
                                            )
                                            2: Arg(
                                                value: Expr_ArrowFunction(
                                                    expr: Scalar_String(
                                                        eligible: true
                                                        kind: KIND_SINGLE_QUOTED (1)
                                                        nodeId: 15
                                                        rawValue: 'third'
                                                    )
                                                    eligible: true
                                                    nodeId: 14
                                                )
                                                eligible: true
                                                nodeId: 13
                                            )
                                        )
                                        eligible: true
                                        nodeId: 5
                                    )
                                    eligible: true
                                    nodeId: 3
                                )
                                eligible: true
                                nodeId: 2
                            )
                        )
                        eligible: true
                        kind: 1
                        nodeId: 0
                    )
                )
                AST,
        ];

        yield 'some nodes skipped' => [
            <<<'PHP'
                <?php

                namespace Infection\Tests\Virtual;

                $engine = new Engine(
                    static fn () => 'first',
                    static fn () => 'second',
                    static fn () => 'third',
                );

                PHP,
            [7, 11, 15],
            <<<'AST'
                array(
                    0: Stmt_Namespace(
                        name: Name(
                            eligible: true
                            nodeId: 1
                        )
                        stmts: array(
                            0: Stmt_Expression(
                                expr: Expr_Assign(
                                    var: Expr_Variable(
                                        eligible: true
                                        nodeId: 4
                                    )
                                    expr: Expr_New(
                                        class: Name(
                                            eligible: true
                                            nodeId: 6
                                        )
                                        args: array(
                                            0: <skipped>
                                            1: Arg(
                                                value: <skipped>
                                                eligible: true
                                                nodeId: 10
                                            )
                                            2: Arg(
                                                value: Expr_ArrowFunction(
                                                    expr: <skipped>
                                                    eligible: true
                                                    nodeId: 14
                                                )
                                                eligible: true
                                                nodeId: 13
                                            )
                                        )
                                        eligible: true
                                        nodeId: 5
                                    )
                                    eligible: true
                                    nodeId: 3
                                )
                                eligible: true
                                nodeId: 2
                            )
                        )
                        eligible: true
                        kind: 1
                        nodeId: 0
                    )
                )
                AST,
        ];
    }
}
