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

use Infection\PhpParser\Visitor\AddIdToTraversedNodesVisitor\AddIdToTraversedNodesVisitor;
use Infection\PhpParser\Visitor\ContainsEligibleNodeVisitor;
use Infection\PhpParser\Visitor\LabelNodesAsEligibleVisitor;
use Infection\Tests\PhpParser\Visitor\VisitorTestCase\VisitorTestCase;
use PhpParser\NodeTraverser;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(ContainsEligibleNodeVisitor::class)]
final class ContainsEligibleNodeVisitorTest extends VisitorTestCase
{
    /**
     * @param list<int> $eligibleNodeIds
     */
    #[DataProvider('nodeProvider')]
    public function test_it_marks_nodes_containing_an_eligible_node(
        string $code,
        array $eligibleNodeIds,
        string $expected,
    ): void {
        $nodes = $this->parse($code);

        $nodesById = $this->addIdsToNodes($nodes);
        $this->markNodesAsEligible($nodesById, $eligibleNodeIds);

        (new NodeTraverser(
            new ContainsEligibleNodeVisitor(),
        ))->traverse($nodes);

        $this->keepOnlyDesiredAttributes(
            $nodes,
            AddIdToTraversedNodesVisitor::NODE_ID_ATTRIBUTE,
            LabelNodesAsEligibleVisitor::ELIGIBLE,
            ContainsEligibleNodeVisitor::CONTAINS_ELIGIBLE_NODE,
        );

        $actual = $this->dumper->dump($nodes, onlyVisitedNodes: false);

        $this->assertSame($expected, $actual);
    }

    public static function nodeProvider(): iterable
    {
        yield 'no eligible nodes' => [
            <<<'PHP'
                <?php

                $x = 1 + 2;

                PHP,
            [],
            <<<'AST'
                array(
                    0: Stmt_Expression(
                        expr: Expr_Assign(
                            var: Expr_Variable(
                                containsEligibleNode: false
                                nodeId: 2
                            )
                            expr: Expr_BinaryOp_Plus(
                                left: Scalar_Int(
                                    containsEligibleNode: false
                                    nodeId: 4
                                )
                                right: Scalar_Int(
                                    containsEligibleNode: false
                                    nodeId: 5
                                )
                                containsEligibleNode: false
                                nodeId: 3
                            )
                            containsEligibleNode: false
                            nodeId: 1
                        )
                        containsEligibleNode: false
                        nodeId: 0
                    )
                )
                AST,
        ];

        yield 'eligible leaf marks its ancestors' => [
            <<<'PHP'
                <?php

                $x = 1 + 2;

                PHP,
            [4],
            <<<'AST'
                array(
                    0: Stmt_Expression(
                        expr: Expr_Assign(
                            var: Expr_Variable(
                                containsEligibleNode: false
                                nodeId: 2
                            )
                            expr: Expr_BinaryOp_Plus(
                                left: Scalar_Int(
                                    containsEligibleNode: true
                                    eligible: true
                                    nodeId: 4
                                )
                                right: Scalar_Int(
                                    containsEligibleNode: false
                                    nodeId: 5
                                )
                                containsEligibleNode: true
                                nodeId: 3
                            )
                            containsEligibleNode: true
                            nodeId: 1
                        )
                        containsEligibleNode: true
                        nodeId: 0
                    )
                )
                AST,
        ];

        yield 'eligible root does not mark its children' => [
            <<<'PHP'
                <?php

                $x = 1 + 2;

                PHP,
            [0],
            <<<'AST'
                array(
                    0: Stmt_Expression(
                        expr: Expr_Assign(
                            var: Expr_Variable(
                                containsEligibleNode: false
                                nodeId: 2
                            )
                            expr: Expr_BinaryOp_Plus(
                                left: Scalar_Int(
                                    containsEligibleNode: false
                                    nodeId: 4
                                )
                                right: Scalar_Int(
                                    containsEligibleNode: false
                                    nodeId: 5
                                )
                                containsEligibleNode: false
                                nodeId: 3
                            )
                            containsEligibleNode: false
                            nodeId: 1
                        )
                        containsEligibleNode: true
                        eligible: true
                        nodeId: 0
                    )
                )
                AST,
        ];

        yield 'unrelated branches remain unmarked' => [
            <<<'PHP'
                <?php

                $x = 1 + 2;
                $y = 3 + 4;

                PHP,
            [4],
            <<<'AST'
                array(
                    0: Stmt_Expression(
                        expr: Expr_Assign(
                            var: Expr_Variable(
                                containsEligibleNode: false
                                nodeId: 2
                            )
                            expr: Expr_BinaryOp_Plus(
                                left: Scalar_Int(
                                    containsEligibleNode: true
                                    eligible: true
                                    nodeId: 4
                                )
                                right: Scalar_Int(
                                    containsEligibleNode: false
                                    nodeId: 5
                                )
                                containsEligibleNode: true
                                nodeId: 3
                            )
                            containsEligibleNode: true
                            nodeId: 1
                        )
                        containsEligibleNode: true
                        nodeId: 0
                    )
                    1: Stmt_Expression(
                        expr: Expr_Assign(
                            var: Expr_Variable(
                                containsEligibleNode: false
                                nodeId: 8
                            )
                            expr: Expr_BinaryOp_Plus(
                                left: Scalar_Int(
                                    containsEligibleNode: false
                                    nodeId: 10
                                )
                                right: Scalar_Int(
                                    containsEligibleNode: false
                                    nodeId: 11
                                )
                                containsEligibleNode: false
                                nodeId: 9
                            )
                            containsEligibleNode: false
                            nodeId: 7
                        )
                        containsEligibleNode: false
                        nodeId: 6
                    )
                )
                AST,
        ];
    }
}
