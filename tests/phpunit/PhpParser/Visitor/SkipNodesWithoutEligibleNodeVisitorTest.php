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
use Infection\PhpParser\Visitor\ContainsEligibleNodeVisitor;
use Infection\PhpParser\Visitor\MarkTraversedNodesAsVisitedVisitor;
use Infection\PhpParser\Visitor\SkipNodesWithoutEligibleNodeVisitor;
use Infection\Tests\PhpParser\Visitor\VisitorTestCase\VisitorTestCase;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(SkipNodesWithoutEligibleNodeVisitor::class)]
final class SkipNodesWithoutEligibleNodeVisitorTest extends VisitorTestCase
{
    /**
     * @param list<int> $containsEligibleNodeIds
     */
    #[DataProvider('nodeProvider')]
    public function test_it_skips_nodes_without_eligible_nodes(
        string $code,
        array $containsEligibleNodeIds,
        string $expected,
    ): void {
        $nodes = $this->parse($code);

        $nodesById = $this->addIdsToNodes($nodes);
        $this->markNodesAsContainingEligibleNode($nodesById, $containsEligibleNodeIds);

        (new NodeTraverser(
            new SkipNodesWithoutEligibleNodeVisitor(),
            new MarkTraversedNodesAsVisitedVisitor(),
        ))->traverse($nodes);

        $this->keepOnlyDesiredAttributes(
            $nodes,
            AddIdToTraversedNodesVisitor::NODE_ID_ATTRIBUTE,
            ContainsEligibleNodeVisitor::CONTAINS_ELIGIBLE_NODE,
        );

        $actual = $this->dumper->dump($nodes);

        $this->assertSame($expected, $actual);
    }

    public static function nodeProvider(): iterable
    {
        $phpCode = <<<'PHP'
            <?php

            $x = 1 + 2;
            $y = 3 + 4;

            PHP;

        yield 'no branch with eligible nodes' => [
            $phpCode,
            [],
            <<<'AST'
                array(
                    0: <skipped>
                    1: <skipped>
                )
                AST,
        ];

        yield 'one branch with eligible nodes' => [
            $phpCode,
            [0, 1, 3, 4, 5],
            <<<'AST'
                array(
                    0: Stmt_Expression(
                        expr: Expr_Assign(
                            var: <skipped>
                            expr: Expr_BinaryOp_Plus(
                                left: Scalar_Int(
                                    containsEligibleNode: true
                                    nodeId: 4
                                )
                                right: Scalar_Int(
                                    containsEligibleNode: true
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
                    1: <skipped>
                )
                AST,
        ];
    }

    /**
     * @param array<positive-int|0, Node> $nodesById
     * @param list<int> $nodeIds
     */
    private function markNodesAsContainingEligibleNode(array $nodesById, array $nodeIds): void
    {
        $nodes = array_intersect_key(
            $nodesById,
            array_flip($nodeIds),
        );

        foreach ($nodes as $node) {
            ContainsEligibleNodeVisitor::markAsContainingEligibleNode($node);
        }
    }
}
