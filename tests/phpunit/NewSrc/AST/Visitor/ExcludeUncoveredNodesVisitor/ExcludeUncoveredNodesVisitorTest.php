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

namespace Infection\Tests\NewSrc\AST\Visitor\ExcludeUncoveredNodesVisitor;

use Infection\Tests\NewSrc\AST\AstTestCase;
use Infection\Tests\NewSrc\AST\Visitor\MarkTraversedNodesAsVisitedVisitor\MarkTraversedNodesAsVisitedVisitor;
use newSrc\AST\Metadata\NodePosition;
use newSrc\AST\Metadata\TraverseContext;
use newSrc\AST\NodeVisitor\ExcludeUncoveredNodesVisitor;
use newSrc\TestFramework\Adapter\PHPUnit\PHPUnitTracer;
use PhpParser\NodeTraverser;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(ExcludeUncoveredNodesVisitor::class)]
final class ExcludeUncoveredNodesVisitorTest extends AstTestCase
{
    /**
     * @param list<NodePosition> $coveredLines
     */
    #[DataProvider('nodeProvider')]
    public function test_it_annotates_excluded_nodes_and_stops_the_traversal(
        string $code,
        array $coveredLines,
        string $expected,
    ): void {
        $nodes = $this->createParser()->parse($code);

        $traverser = new NodeTraverser(
            new ExcludeUncoveredNodesVisitor(
                new TestTracer($coveredLines),
                new TraverseContext('/path/to/file.php'),
            ),
            new MarkTraversedNodesAsVisitedVisitor(),
        );
        $traverser->traverse($nodes);

        $actual = $this->dumper->dump(
            $nodes,
            $code,
            dumpPositions: true,
        );

        $this->assertSame($expected, $actual);
    }

    public static function nodeProvider(): iterable
    {


        yield 'method fully covered' => [
            <<<'PHP'
                <?php

                namespace Infection\Tests\Virtual;

                class SomeClass {
                    function methodA() {
                        $x0 = '';
                        $x1 = '';
                    }
                }

                PHP,
            [
                new NodePosition(
                    startLine: 0,
                    startTokenPosition: 0,
                    endLine: 100,
                    endTokenPosition: 0,
                ),
            ],
            <<<'OUT'
                array(
                    0: Stmt_Namespace[3:1 - 10:1](
                        name: Name[3:11 - 3:33]
                        stmts: array(
                            0: Stmt_Class[5:1 - 10:1](
                                name: Identifier[5:7 - 5:15]
                                stmts: array(
                                    0: Stmt_ClassMethod[6:5 - 9:5](
                                        name: Identifier[6:14 - 6:20]
                                        stmts: array(
                                            0: Stmt_Expression[7:9 - 7:17](
                                                expr: Expr_Assign[7:9 - 7:16](
                                                    var: Expr_Variable[7:9 - 7:11]
                                                    expr: Scalar_String[7:15 - 7:16](
                                                        kind: KIND_SINGLE_QUOTED (1)
                                                        rawValue: ''
                                                    )
                                                )
                                            )
                                            1: Stmt_Expression[8:9 - 8:17](
                                                expr: Expr_Assign[8:9 - 8:16](
                                                    var: Expr_Variable[8:9 - 8:11]
                                                    expr: Scalar_String[8:15 - 8:16](
                                                        kind: KIND_SINGLE_QUOTED (1)
                                                        rawValue: ''
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
                OUT,
        ];

        yield 'method partially covered' => [
            <<<'PHP'
                <?php

                namespace Infection\Tests\Virtual;

                class SomeClass {
                    function methodA() {
                        $x0 = '';
                        $x1 = '';
                    }
                }

                PHP,
            [
                new NodePosition(
                    startLine: 7,
                    startTokenPosition: 9,
                    endLine: 7,
                    endTokenPosition: 16,
                ),
            ],
            <<<'OUT'
                array(
                    0: Stmt_Namespace[3:1 - 10:1](
                        name: Name[3:11 - 3:33]
                        stmts: array(
                            0: Stmt_Class[5:1 - 10:1](
                                name: Identifier[5:7 - 5:15]
                                stmts: array(
                                    0: Stmt_ClassMethod[6:5 - 9:5](
                                        name: Identifier[6:14 - 6:20]
                                        stmts: array(
                                            0: Stmt_Expression[7:9 - 7:17](
                                                expr: Expr_Assign[7:9 - 7:16](
                                                    var: Expr_Variable[7:9 - 7:11]
                                                    expr: Scalar_String[7:15 - 7:16](
                                                        kind: KIND_SINGLE_QUOTED (1)
                                                        rawValue: ''
                                                    )
                                                )
                                            )
                                            1: Stmt_Expression[8:9 - 8:17](
                                                expr: Expr_Assign[8:9 - 8:16](
                                                    var: Expr_Variable[8:9 - 8:11]
                                                    expr: Scalar_String[8:15 - 8:16](
                                                        kind: KIND_SINGLE_QUOTED (1)
                                                        rawValue: ''
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
                OUT,
        ];
    }
}
