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

namespace Infection\Tests\PhpParser\NodeDumper\NodeDumper;

use Exception;
use Infection\PhpParser\NodeDumper\NodeDumper;
use Infection\PhpParser\NodeDumper\PotentialCircularDependencyDetected;
use Infection\PhpParser\Visitor\AddIdToTraversedNodesVisitor\AddIdToTraversedNodesVisitor;
use Infection\PhpParser\Visitor\FullyQualifiedClassNameManipulator;
use Infection\PhpParser\Visitor\LabelNodesAsEligibleVisitor;
use Infection\PhpParser\Visitor\MarkTraversedNodesAsVisitedVisitor;
use Infection\PhpParser\Visitor\NextConnectingVisitor;
use Infection\PhpParser\Visitor\ParentConnector;
use Infection\PhpParser\Visitor\ReflectionVisitor;
use function is_string;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\ArrayItem;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use PhpParser\ParserFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(NodeDumper::class)]
final class NodeDumperTest extends TestCase
{
    #[DataProvider('codeWithDefaultConfigurationProvider')]
    #[DataProvider('decoratedNodesProvider')]
    #[DataProvider('nodesWithAttributesWhichMayCauseCircularDependenciesProvider')]
    #[DataProvider('resolvedNameAttributeProvider')]
    #[DataProvider('showLineNumbersProvider')]
    public function test_dump_nodes(NodeDumperScenario $scenario): void
    {
        $node = $scenario->node;
        $expected = $scenario->expected;

        if (is_string($node)) {
            $parser = (new ParserFactory())->createForHostVersion();

            $node = $parser->parse($node);
        }

        $this->assertNotNull($node);

        $dumper = new NodeDumper(
            $scenario->dumpProperties,
            $scenario->dumpComments,
            $scenario->dumpPositions,
            $scenario->dumpOtherAttributes,
            $scenario->onlyVisitedNodes,
            $scenario->decorateNodes,
            $scenario->showLineNumbers,
        );

        if ($expected instanceof Exception) {
            $this->expectExceptionObject($expected);
        }

        $actual = $dumper->dump($node);

        if (!($expected instanceof Exception)) {
            $this->assertSame($expected, $actual);
        }
    }

