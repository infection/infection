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

use Infection\PhpParser\Metadata\NodeAnnotator;
use Infection\PhpParser\Visitor\NameResolverFactory;
use Infection\Tests\PhpParser\Visitor\VisitorTestCase\VisitorTestCase;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Nop;
use PhpParser\NodeTraverser;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(NodeAnnotator::class)]
final class NameResolverTest extends VisitorTestCase
{
    /**
     * This test is to ensure the integration of NameResolver works as expected.
     */
    #[CoversNothing]
    #[DataProvider('nodeProvider')]
    public function test_it_annotates_the_resolved_node_names(
        string $code,
        string $expected,
    ): void {
        $nodes = $this->parse($code);

        $this->addIdsToNodes($nodes);
        (new NodeTraverser(
            NameResolverFactory::create(),
        ))->traverse($nodes);

        $actual = $this->dumper->dump($nodes, onlyVisitedNodes: false);

        $this->assertSame($expected, $actual);
    }

    public static function nodeProvider(): iterable
    {
        yield [
            <<<'PHP'
                <?php

                declare(strict_types=1);

                namespace Infection\Tests\Virtual\A;

                function calculate() {}

                namespace Infection\Tests\Virtual\B;

                use function Infection\Tests\Virtual\A\calculate;

                class First {
                    function __construct() {
                        calculate();
                        function_exists('ambiguousFunctionCall');
                    }
                }

                PHP,
            <<<'AST'
                array(
                    0: Stmt_Declare(
                        declares: array(
                            0: DeclareItem(
                                key: Identifier(
                                    nodeId: 2
                                )
                                value: Scalar_Int(
                                    rawValue: 1
                                    kind: KIND_DEC (10)
                                    nodeId: 3
                                )
                                nodeId: 1
                            )
                        )
                        nodeId: 0
                    )
                    1: Stmt_Namespace(
                        name: Name(
                            nodeId: 5
                        )
                        stmts: array(
                            0: Stmt_Function(
                                name: Identifier(
                                    nodeId: 7
                                )
                                nodeId: 6
                            )
                        )
                        kind: 1
                        nodeId: 4
                    )
                    2: Stmt_Namespace(
                        name: Name(
                            nodeId: 9
                        )
                        stmts: array(
                            0: Stmt_Use(
                                uses: array(
                                    0: UseItem(
                                        name: Name(
                                            nodeId: 12
                                        )
                                        nodeId: 11
                                    )
                                )
                                nodeId: 10
                            )
                            1: Stmt_Class(
                                name: Identifier(
                                    nodeId: 14
                                )
                                stmts: array(
                                    0: Stmt_ClassMethod(
                                        name: Identifier(
                                            nodeId: 16
                                        )
                                        stmts: array(
                                            0: Stmt_Expression(
                                                expr: Expr_FuncCall(
                                                    name: Name(
                                                        nodeId: 19
                                                        resolvedName: nodeId(19)
                                                    )
                                                    nodeId: 18
                                                )
                                                nodeId: 17
                                            )
                                            1: Stmt_Expression(
                                                expr: Expr_FuncCall(
                                                    name: Name(
                                                        nodeId: 22
                                                        namespacedName: nodeId(22)
                                                    )
                                                    args: array(
                                                        0: Arg(
                                                            value: Scalar_String(
                                                                kind: KIND_SINGLE_QUOTED (1)
                                                                rawValue: 'ambiguousFunctionCall'
                                                                nodeId: 24
                                                            )
                                                            nodeId: 23
                                                        )
                                                    )
                                                    nodeId: 21
                                                )
                                                nodeId: 20
                                            )
                                        )
                                        nodeId: 15
                                    )
                                )
                                nodeId: 13
                            )
                        )
                        kind: 1
                        nodeId: 8
                    )
                )
                AST,
        ];
    }

    public function test_it_can_provide_the_node_fqcn(): void
    {
        $fqcn = new FullyQualified('Acme\Foo');
        $node = new Nop(['resolvedName' => $fqcn]);

        $this->assertSame($fqcn, NodeAnnotator::getFqcn($node));
    }

    public function test_it_can_provide_the_node_fqcn_for_an_anonymous_class(): void
    {
        $node = new Nop(['resolvedName' => null]);

        $this->assertNull(NodeAnnotator::getFqcn($node));
    }
}
