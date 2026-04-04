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
use function array_keys;
use Infection\PhpParser\Visitor\AddIdToTraversedNodesVisitor\AddIdToTraversedNodesVisitor;
use Infection\PhpParser\Visitor\AddTestsVisitor;
use Infection\PhpParser\Visitor\ExcludeUntestedNodesVisitor;
use Infection\Tests\PhpParser\Visitor\ExcludeIgnoredNodesVisitor\MarkAllButIneligibleNodesAsVisitedVisitor;
use Infection\Tests\PhpParser\Visitor\VisitorTestCase\VisitorTestCase;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(ExcludeUntestedNodesVisitor::class)]
final class ExcludeUntestedNodesVisitorTest extends VisitorTestCase
{
    /**
     * @param list<int>|null $eligibleNodeIds
     * @param list<int>|null $testedNodeIds
     */
    #[DataProvider('nodeProvider')]
    public function test_it_marks_eligible_untested_nodes_as_ineligible(
        string $code,
        ?array $eligibleNodeIds,
        ?array $testedNodeIds,
        string $expected,
    ): void {
        $nodes = $this->parse($code);

        $nodesById = $this->addIdsToNodes($nodes);
        $this->markNodesAsEligible(
            $nodesById,
            $eligibleNodeIds ?? array_keys($nodesById),
        );
        $this->addTests(
            $nodesById,
            $testedNodeIds ?? array_keys($nodesById),
        );

        $traverser = new NodeTraverser(
            new ExcludeUntestedNodesVisitor(),
            new MarkAllButIneligibleNodesAsVisitedVisitor(),
        );
        $traverser->traverse($nodes);

        $this->keepOnlyDesiredAttributes(
            $nodes,
            AddIdToTraversedNodesVisitor::NODE_ID_ATTRIBUTE,
            'expr',
        );

        $actual = $this->dumper->dump($nodes);

        $this->assertSame($expected, $actual);
    }

    public static function nodeProvider(): iterable
    {
        $phpCode = <<<'PHP'
            <?php

            $x1;
            $x2;
            $x3;
            $x4;

            PHP;

        $untouchedAst = <<<'AST'
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
            AST;

        yield 'no eligible, no tested node' => [
            $phpCode,
            [],
            [],
            $untouchedAst,
        ];

        yield 'all nodes eligible, no tested node' => [
            $phpCode,
            null,
            [],
            <<<'AST'
                array(
                    0: <skipped>
                    1: <skipped>
                    2: <skipped>
                    3: <skipped>
                )
                AST,
        ];

        yield 'no eligible node, all nodes with tests' => [
            $phpCode,
            [],
            null,
            $untouchedAst,
        ];

        yield 'all eligible and tested node' => [
            $phpCode,
            [],
            [],
            $untouchedAst,
        ];

        yield 'some eligible and some tested node' => [
            $phpCode,
            [0, 1],
            [0, 2],
            <<<'AST'
                array(
                    0: Stmt_Expression(
                        expr: <skipped>
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
    }

    /**
     * @param array<positive-int|0, Node> $nodesById
     * @param list<int> $testedNodeIds
     */
    private function addTests(array $nodesById, array $testedNodeIds): void
    {
        $testedNodes = array_intersect_key(
            $nodesById,
            array_flip($testedNodeIds),
        );

        foreach ($testedNodes as $node) {
            $node->setAttribute(
                AddTestsVisitor::TESTS,
                static fn () => ['test0', 'test1'], // The values do not really matter
            );
        }
    }
}
