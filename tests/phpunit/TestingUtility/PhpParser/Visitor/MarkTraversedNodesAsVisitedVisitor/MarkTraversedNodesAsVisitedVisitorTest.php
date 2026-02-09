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

namespace Infection\Tests\TestingUtility\PhpParser\Visitor\MarkTraversedNodesAsVisitedVisitor;

use Infection\Tests\PhpParser\Visitor\VisitorTestCase;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PHPUnit\Framework\Attributes\CoversClass;
use function serialize;
use function unserialize;

#[CoversClass(MarkTraversedNodesAsVisitedVisitor::class)]
final class MarkTraversedNodesAsVisitedVisitorTest extends VisitorTestCase
{
    public function test_it_annotates_the_viewed_nodes_as_visited(): void
    {
        $nodes = $this->parser->parse(
            <<<'PHP'
                <?php

                namespace Infection\Tests\Virtual;

                $engine = new Engine(
                    static fn () => 'first',
                    static fn () => 'second',
                    static fn () => 'third',
                );

                PHP,
        );

        $namespace = $nodes[0] ?? null;
        $this->assertInstanceOf(Node\Stmt\Namespace_::class, $namespace);

        /** @var Node\Stmt\Expression $assignmentExpression */
        $assignmentExpression = $namespace->stmts[0];
        /** @var Node\Expr\Assign $assignmentStmt */
        $assignmentStmt = $assignmentExpression->expr;
        /** @var Node\Expr\New_ $newStmt */
        $newStmt = $assignmentStmt->expr;
        // Stop at the first argument
        StopAtSkippedArgVisitor::markNodeAsSkipped($newStmt->args[1]);

        $expectedFullyTraversed = <<<'AST'
            array(
                0: Stmt_Namespace(
                    name: Name
                    stmts: array(
                        0: Stmt_Expression(
                            expr: Expr_Assign(
                                var: Expr_Variable
                                expr: Expr_New(
                                    class: Name
                                    args: array(
                                        0: Arg(
                                            value: Expr_ArrowFunction(
                                                expr: Scalar_String(
                                                    kind: KIND_SINGLE_QUOTED (1)
                                                    rawValue: 'first'
                                                )
                                            )
                                        )
                                        1: Arg(
                                            value: Expr_ArrowFunction(
                                                expr: Scalar_String(
                                                    kind: KIND_SINGLE_QUOTED (1)
                                                    rawValue: 'second'
                                                )
                                            )
                                            skip: true
                                        )
                                        2: Arg(
                                            value: Expr_ArrowFunction(
                                                expr: Scalar_String(
                                                    kind: KIND_SINGLE_QUOTED (1)
                                                    rawValue: 'third'
                                                )
                                            )
                                        )
                                    )
                                )
                            )
                        )
                    )
                    kind: 1
                )
            )
            AST;

        $fullTraverseNodes = self::cloneNodes($nodes);
        $fullTraverseVisitor = new MarkTraversedNodesAsVisitedVisitor();

        (new NodeTraverser($fullTraverseVisitor))->traverse($fullTraverseNodes);

        // Sanity check
        $this->assertSame(
            $expectedFullyTraversed,
            $this->dumper->dump($fullTraverseNodes),
        );

        $expectedPartialTraversed = <<<'AST'
            array(
                0: Stmt_Namespace(
                    name: Name
                    stmts: array(
                        0: Stmt_Expression(
                            expr: Expr_Assign(
                                var: Expr_Variable
                                expr: Expr_New(
                                    class: Name
                                    args: array(
                                        0: Arg(
                                            value: Expr_ArrowFunction(
                                                expr: Scalar_String(
                                                    kind: KIND_SINGLE_QUOTED (1)
                                                    rawValue: 'first'
                                                )
                                            )
                                        )
                                        1: <skipped>
                                        2: Arg(
                                            value: Expr_ArrowFunction(
                                                expr: Scalar_String(
                                                    kind: KIND_SINGLE_QUOTED (1)
                                                    rawValue: 'third'
                                                )
                                            )
                                        )
                                    )
                                )
                            )
                        )
                    )
                    kind: 1
                )
            )
            AST;

        $partialTraverseNodes = self::cloneNodes($nodes);
        $partialTraverseVisitor = new MarkTraversedNodesAsVisitedVisitor();

        (new NodeTraverser(
            new StopAtSkippedArgVisitor(),
            $partialTraverseVisitor,
        ))->traverse($partialTraverseNodes);

        $this->assertSame(
            $expectedPartialTraversed,
            $this->dumper->dump($partialTraverseNodes),
        );
    }

    /**
     * @param Node[]|null $nodes
     * @return Node[]
     */
    private static function cloneNodes(?array $nodes): array
    {
        return unserialize(serialize($nodes));
    }
}
