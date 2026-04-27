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

namespace Infection\Tests\PhpParser\Visitor\ExcludeIgnoredNodesVisitor;

use function array_intersect;
use function array_keys;
use Infection\PhpParser\Visitor\AddIdToTraversedNodesVisitor\AddIdToTraversedNodesVisitor;
use Infection\Tests\PhpParser\Visitor\VisitorTestCase\VisitorTestCase;
use PhpParser\NodeTraverser;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(MarkAllButIneligibleNodesAsVisitedVisitor::class)]
final class MarkAllButIneligibleNodesAsVisitedVisitorTest extends VisitorTestCase
{
    /**
     * @param list<int>|null $ineligibleNodeIds
     * @param list<int>|null $eligibleNodeIds
     */
    #[DataProvider('nodeProvider')]
    public function test_it_labels_visited_nodes_as_visited_and_eligible_nodes_as_mutation_candidates(
        string $code,
        ?array $eligibleNodeIds,
        ?array $ineligibleNodeIds,
        string $expected,
    ): void {
        $nodes = $this->parse($code);

        $nodesById = $this->addIdsToNodes($nodes);

        $eligibleNodeIds ??= array_keys($nodesById);
        $ineligibleNodeIds ??= array_keys($nodesById);

        self::ensureNoCommonNodes($eligibleNodeIds, $ineligibleNodeIds);

        $this->markNodesAsEligible($nodesById, $eligibleNodeIds);
        self::markNodesAsIneligible($nodesById, $ineligibleNodeIds);

        $traverser = new NodeTraverser(
            new MarkAllButIneligibleNodesAsVisitedVisitor(),
        );
        $traverser->traverse($nodes);

        $this->keepOnlyDesiredAttributes(
            $nodes,
            AddIdToTraversedNodesVisitor::NODE_ID_ATTRIBUTE,
            'expr',
            'kind',
            'rawValue',
        );

        $actual = $this->dumper->dump($nodes);

        $this->assertSame($expected, $actual);
    }

    public static function nodeProvider(): iterable
    {
        yield 'no eligible/ineligible nodes' => [
            <<<'PHP'
                <?php

                $x1;
                $x2;
                $x3;
                $x4;

                PHP,
            [],
            [],
            <<<'AST'
                array(
                    0: Stmt_Expression(
                        expr: Expr_Variable(
                            nodeId: 1
                        )
                        nodeId: 0
                    )
                    1: Stmt_Expression(
                        expr: Expr_Variable(
                            nodeId: 3
                        )
                        nodeId: 2
                    )
                    2: Stmt_Expression(
                        expr: Expr_Variable(
                            nodeId: 5
                        )
                        nodeId: 4
                    )
                    3: Stmt_Expression(
                        expr: Expr_Variable(
                            nodeId: 7
                        )
                        nodeId: 6
                    )
                )
                AST,
        ];

        yield 'all nodes are eligible' => [
            <<<'PHP'
                <?php

                $x1;
                $x2;
                $x3;
                $x4;

                PHP,
            null,
            [],
            <<<'AST'
                array(
                    0: Stmt_Expression(
                        expr: Expr_Variable(
                            nodeId: 1
                        )
                        nodeId: 0
                    )
                    1: Stmt_Expression(
                        expr: Expr_Variable(
                            nodeId: 3
                        )
                        nodeId: 2
                    )
                    2: Stmt_Expression(
                        expr: Expr_Variable(
                            nodeId: 5
                        )
                        nodeId: 4
                    )
                    3: Stmt_Expression(
                        expr: Expr_Variable(
                            nodeId: 7
                        )
                        nodeId: 6
                    )
                )
                AST,
        ];

        yield 'all nodes are ineligible' => [
            <<<'PHP'
                <?php

                $x1;
                $x2;
                $x3;
                $x4;

                PHP,
            [],
            null,
            <<<'AST'
                array(
                    0: <skipped>
                    1: <skipped>
                    2: <skipped>
                    3: <skipped>
                )
                AST,
        ];

        yield 'some eligible nodes, some not eligble, others no eligibility' => [
            <<<'PHP'
                <?php

                $x1;
                $x2;
                $x3;
                $x4;

                PHP,
            [0, 4, 5],
            [2, 3],
            <<<'AST'
                array(
                    0: Stmt_Expression(
                        expr: Expr_Variable(
                            nodeId: 1
                        )
                        nodeId: 0
                    )
                    1: <skipped>
                    2: Stmt_Expression(
                        expr: Expr_Variable(
                            nodeId: 5
                        )
                        nodeId: 4
                    )
                    3: Stmt_Expression(
                        expr: Expr_Variable(
                            nodeId: 7
                        )
                        nodeId: 6
                    )
                )
                AST,
        ];
    }

    /**
     * @param list<int> $eligibleNodeIds
     * @param list<int> $ineligibleNodeIds
     */
    private static function ensureNoCommonNodes(
        array $eligibleNodeIds,
        array $ineligibleNodeIds,
    ): void {
        self::assertCount(
            0,
            array_intersect($eligibleNodeIds, $ineligibleNodeIds),
            'Did not expect to find nodes to be marked as both eligible and ineligible.',
        );
    }
}
