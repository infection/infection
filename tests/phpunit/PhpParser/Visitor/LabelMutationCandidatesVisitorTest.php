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

use function array_flip;
use function array_intersect_key;
use Infection\PhpParser\Visitor\AddIdToTraversedNodesVisitor\AddIdToTraversedNodesVisitor;
use Infection\PhpParser\Visitor\LabelMutationCandidatesVisitor;
use Infection\PhpParser\Visitor\LabelNodesAsEligibleVisitor;
use Infection\PhpParser\Visitor\MarkTraversedNodesAsVisitedVisitor;
use Infection\PhpParser\Visitor\ReflectionVisitor;
use Infection\Tests\PhpParser\Visitor\VisitorTestCase\VisitorTestCase;
use Infection\Tests\TestingUtility\PhpParser\Visitor\KeepOnlyDesiredAttributesVisitor\KeepOnlyDesiredAttributesVisitor;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(LabelMutationCandidatesVisitor::class)]
final class LabelMutationCandidatesVisitorTest extends VisitorTestCase
{
    /**
     * @param list<int> $eligibleNodeIds
     */
    #[DataProvider('nodeProvider')]
    public function test_it_labels_visited_nodes_as_visited_and_eligible_nodes_as_mutation_candidates(
        string $code,
        array $eligibleNodeIds,
        string $expected,
    ): void {
        $nodes = $this->parse($code);

        $nodesById = $this->addIdsToNodes($nodes);

        self::markNodeAsEligible($nodesById, $eligibleNodeIds);

        $traverser = new NodeTraverser(
            new ReflectionVisitor(),
            new LabelMutationCandidatesVisitor(),
            new KeepOnlyDesiredAttributesVisitor(
                MarkTraversedNodesAsVisitedVisitor::VISITED_ATTRIBUTE,
                LabelNodesAsEligibleVisitor::ELIGIBLE,
                LabelMutationCandidatesVisitor::MUTATION_CANDIDATE,
                AddIdToTraversedNodesVisitor::NODE_ID_ATTRIBUTE,
                'expr',
                'kind',
                'rawValue',
            ),
        );
        $traverser->traverse($nodes);

        $actual = $this->dumper->dump($nodes);

        $this->assertSame($expected, $actual);
    }

    public static function nodeProvider(): iterable
    {
        yield 'no eligible node' => [
            <<<'PHP'
                <?php

                namespace Infection\Tests\Virtual;

                class Engine {
                    function create() {
                        return new Engine(
                            static fn () => 'first',
                            static fn () => 'second',
                            static fn () => 'third',
                        );
                    }
                }

                PHP,
            [],
            <<<'AST'
                array(
                    0: Stmt_Namespace(
                        name: Name(
                            nodeId: 1
                        )
                        stmts: array(
                            0: Stmt_Class(
                                name: Identifier(
                                    nodeId: 3
                                )
                                stmts: array(
                                    0: Stmt_ClassMethod(
                                        name: Identifier(
                                            nodeId: 5
                                        )
                                        stmts: array(
                                            0: Stmt_Return(
                                                expr: Expr_New(
                                                    class: Name(
                                                        nodeId: 8
                                                    )
                                                    args: array(
                                                        0: Arg(
                                                            value: Expr_ArrowFunction(
                                                                expr: Scalar_String(
                                                                    kind: KIND_SINGLE_QUOTED (1)
                                                                    rawValue: 'first'
                                                                    nodeId: 11
                                                                )
                                                                nodeId: 10
                                                            )
                                                            nodeId: 9
                                                        )
                                                        1: Arg(
                                                            value: Expr_ArrowFunction(
                                                                expr: Scalar_String(
                                                                    kind: KIND_SINGLE_QUOTED (1)
                                                                    rawValue: 'second'
                                                                    nodeId: 14
                                                                )
                                                                nodeId: 13
                                                            )
                                                            nodeId: 12
                                                        )
                                                        2: Arg(
                                                            value: Expr_ArrowFunction(
                                                                expr: Scalar_String(
                                                                    kind: KIND_SINGLE_QUOTED (1)
                                                                    rawValue: 'third'
                                                                    nodeId: 17
                                                                )
                                                                nodeId: 16
                                                            )
                                                            nodeId: 15
                                                        )
                                                    )
                                                    nodeId: 7
                                                )
                                                nodeId: 6
                                            )
                                        )
                                        nodeId: 4
                                    )
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

        yield 'some eligible nodes, not all mutable' => [
            <<<'PHP'
                <?php

                namespace Infection\Tests\Virtual;

                class Engine {
                    function create() {
                        return new Engine(
                            static fn () => 'first',
                            static fn () => 'second',
                            static fn () => 'third',
                        );
                    }
                }

                PHP,
            [3, 4],
            <<<'AST'
                array(
                    0: Stmt_Namespace(
                        name: Name(
                            nodeId: 1
                        )
                        stmts: array(
                            0: Stmt_Class(
                                name: Identifier(
                                    nodeId: 3
                                    eligible: true
                                )
                                stmts: array(
                                    0: Stmt_ClassMethod(
                                        name: Identifier(
                                            nodeId: 5
                                        )
                                        stmts: array(
                                            0: Stmt_Return(
                                                expr: Expr_New(
                                                    class: Name(
                                                        nodeId: 8
                                                    )
                                                    args: array(
                                                        0: Arg(
                                                            value: Expr_ArrowFunction(
                                                                expr: Scalar_String(
                                                                    kind: KIND_SINGLE_QUOTED (1)
                                                                    rawValue: 'first'
                                                                    nodeId: 11
                                                                )
                                                                nodeId: 10
                                                            )
                                                            nodeId: 9
                                                        )
                                                        1: Arg(
                                                            value: Expr_ArrowFunction(
                                                                expr: Scalar_String(
                                                                    kind: KIND_SINGLE_QUOTED (1)
                                                                    rawValue: 'second'
                                                                    nodeId: 14
                                                                )
                                                                nodeId: 13
                                                            )
                                                            nodeId: 12
                                                        )
                                                        2: Arg(
                                                            value: Expr_ArrowFunction(
                                                                expr: Scalar_String(
                                                                    kind: KIND_SINGLE_QUOTED (1)
                                                                    rawValue: 'third'
                                                                    nodeId: 17
                                                                )
                                                                nodeId: 16
                                                            )
                                                            nodeId: 15
                                                        )
                                                    )
                                                    nodeId: 7
                                                )
                                                nodeId: 6
                                            )
                                        )
                                        nodeId: 4
                                        eligible: true
                                        mutationCandidate: true
                                    )
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

    /**
     * @param array<positive-int|0, Node> $nodesById
     * @param list<int> $eligibleNodeIds
     */
    private static function markNodeAsEligible(array $nodesById, array $eligibleNodeIds): void
    {
        $eligibleNodes = array_intersect_key(
            $nodesById,
            array_flip($eligibleNodeIds),
        );

        foreach ($eligibleNodes as $node) {
            LabelNodesAsEligibleVisitor::markAsEligible($node);
        }
    }
}
