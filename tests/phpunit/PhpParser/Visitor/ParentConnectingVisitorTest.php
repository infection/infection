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
use Infection\Tests\PhpParser\Visitor\VisitorTestCase\VisitorTestCase;
use Infection\Tests\TestingUtility\PHPUnit\ExpectsThrowables;
use LogicException;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Function_;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\ParentConnectingVisitor;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(NodeAnnotator::class)]
final class ParentConnectingVisitorTest extends VisitorTestCase
{
    use ExpectsThrowables;

    /**
     * This test is to ensure the integration of ParentConnectingVisitor works as expected.
     */
    #[CoversNothing]
    #[DataProvider('nodeProvider')]
    public function test_it_annotates_the_parent_nodes(
        string $code,
        string $expected,
    ): void {
        $nodes = $this->parse($code);

        $this->addIdsToNodes($nodes);
        (new NodeTraverser(
            new ParentConnectingVisitor(),
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

                namespace Infection\Tests\Virtual;

                if ('mock' === $GLOBALS['mode']) {
                    return;
                }

                class Greeter {
                    function greet(): void {
                        echo 'Hello world!';
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
                                    parent: nodeId(1)
                                )
                                value: Scalar_Int(
                                    rawValue: 1
                                    kind: KIND_DEC (10)
                                    nodeId: 3
                                    parent: nodeId(1)
                                )
                                nodeId: 1
                                parent: nodeId(0)
                            )
                        )
                        nodeId: 0
                    )
                    1: Stmt_Namespace(
                        name: Name(
                            nodeId: 5
                            parent: nodeId(4)
                        )
                        stmts: array(
                            0: Stmt_If(
                                cond: Expr_BinaryOp_Identical(
                                    left: Scalar_String(
                                        kind: KIND_SINGLE_QUOTED (1)
                                        rawValue: 'mock'
                                        nodeId: 8
                                        parent: nodeId(7)
                                    )
                                    right: Expr_ArrayDimFetch(
                                        var: Expr_Variable(
                                            nodeId: 10
                                            parent: nodeId(9)
                                        )
                                        dim: Scalar_String(
                                            kind: KIND_SINGLE_QUOTED (1)
                                            rawValue: 'mode'
                                            nodeId: 11
                                            parent: nodeId(9)
                                        )
                                        nodeId: 9
                                        parent: nodeId(7)
                                    )
                                    nodeId: 7
                                    parent: nodeId(6)
                                )
                                stmts: array(
                                    0: Stmt_Return(
                                        nodeId: 12
                                        parent: nodeId(6)
                                    )
                                )
                                nodeId: 6
                                parent: nodeId(4)
                            )
                            1: Stmt_Class(
                                name: Identifier(
                                    nodeId: 14
                                    parent: nodeId(13)
                                )
                                stmts: array(
                                    0: Stmt_ClassMethod(
                                        name: Identifier(
                                            nodeId: 16
                                            parent: nodeId(15)
                                        )
                                        returnType: Identifier(
                                            nodeId: 17
                                            parent: nodeId(15)
                                        )
                                        stmts: array(
                                            0: Stmt_Echo(
                                                exprs: array(
                                                    0: Scalar_String(
                                                        kind: KIND_SINGLE_QUOTED (1)
                                                        rawValue: 'Hello world!'
                                                        nodeId: 19
                                                        parent: nodeId(18)
                                                    )
                                                )
                                                nodeId: 18
                                                parent: nodeId(15)
                                            )
                                        )
                                        nodeId: 15
                                        parent: nodeId(13)
                                    )
                                )
                                nodeId: 13
                                parent: nodeId(4)
                            )
                        )
                        kind: 1
                        nodeId: 4
                    )
                )
                AST,
        ];
    }

    public function test_it_can_provide_the_parent_node(): void
    {
        $nodes = $this->parse(
            <<<'PHP'
                <?php

                function greet(): void {
                    echo 'Hello world!';
                }

                PHP,
        );

        $this->addIdsToNodes($nodes);
        (new NodeTraverser(
            new ParentConnectingVisitor(),
        ))->traverse($nodes);

        $functionNode = $nodes[0];
        $this->assertInstanceOf(Function_::class, $functionNode);

        $this->assertNull(
            NodeAnnotator::findParent($functionNode),
            'Expected a root node to not have any parent.',
        );

        $failure = $this->expectToThrow(
            static fn () => NodeAnnotator::getParent($functionNode),
        );
        $this->assertInstanceOf(LogicException::class, $failure);
        $this->assertSame(
            'Expected to find the attribute "parent". Could not find it for the node: Node(Stmt_Function, 0)',
            $failure->getMessage(),
        );

        $this->assertNull(
            NodeAnnotator::findParent($functionNode),
            'Expected a root node to not have any parent.',
        );

        $functionName = $functionNode->name;
        $this->assertInstanceOf(Identifier::class, $functionName);

        $this->assertSame($functionNode, NodeAnnotator::getParent($functionName));
        $this->assertSame($functionNode, NodeAnnotator::findParent($functionName));
    }
}
