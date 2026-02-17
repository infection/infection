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

use function array_keys;
use Infection\PhpParser\Visitor\FullyQualifiedClassNameManipulator;
use Infection\PhpParser\Visitor\NameResolverFactory;
use Infection\Tests\PhpParser\Visitor\VisitorTestCase\VisitorTestCase;
use PhpParser\Node;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\NodeTraverser;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProvider;
use function sprintf;

#[CoversClass(FullyQualifiedClassNameManipulator::class)]
final class FullyQualifiedClassNameManipulatorTest extends VisitorTestCase
{
    /**
     * This test is to ensure the integration of NameResolver works as expected.
     *
     * @param array<int, FullyQualified>
     */
    #[CoversNothing]
    #[DataProvider('nodeProvider')]
    public function test_it_annotates_the_resolved_node_names(
        string $code,
        string $expectedDump,
        array $expectedFqcns,
    ): void {
        $nodes = $this->parse($code);

        $nodesById = $this->addIdsToNodes($nodes);
        (new NodeTraverser(
            NameResolverFactory::create(),
        ))->traverse($nodes);

        $actual = $this->dumper->dump($nodes, onlyVisitedNodes: false);

        $this->assertSame($expectedDump, $actual);

        $actualFqcns = $this->getFqcns(
            array_keys($expectedFqcns),
            $nodesById,
        );

        $this->assertEquals($expectedFqcns, $actualFqcns);
    }

    public static function nodeProvider(): iterable
    {
        yield 'function declaration and call' => [
            <<<'PHP'
                <?php

                function calculate() {}
                calculate();

                PHP,
            <<<'AST'
                array(
                    0: Stmt_Function(
                        name: Identifier(
                            nodeId: 1
                        )
                        nodeId: 0
                    )
                    1: Stmt_Expression(
                        expr: Expr_FuncCall(
                            name: Name(
                                nodeId: 4
                                resolvedName: nodeId(4)
                            )
                            nodeId: 3
                        )
                        nodeId: 2
                    )
                )
                AST,
            [
                4 => new FullyQualified('calculate'),
            ],
        ];

        yield 'namespaced function declaration and call' => [
            <<<'PHP'
                <?php

                namespace Infection\Tests\Virtual;

                function calculate() {}
                calculate();

                PHP,
            <<<'AST'
                array(
                    0: Stmt_Namespace(
                        name: Name(
                            nodeId: 1
                        )
                        stmts: array(
                            0: Stmt_Function(
                                name: Identifier(
                                    nodeId: 3
                                )
                                nodeId: 2
                            )
                            1: Stmt_Expression(
                                expr: Expr_FuncCall(
                                    name: Name(
                                        nodeId: 6
                                        namespacedName: nodeId(6)
                                    )
                                    nodeId: 5
                                )
                                nodeId: 4
                            )
                        )
                        kind: 1
                        nodeId: 0
                    )
                )
                AST,
            [
                6 => new FullyQualified('Infection\Tests\Virtual\calculate'),
            ],
        ];

        yield 'class declaration and call' => [
            <<<'PHP'
                <?php

                class Calculator {
                    static function calculate() {}
                }
                Calculator::calculate();

                PHP,
            <<<'AST'
                array(
                    0: Stmt_Class(
                        name: Identifier(
                            nodeId: 1
                        )
                        stmts: array(
                            0: Stmt_ClassMethod(
                                name: Identifier(
                                    nodeId: 3
                                )
                                nodeId: 2
                            )
                        )
                        nodeId: 0
                    )
                    1: Stmt_Expression(
                        expr: Expr_StaticCall(
                            class: Name(
                                nodeId: 6
                                resolvedName: nodeId(6)
                            )
                            name: Identifier(
                                nodeId: 7
                            )
                            nodeId: 5
                        )
                        nodeId: 4
                    )
                )
                AST,
            [
                6 => new FullyQualified('Calculator'),
            ],
        ];

        yield 'namespaced class declaration and call' => [
            <<<'PHP'
                <?php

                namespace Infection\Tests\Virtual;

                class Calculator {
                    static function calculate() {}
                }
                Calculator::calculate();

                PHP,
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
                                        nodeId: 4
                                    )
                                )
                                nodeId: 2
                            )
                            1: Stmt_Expression(
                                expr: Expr_StaticCall(
                                    class: Name(
                                        nodeId: 8
                                        resolvedName: nodeId(8)
                                    )
                                    name: Identifier(
                                        nodeId: 9
                                    )
                                    nodeId: 7
                                )
                                nodeId: 6
                            )
                        )
                        kind: 1
                        nodeId: 0
                    )
                )
                AST,
            [
                8 => new FullyQualified('Infection\Tests\Virtual\Calculator'),
            ],
        ];
    }

    /**
     * @param int[] $nodeIds
     * @param array<int, Node> $nodesById
     *
     * @return array<int, FullyQualified>
     */
    private function getFqcns(array $nodeIds, array $nodesById): array
    {
        $fqcns = [];

        foreach ($nodeIds as $nodeId) {
            $this->assertArrayHasKey(
                $nodeId,
                $nodesById,
                'No node with the node ID %s was found.',
            );

            $node = $nodesById[$nodeId];
            $actual = FullyQualifiedClassNameManipulator::getFqcn($node);
            $this->assertNotNull(
                $actual,
                sprintf(
                    'Expected node to have a FQCN attribute, none found. Node:%s%s',
                    "\n",
                    $this->dumper->dump($node, onlyVisitedNodes: false),
                ),
            );

            $actual->setAttributes([]);

            $fqcns[$nodeId] = $actual;
        }

        return $fqcns;
    }
}
