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

namespace Infection\Tests\TestingUtility\PhpParser\Visitor\SkipNodesVisitor;

use Infection\Tests\PhpParser\Visitor\VisitorTestCase\VisitorTestCase;
use Infection\Tests\TestingUtility\PhpParser\Visitor\MarkTraversedNodesAsVisitedVisitor\MarkTraversedNodesAsVisitedVisitor;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(SkipNodesVisitor::class)]
final class SkipNodesVisitorTest extends VisitorTestCase
{
    /**
     * @param list<int> $nodeIds
     * @param NodeVisitor::DONT_TRAVERSE_CHILDREN|NodeVisitor::STOP_TRAVERSAL|NodeVisitor::DONT_TRAVERSE_CURRENT_AND_CHILDREN $stopTraverseType
     */
    #[DataProvider('nodeProvider')]
    public function test_it_skips_encountered_nodes(
        string $code,
        array $nodeIds,
        int $stopTraverseType,
        string $expected,
    ): void {
        $nodes = $this->parse($code);

        $this->addIdsToNodes($nodes);

        (new NodeTraverser(
            new SkipNodesVisitor($nodeIds, $stopTraverseType),
            new MarkTraversedNodesAsVisitedVisitor(),
        ))->traverse($nodes);

        $this->assertSame(
            $expected,
            $this->dumper->dump($nodes),
        );
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
            SkipNodesVisitor::DEFAULT_STOP_TRAVERSE_TYPE,
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

        yield 'skip one node (default skip)' => [
            <<<'PHP'
                <?php

                namespace Infection\Tests\Virtual;

                $engine = new Engine(
                    static fn () => 'first',
                    static fn () => 'second',
                    static fn () => 'third',
                );

                PHP,
            [10],
            SkipNodesVisitor::DEFAULT_STOP_TRAVERSE_TYPE,
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
                                            1: <skipped>
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

        yield 'stop the traverse of current and children for a node' => [
            <<<'PHP'
                <?php

                namespace Infection\Tests\Virtual;

                $engine = new Engine(
                    static fn () => 'first',
                    static fn () => 'second',
                    static fn () => 'third',
                );

                PHP,
            [10],
            NodeVisitor::DONT_TRAVERSE_CURRENT_AND_CHILDREN,
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
                                            1: <skipped>
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

        yield 'stop the traverse of children for a node' => [
            <<<'PHP'
                <?php

                namespace Infection\Tests\Virtual;

                $engine = new Engine(
                    static fn () => 'first',
                    static fn () => 'second',
                    static fn () => 'third',
                );

                PHP,
            [10],
            NodeVisitor::DONT_TRAVERSE_CHILDREN,
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
                                                value: <skipped>
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

        yield 'stop the traverse for a node' => [
            <<<'PHP'
                <?php

                namespace Infection\Tests\Virtual;

                $engine = new Engine(
                    static fn () => 'first',
                    static fn () => 'second',
                    static fn () => 'third',
                );

                PHP,
            [10],
            NodeVisitor::STOP_TRAVERSAL,
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
                                            1: <skipped>
                                            2: <skipped>
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
