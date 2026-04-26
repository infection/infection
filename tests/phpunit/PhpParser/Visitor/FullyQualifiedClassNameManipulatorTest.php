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

use function array_fill_keys;
use function array_keys;
use function array_replace;
use Infection\PhpParser\Visitor\FullyQualifiedClassNameManipulator;
use Infection\PhpParser\Visitor\NameResolverFactory;
use Infection\Tests\PhpParser\Visitor\VisitorTestCase\VisitorTestCase;
use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\NodeTraverser;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(FullyQualifiedClassNameManipulator::class)]
final class FullyQualifiedClassNameManipulatorTest extends VisitorTestCase
{
    /**
     * This test is to ensure the integration of NameResolver works as expected.
     *
     * @param array<int, Name|FullyQualified|null> $partialExpectedFqcns
     */
    #[CoversNothing]
    #[DataProvider('nodeProvider')]
    public function test_it_annotates_the_resolved_node_names(
        string $code,
        string $expectedDump,
        array $partialExpectedFqcns,
    ): void {
        $nodes = $this->parse($code);

        $nodesById = $this->addIdsToNodes($nodes);
        (new NodeTraverser(
            NameResolverFactory::create(),
        ))->traverse($nodes);

        $actual = $this->dumper->dump($nodes, onlyVisitedNodes: false);

        $this->assertSame($expectedDump, $actual);

        $expected = self::completeExpectedFqcns($nodesById, $partialExpectedFqcns);
        $actualFqcns = $this->getFqcns($nodesById);

        $this->assertEquals($expected, $actualFqcns);
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
                                resolvedName: FullyQualified(calculate)
                            )
                            nodeId: 3
                        )
                        nodeId: 2
                    )
                )
                AST,
            [
                0 => new Name('calculate'),
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
                                        namespacedName: FullyQualified(Infection\Tests\Virtual\calculate)
                                        nodeId: 6
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
                2 => new Name('Infection\Tests\Virtual\calculate'),
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
                                resolvedName: FullyQualified(Calculator)
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
                0 => new Name('Calculator'),
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
                                        resolvedName: FullyQualified(Infection\Tests\Virtual\Calculator)
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
                2 => new Name('Infection\Tests\Virtual\Calculator'),
                8 => new FullyQualified('Infection\Tests\Virtual\Calculator'),
            ],
        ];

        yield 'code without names' => [
            <<<'PHP'
                <?php

                $x = 'Hello World!';

                PHP,
            <<<'AST'
                array(
                    0: Stmt_Expression(
                        expr: Expr_Assign(
                            var: Expr_Variable(
                                nodeId: 2
                            )
                            expr: Scalar_String(
                                kind: KIND_SINGLE_QUOTED (1)
                                nodeId: 3
                                rawValue: 'Hello World!'
                            )
                            nodeId: 1
                        )
                        nodeId: 0
                    )
                )
                AST,
            [],
        ];
    }

    /**
     * @param array<int, Node> $nodesById
     * @param array<int, Name|FullyQualified|null> $partialExpectedFqcns
     *
     * @return array<int, Name|FullyQualified|null>
     */
    private static function completeExpectedFqcns(
        array $nodesById,
        array $partialExpectedFqcns,
    ): array {
        return array_replace(
            array_fill_keys(
                array_keys($nodesById),
                null,
            ),
            $partialExpectedFqcns,
        );
    }

    /**
     * @param array<int, Node> $nodesById
     *
     * @return array<int, Name|FullyQualified|null>
     */
    private function getFqcns(array $nodesById): array
    {
        $fqcns = [];

        foreach ($nodesById as $nodeId => $node) {
            $this->assertArrayHasKey(
                $nodeId,
                $nodesById,
                'No node with the node ID %s was found.',
            );

            $actual = FullyQualifiedClassNameManipulator::getFqcn($node);
            $actual?->setAttributes([]);

            $fqcns[$nodeId] = $actual;
        }

        return $fqcns;
    }
}