    public static function codeWithDefaultConfigurationProvider(): iterable
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
                <<<'AST'
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
                    AST,
            )
            ->build();

        yield 'variable with comments' => $variableAssignment
            ->withDumpComments()
            ->withExpected(
                <<<'AST'
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
                                0: // Salutation
                            )
                        )
                    )
                    AST,
            )
            ->build();

        yield 'variable with positions' => $variableAssignment
            ->withDumpPositions()
            ->withExpected(
                <<<'AST'
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
                    AST,
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
                <<<'AST'
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
                    AST,
            )
            ->build();

        yield 'variable other attributes' => NodeDumperScenario::forNode(
            [
                new Assign(
                    new Variable('x'),
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
                <<<'AST'
                    array(
                        0: Expr_Assign(
                            var: Expr_Variable(
                                name: x
                            )
                            expr: Scalar_String(
                                value: Hello World!
                                unspecifiedAttribute: Hi
                            )
                            anotherUnspecifiedAttribute: ...
                        )
                    )
                    AST,
            )
            ->build();

        yield 'empty array' => NodeDumperScenario::forNode([])
            ->withShowAllNodes()
            ->withExpected(
                <<<'AST'
                    array(
                    )
                    AST,
            )
            ->build();

        yield 'array with values' => NodeDumperScenario::forNode(
            // @phpstan-ignore argument.type
            ['Foo', 'Bar', 'Key' => 'FooBar'],
        )
            ->withShowAllNodes()
            ->withExpected(
                <<<'AST'
                    array(
                        0: Foo
                        1: Bar
                        Key: FooBar
                    )
                    AST,
            )
            ->build();

        yield 'name' => NodeDumperScenario::forNode(
            new Name(['Hallo', 'World']),
        )
            ->withShowAllNodes()
            ->withExpected(
                <<<'AST'
                    Name
                    AST,
            )
            ->build();

        yield 'name with extra properties' => NodeDumperScenario::forNode(
            new Name(['Hallo', 'World']),
        )
            ->withDumpProperties()
            ->withShowAllNodes()
            ->withExpected(
                <<<'AST'
                    Name(
                        name: Hallo\World
                    )
                    AST,
            )
            ->build();

        yield 'array expression' => NodeDumperScenario::forNode(
            new Array_([
                new ArrayItem(new String_('Foo')),
            ]),
        )
            ->withShowAllNodes()
            ->withExpected(
                <<<'AST'
                    Expr_Array(
                        items: array(
                            0: ArrayItem(
                                value: Scalar_String
                            )
                        )
                    )
                    AST,
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
                <<<'AST'
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
                    AST,
            )
            ->build();

        yield 'empty method' => NodeDumperScenario::forNode(
            new Node\Stmt\ClassMethod(
                new Node\Identifier('salute'),
            ),
        )
            ->withShowAllNodes()
            ->withExpected(
                <<<'AST'
                    Stmt_ClassMethod(
                        name: Identifier
                    )
                    AST,
            )
            ->build();
    }

    public static function decoratedNodesProvider(): iterable
    {
        yield 'eligible nodes are not decorated by default' => (static function () {
            $node = new Assign(
                new Variable('x'),
                new String_('value'),
            );
            LabelNodesAsEligibleVisitor::markAsEligible($node);

            return NodeDumperScenario::forNode([$node])
                ->withShowAllNodes()
                ->withExpected(
                    <<<'AST'
                        array(
                            0: Expr_Assign(
                                var: Expr_Variable
                                expr: Scalar_String
                            )
                        )
                        AST,
                )
                ->build();
        })();

        yield 'eligible nodes can be decorated' => (static function () {
            $node = new Assign(
                new Variable('x'),
                new String_('value'),
            );
            $eligibleNode = new Assign(
                new Variable('x'),
                new String_('value'),
            );

            LabelNodesAsEligibleVisitor::markAsEligible($eligibleNode);

            return NodeDumperScenario::forNode([$node, $eligibleNode])
                ->withShowAllNodes()
                ->withDecorateNodes()
                ->withExpected(
                    <<<'AST'
                        array(
                            0: Expr_Assign(
                                var: Expr_Variable
                                expr: Scalar_String
                            )
                            1: <eligible>Expr_Assign</eligible>(
                                var: Expr_Variable
                                expr: Scalar_String
                            )
                        )
                        AST,
                )
                ->build();
        })();
    }

    public static function nodesWithAttributesWhichMayCauseCircularDependenciesProvider(): iterable
    {
        yield 'next attribute' => (static function () {
            $node1 = new Node\Stmt\Expression(
                new Assign(
                    new Variable('x1'),
                    new String_('value1'),
                ),
            );
            $node2 = new Node\Stmt\Expression(
                new Assign(
                    new Variable('x1'),
                    new String_('value1'),
                ),
            );

            $node1->setAttribute(
                NextConnectingVisitor::NEXT_ATTRIBUTE,
                $node2,
            );

            return NodeDumperScenario::forNode([
                $node1,
                $node2,
            ])
                ->withShowAllNodes()
                ->withDumpProperties()
                ->withDumpOtherAttributes()
                ->withExpected(
                    PotentialCircularDependencyDetected::forAttribute(
                        NextConnectingVisitor::NEXT_ATTRIBUTE,
                        $node2,
                    ),
                )
                ->build();
        })();

        yield 'next attribute with ID' => (static function () {
            $node1 = new Node\Stmt\Expression(
                new Assign(
                    new Variable('x1'),
                    new String_('value1'),
                ),
            );
            $node2 = new Node\Stmt\Expression(
                new Assign(
                    new Variable('x1'),
                    new String_('value1'),
                ),
            );

            $node1->setAttribute(
                NextConnectingVisitor::NEXT_ATTRIBUTE,
                $node2,
            );
            $node2->setAttribute(AddIdToTraversedNodesVisitor::NODE_ID_ATTRIBUTE, 10);

            return NodeDumperScenario::forNode([
                $node1,
                $node2,
            ])
                ->withShowAllNodes()
                ->withDumpProperties()
                ->withDumpOtherAttributes()
                ->withExpected(
                    <<<'AST'
                        array(
                            0: Stmt_Expression(
                                expr: Expr_Assign(
                                    var: Expr_Variable(
                                        name: x1
                                    )
                                    expr: Scalar_String(
                                        value: value1
                                    )
                                )
                                next: nodeId(10)
                            )
                            1: Stmt_Expression(
                                expr: Expr_Assign(
                                    var: Expr_Variable(
                                        name: x1
                                    )
                                    expr: Scalar_String(
                                        value: value1
                                    )
                                )
                                nodeId: 10
                            )
                        )
                        AST,
                )
                ->build();
        })();

        yield 'parent attribute' => (static function () {
            $parent = new Node\Stmt\Expression(
                new Assign(
                    new Variable('x1'),
                    new String_('value1'),
                ),
            );
            $child = new Node\Stmt\Expression(
                new Assign(
                    new Variable('x1'),
                    new String_('value1'),
                ),
            );

            ParentConnector::setParent($child, $parent);

            return NodeDumperScenario::forNode([
                $parent,
                $child,
            ])
                ->withShowAllNodes()
                ->withDumpProperties()
                ->withDumpOtherAttributes()
                ->withExpected(
                    PotentialCircularDependencyDetected::forAttribute('parent', $parent),
                )
                ->build();
        })();

        yield 'parent attribute with ID' => (static function () {
            $parent = new Node\Stmt\Expression(
                new Assign(
                    new Variable('x1'),
                    new String_('value1'),
                ),
            );
            $child = new Node\Stmt\Expression(
                new Assign(
                    new Variable('x1'),
                    new String_('value1'),
                ),
            );

            $parent->setAttribute(AddIdToTraversedNodesVisitor::NODE_ID_ATTRIBUTE, 10);
            ParentConnector::setParent($child, $parent);

            return NodeDumperScenario::forNode([
                $parent,
                $child,
            ])
                ->withShowAllNodes()
                ->withDumpProperties()
                ->withDumpOtherAttributes()
                ->withExpected(
                    <<<'AST'
                        array(
                            0: Stmt_Expression(
                                expr: Expr_Assign(
                                    var: Expr_Variable(
                                        name: x1
                                    )
                                    expr: Scalar_String(
                                        value: value1
                                    )
                                )
                                nodeId: 10
                            )
                            1: Stmt_Expression(
                                expr: Expr_Assign(
                                    var: Expr_Variable(
                                        name: x1
                                    )
                                    expr: Scalar_String(
                                        value: value1
                                    )
                                )
                                parent: nodeId(10)
                            )
                        )
                        AST,
                )
                ->build();
        })();

        yield 'functionScope' => (static function () {
            $node = new Node\Stmt\Expression(
                new Assign(
                    new Variable('x1'),
                    new String_('value1'),
                ),
            );

            $node->setAttribute(
                ReflectionVisitor::FUNCTION_SCOPE_KEY,
                $node,
            );

            return NodeDumperScenario::forNode([$node])
                ->withShowAllNodes()
                ->withDumpProperties()
                ->withDumpOtherAttributes()
                ->withExpected(
                    PotentialCircularDependencyDetected::forAttribute(
                        ReflectionVisitor::FUNCTION_SCOPE_KEY,
                        $node,
                    ),
                )
                ->build();
        })();

        yield 'functionScope with ID' => (static function () {
            $node = new Node\Stmt\Expression(
                new Assign(
                    new Variable('x1'),
                    new String_('value1'),
                ),
            );

            $node->setAttribute(
                AddIdToTraversedNodesVisitor::NODE_ID_ATTRIBUTE,
                10,
            );
            $node->setAttribute(
                ReflectionVisitor::FUNCTION_SCOPE_KEY,
                $node,
            );

            return NodeDumperScenario::forNode([$node])
                ->withShowAllNodes()
                ->withDumpProperties()
                ->withDumpOtherAttributes()
                ->withExpected(
                    <<<'AST'
                        array(
                            0: Stmt_Expression(
                                expr: Expr_Assign(
                                    var: Expr_Variable(
                                        name: x1
                                    )
                                    expr: Scalar_String(
                                        value: value1
                                    )
                                )
                                functionScope: nodeId(10)
                                nodeId: 10
                            )
                        )
                        AST,
                )
                ->build();
        })();
    }

    public static function resolvedNameAttributeProvider(): iterable
    {
        yield 'resolvedName with FullyQualified name' => (static function () {
            $node = new Name('Foo');
            $node->setAttribute(FullyQualifiedClassNameManipulator::RESOLVED_NAME, new FullyQualified('App\\Foo'));

            return NodeDumperScenario::forNode([$node])
                ->withShowAllNodes()
                ->withDumpOtherAttributes()
                ->withExpected(
                    <<<'AST'
                        array(
                            0: Name(
                                resolvedName: FullyQualified(App\Foo)
                            )
                        )
                        AST,
                )
                ->build();
        })();

        yield 'resolvedName with identified FullyQualified name' => (static function () {
            $node = new Name('Foo');
            $node->setAttribute(FullyQualifiedClassNameManipulator::RESOLVED_NAME, new FullyQualified('App\\Foo'));
            $node->setAttribute(AddIdToTraversedNodesVisitor::NODE_ID_ATTRIBUTE, 10);

            return NodeDumperScenario::forNode([$node])
                ->withShowAllNodes()
                ->withDumpOtherAttributes()
                ->withExpected(
                    <<<'AST'
                        array(
                            0: Name(
                                nodeId: 10
                                resolvedName: FullyQualified(App\Foo)
                            )
                        )
                        AST,
                )
                ->build();
        })();

        yield 'resolvedName with plain Name' => (static function () {
            $node = new Name('Bar');
            $node->setAttribute(FullyQualifiedClassNameManipulator::RESOLVED_NAME, new Name('Bar'));

            return NodeDumperScenario::forNode([$node])
                ->withShowAllNodes()
                ->withDumpOtherAttributes()
                ->withExpected(
                    <<<'AST'
                        array(
                            0: Name(
                                resolvedName: Name(Bar)
                            )
                        )
                        AST,
                )
                ->build();
        })();

        yield 'resolvedName with plain identified Name' => (static function () {
            $node = new Name('Bar');
            $node->setAttribute(FullyQualifiedClassNameManipulator::RESOLVED_NAME, new Name('Bar'));
            $node->setAttribute(AddIdToTraversedNodesVisitor::NODE_ID_ATTRIBUTE, 10);

            return NodeDumperScenario::forNode([$node])
                ->withShowAllNodes()
                ->withDumpOtherAttributes()
                ->withExpected(
                    <<<'AST'
                        array(
                            0: Name(
                                nodeId: 10
                                resolvedName: Name(Bar)
                            )
                        )
                        AST,
                )
                ->build();
        })();

        yield 'resolvedName is not shown without dumpOtherAttributes' => (static function () {
            $node = new Name('Foo');
            $node->setAttribute(FullyQualifiedClassNameManipulator::RESOLVED_NAME, new FullyQualified('App\\Foo'));

            return NodeDumperScenario::forNode([$node])
                ->withShowAllNodes()
                ->withExpected(
                    <<<'AST'
                        array(
                            0: Name
                        )
                        AST,
                )
                ->build();
        })();

        yield 'namespacedName with FullyQualified name' => (static function () {
            $node = new Node\Stmt\Class_('Foo');
            $node->setAttribute(FullyQualifiedClassNameManipulator::RESOLVED_NAMESPACE_NAME, new FullyQualified('App\\Foo'));

            return NodeDumperScenario::forNode([$node])
                ->withShowAllNodes()
                ->withDumpOtherAttributes()
                ->withExpected(
                    <<<'AST'
                        array(
                            0: Stmt_Class(
                                name: Identifier
                                namespacedName: FullyQualified(App\Foo)
                            )
                        )
                        AST,
                )
                ->build();
        })();

        yield 'namespacedName with identified FullyQualified name' => (static function () {
            $node = new Node\Stmt\Class_('Foo');
            $node->setAttribute(FullyQualifiedClassNameManipulator::RESOLVED_NAMESPACE_NAME, new FullyQualified('App\\Foo'));
            $node->setAttribute(AddIdToTraversedNodesVisitor::NODE_ID_ATTRIBUTE, 10);

            return NodeDumperScenario::forNode([$node])
                ->withShowAllNodes()
                ->withDumpOtherAttributes()
                ->withExpected(
                    <<<'AST'
                        array(
                            0: Stmt_Class(
                                name: Identifier
                                namespacedName: FullyQualified(App\Foo)
                                nodeId: 10
                            )
                        )
                        AST,
                )
                ->build();
        })();

        yield 'namespacedName with plain Name' => (static function () {
            $node = new Node\Stmt\Function_('bar');
            $node->setAttribute(FullyQualifiedClassNameManipulator::RESOLVED_NAMESPACE_NAME, new Name('bar'));

            return NodeDumperScenario::forNode([$node])
                ->withShowAllNodes()
                ->withDumpOtherAttributes()
                ->withExpected(
                    <<<'AST'
                        array(
                            0: Stmt_Function(
                                name: Identifier
                                namespacedName: Name(bar)
                            )
                        )
                        AST,
                )
                ->build();
        })();

        yield 'namespacedName with plain identified Name' => (static function () {
            $node = new Node\Stmt\Function_('bar');
            $node->setAttribute(FullyQualifiedClassNameManipulator::RESOLVED_NAMESPACE_NAME, new Name('bar'));
            $node->setAttribute(AddIdToTraversedNodesVisitor::NODE_ID_ATTRIBUTE, 10);

            return NodeDumperScenario::forNode([$node])
                ->withShowAllNodes()
                ->withDumpOtherAttributes()
                ->withExpected(
                    <<<'AST'
                        array(
                            0: Stmt_Function(
                                name: Identifier
                                namespacedName: Name(bar)
                                nodeId: 10
                            )
                        )
                        AST,
                )
                ->build();
        })();

        yield 'namespacedName is not shown without dumpOtherAttributes' => (static function () {
            $node = new Node\Stmt\Class_('Foo');
            $node->setAttribute(FullyQualifiedClassNameManipulator::RESOLVED_NAMESPACE_NAME, new FullyQualified('App\\Foo'));

            return NodeDumperScenario::forNode([$node])
                ->withShowAllNodes()
                ->withExpected(
                    <<<'AST'
                        array(
                            0: Stmt_Class(
                                name: Identifier
                            )
                        )
                        AST,
                )
                ->build();
        })();
    }

    public static function showLineNumbersProvider(): iterable
    {
        yield 'line numbers are shown when enabled' => NodeDumperScenario::forCode(
            <<<'PHP'
                <?php

                $a = 1;
                PHP,
        )
            ->withShowAllNodes()
            ->withDumpProperties()
            ->withDumpOtherAttributes()
            ->withShowLineNumbers()
            ->withExpected(
                <<<'AST'
                    array(
                        0: Stmt_Expression(
                            expr: Expr_Assign(
                                var: Expr_Variable(
                                    name: a
                                    endLine: 3
                                    startLine: 3
                                )
                                expr: Scalar_Int(
                                    value: 1
                                    endLine: 3
                                    kind: KIND_DEC (10)
                                    rawValue: 1
                                    startLine: 3
                                )
                                endLine: 3
                                startLine: 3
                            )
                            endLine: 3
                            startLine: 3
                        )
                    )
                    AST,
            )
            ->build();

        yield 'line numbers are hidden by default' => NodeDumperScenario::forCode(
            <<<'PHP'
                <?php

                $a = 1;
                PHP,
        )
            ->withShowAllNodes()
            ->withDumpProperties()
            ->withDumpOtherAttributes()
            ->withExpected(
                <<<'AST'
                    array(
                        0: Stmt_Expression(
                            expr: Expr_Assign(
                                var: Expr_Variable(
                                    name: a
                                )
                                expr: Scalar_Int(
                                    value: 1
                                    kind: KIND_DEC (10)
                                    rawValue: 1
                                )
                            )
                        )
                    )
                    AST,
            )
            ->build();

        yield 'line numbers with multi-line code' => NodeDumperScenario::forCode(
            <<<'PHP'
                <?php

                $a = [
                    'hello',
                ];
                PHP,
        )
            ->withShowAllNodes()
            ->withDumpProperties()
            ->withDumpOtherAttributes()
            ->withShowLineNumbers()
            ->withExpected(
                <<<'AST'
                    array(
                        0: Stmt_Expression(
                            expr: Expr_Assign(
                                var: Expr_Variable(
                                    name: a
                                    endLine: 3
                                    startLine: 3
                                )
                                expr: Expr_Array(
                                    items: array(
                                        0: ArrayItem(
                                            key: null
                                            value: Scalar_String(
                                                value: hello
                                                endLine: 4
                                                kind: KIND_SINGLE_QUOTED (1)
                                                rawValue: 'hello'
                                                startLine: 4
                                            )
                                            byRef: false
                                            unpack: false
                                            endLine: 4
                                            startLine: 4
                                        )
                                    )
                                    endLine: 5
                                    kind: KIND_SHORT (2)
                                    startLine: 3
                                )
                                endLine: 5
                                startLine: 3
                            )
                            endLine: 5
                            startLine: 3
                        )
                    )
                    AST,
            )
            ->build();
    }
}
