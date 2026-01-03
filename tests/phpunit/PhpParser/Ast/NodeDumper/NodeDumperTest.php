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

namespace Infection\Tests\PhpParser\Ast\NodeDumper;

use Infection\Tests\PhpParser\Ast\Visitor\MarkTraversedNodesAsVisitedVisitor\MarkTraversedNodesAsVisitedVisitor;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\ArrayItem;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PhpParser\ParserFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Path;
use function is_string;

#[CoversClass(NodeDumper::class)]
final class NodeDumperTest extends TestCase
{
    #[DataProvider('provideCodeWithDefaultConfiguration')]
    public function test_dump_nodes(NodeDumperScenario $scenario): void
    {
        $node = $scenario->node;

        if (is_string($node)) {
            $parser = (new ParserFactory())->createForHostVersion();

            $node = $parser->parse($node);
        }

        $dumper = new NodeDumper(
            $scenario->dumpProperties,
            $scenario->dumpComments,
            $scenario->dumpPositions,
            $scenario->dumpOtherAttributes,
            $scenario->onlyVisitedNodes,
        );

        $actual = $dumper->dump($node);

        $this->assertSame(
            Path::canonicalize($scenario->expected),
            Path::canonicalize($actual),
        );
    }

    public static function provideCodeWithDefaultConfiguration(): iterable
    {
        $variableAssignment = NodeDumperScenario::forCode(
            <<<'PHP'
                <?php

                /** @var string */
                $a = 1;
                echo $a;    // Salutation
                PHP,
        )
            ->withShowAllNodes()
        ->withDumpProperties();

        yield 'variable' => $variableAssignment
            ->withExpected(
                <<<'OUT'
                    array(
                        0: Stmt_Expression(
                            expr: Expr_Assign(
                                var: Expr_Variable(
                                    name: a
                                )
                                expr: Scalar_Int(
                                    value: 1
                                )
                            )
                        )
                        1: Stmt_Echo(
                            exprs: array(
                                0: Expr_Variable(
                                    name: a
                                )
                            )
                        )
                        2: Stmt_Nop
                    )
                    OUT,
            )
            ->build();

        yield 'variable with comments' => $variableAssignment
            ->withDumpComments()
            ->withExpected(
                <<<'OUT'
                    array(
                        0: Stmt_Expression(
                            expr: Expr_Assign(
                                var: Expr_Variable(
                                    name: a
                                )
                                expr: Scalar_Int(
                                    value: 1
                                )
                            )
                            comments: array(
                                0: /** @var string */
                            )
                        )
                        1: Stmt_Echo(
                            exprs: array(
                                0: Expr_Variable(
                                    name: a
                                )
                            )
                        )
                        2: Stmt_Nop(
                            comments: array(
                                0: / Salutation
                            )
                        )
                    )
                    OUT,
            )
        ->build();

        yield 'variable with positions' => $variableAssignment
            ->withDumpPositions()
            ->withExpected(
                <<<'OUT'
                    array(
                        0: Stmt_Expression[4 - 4](
                            expr: Expr_Assign[4 - 4](
                                var: Expr_Variable[4 - 4](
                                    name: a
                                )
                                expr: Scalar_Int[4 - 4](
                                    value: 1
                                )
                            )
                        )
                        1: Stmt_Echo[5 - 5](
                            exprs: array(
                                0: Expr_Variable[5 - 5](
                                    name: a
                                )
                            )
                        )
                        2: Stmt_Nop[5 - 5]
                    )
                    OUT,
            )
        ->build();

        yield 'tree with only some nodes marked as visited' => NodeDumperScenario::forNode(
            [
                MarkTraversedNodesAsVisitedVisitor::markAsVisited(
                    new FuncCall(
                        MarkTraversedNodesAsVisitedVisitor::markAsVisited(new Name('salute')),
                        [
                            MarkTraversedNodesAsVisitedVisitor::markAsVisited(
                                new Arg(
                                    MarkTraversedNodesAsVisitedVisitor::markAsVisited(
                                        new ArrowFunction([
                                            'expr' => MarkTraversedNodesAsVisitedVisitor::markAsVisited(
                                                new String_('first'),
                                            ),
                                        ]),
                                    ),
                                ),
                            ),
                            MarkTraversedNodesAsVisitedVisitor::markAsVisited(
                                new Arg(
                                    new ArrowFunction(['expr' => new String_('second')]),
                                ),
                            ),
                            new Arg(
                                new String_('Hello world!'),
                            ),
                        ],
                    ),
                ),
            ],
        )
            ->withExpected(
                <<<'OUT'
                    array(
                        0: Expr_FuncCall(
                            name: Name
                            args: array(
                                0: Arg(
                                    value: Expr_ArrowFunction(
                                        expr: Scalar_String
                                    )
                                )
                                1: Arg(
                                    value: <skipped>
                                )
                                2: <skipped>
                            )
                        )
                    )
                    OUT,
            )
        ->build();

        yield 'variable other attributes' => NodeDumperScenario::forNode(
            [
                new Assign(
                    new Variable(
                        new Name('x'),
                    ),
                    new String_(
                        'Hello World!',
                        ['unspecifiedAttribute' => 'Hi'],
                    ),
                    ['anotherUnspecifiedAttribute' => '...'],
                ),
            ],
        )
            ->withDumpProperties()
            ->withDumpOtherAttributes()
            ->withShowAllNodes()
            ->withExpected(
                <<<'OUT'
                    array(
                        0: Expr_Assign(
                            var: Expr_Variable(
                                name: Name(
                                    name: x
                                )
                            )
                            expr: Scalar_String(
                                value: Hello World!
                                unspecifiedAttribute: Hi
                            )
                            anotherUnspecifiedAttribute: ...
                        )
                    )
                    OUT,
            )
        ->build();

        yield 'empty array' => NodeDumperScenario::forNode([])
            ->withShowAllNodes()
            ->withExpected(
                <<<'OUT'
                    array(
                    )
                    OUT,
            )
        ->build();

        yield 'array with values' => NodeDumperScenario::forNode(
            ['Foo', 'Bar', 'Key' => 'FooBar'],
        )
            ->withShowAllNodes()
            ->withExpected(
                <<<'OUT'
                    array(
                        0: Foo
                        1: Bar
                        Key: FooBar
                    )
                    OUT,
            )
            ->build();

        yield 'name' => NodeDumperScenario::forNode(
            new Name(['Hallo', 'World']),
        )
            ->withShowAllNodes()
            ->withExpected(
                <<<'OUT'
                    Name
                    OUT,
            )
        ->build();

        yield 'name with extra properties' => NodeDumperScenario::forNode(
            new Name(['Hallo', 'World']),
        )
            ->withDumpProperties()
            ->withShowAllNodes()
            ->withExpected(
                <<<'OUT'
                    Name(
                        name: Hallo\World
                    )
                    OUT,
            )
        ->build();

        yield 'array expression' => NodeDumperScenario::forNode(
            new Array_([
                new ArrayItem(new String_('Foo')),
            ]),
        )
            ->withShowAllNodes()
            ->withExpected(
                <<<'OUT'
                    Expr_Array(
                        items: array(
                            0: ArrayItem(
                                value: Scalar_String
                            )
                        )
                    )
                    OUT,
            )
        ->build();

        yield 'array expression with extra properties' => NodeDumperScenario::forNode(
            new Array_([
                new ArrayItem(new String_('Foo')),
            ]),
        )
            ->withDumpProperties()
            ->withShowAllNodes()
            ->withShowAllNodes()
            ->withExpected(
                <<<'OUT'
                    Expr_Array(
                        items: array(
                            0: ArrayItem(
                                key: null
                                value: Scalar_String(
                                    value: Foo
                                )
                                byRef: false
                                unpack: false
                            )
                        )
                    )
                    OUT,
            )
        ->build();

        yield 'empty method' => NodeDumperScenario::forNode(
            new Node\Stmt\ClassMethod(
                new Node\Identifier('salute'),
            ),
        )
            ->withShowAllNodes()
            ->withExpected(
                <<<'OUT'
                    Stmt_ClassMethod(
                        name: Identifier
                    )
                    OUT,
            )
        ->build();
    }
}
