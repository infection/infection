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

use Infection\PhpParser\Visitor\ReflectionVisitor;
use Infection\Tests\TestingUtility\PhpParser\Visitor\MarkTraversedNodesAsVisitedVisitor\MarkTraversedNodesAsVisitedVisitor;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\NodeVisitor\ParentConnectingVisitor;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use function Safe\file_get_contents;

#[Group('integration')]
#[CoversClass(ReflectionVisitor::class)]
final class ReflectionVisitorTest extends VisitorTestCase
{
    private const FIXTURES_DIR = __DIR__ . '/../../../autoloaded/mutator-fixtures';

    /**
     * @param list<string>|null $desiredAttributes
     */
    #[DataProvider('nodeProvider')]
    public function test_it_annotates_excluded_nodes_and_stops_the_traversal(
        string $code,
        ?array $desiredAttributes,
        string $expected,
    ): void {
        $nodes = $this->parse($code);

        $this->addIdsToNodes($nodes);

        (new NodeTraverser(
            new ParentConnectingVisitor(),
            self::createNameResolver(),
            new ReflectionVisitor(),
            new MarkTraversedNodesAsVisitedVisitor(),
        ))->traverse($nodes);

        if ($desiredAttributes !== null) {
            $this->keepOnlyDesiredAttributes(
                $nodes,
                ...$desiredAttributes,
            );
        }

        $actual = $this->dumper->dump($nodes);

        $this->assertSame($expected, $actual);
    }

    public static function nodeProvider(): iterable
    {
        yield 'it marks nodes which are part of the function signature' => [
            file_get_contents(
                self::FIXTURES_DIR . '/Reflection/rv-part-of-signature-flag.php',
            ),
            null,
            <<<'AST'
                array(
                    0: Stmt_Namespace(
                        name: Name(
                            nodeId: 1
                            parent: nodeId(0)
                        )
                        stmts: array(
                            0: Stmt_Class(
                                name: Identifier(
                                    nodeId: 3
                                    parent: nodeId(2)
                                )
                                stmts: array(
                                    0: Stmt_ClassMethod(
                                        name: Identifier(
                                            nodeId: 5
                                            parent: nodeId(4)
                                            isInsideFunction: true
                                            isStrictTypes: false
                                            functionScope: nodeId(4)
                                            reflectionClass: Infection\Reflection\CoreClassReflection
                                            functionName: foo
                                        )
                                        params: array(
                                            0: Param(
                                                type: Identifier(
                                                    nodeId: 7
                                                    parent: nodeId(6)
                                                    isInsideFunction: true
                                                    isOnFunctionSignature: true
                                                    isStrictTypes: false
                                                    functionScope: nodeId(4)
                                                    reflectionClass: Infection\Reflection\CoreClassReflection
                                                    functionName: foo
                                                )
                                                var: Expr_Variable(
                                                    nodeId: 8
                                                    parent: nodeId(6)
                                                    isInsideFunction: true
                                                    isOnFunctionSignature: true
                                                    isStrictTypes: false
                                                    functionScope: nodeId(4)
                                                    reflectionClass: Infection\Reflection\CoreClassReflection
                                                    functionName: foo
                                                )
                                                nodeId: 6
                                                parent: nodeId(4)
                                                isInsideFunction: true
                                                isOnFunctionSignature: true
                                                isStrictTypes: false
                                                functionScope: nodeId(4)
                                                reflectionClass: Infection\Reflection\CoreClassReflection
                                                functionName: foo
                                            )
                                            1: Param(
                                                var: Expr_Variable(
                                                    nodeId: 10
                                                    parent: nodeId(9)
                                                    isInsideFunction: true
                                                    isOnFunctionSignature: true
                                                    isStrictTypes: false
                                                    functionScope: nodeId(4)
                                                    reflectionClass: Infection\Reflection\CoreClassReflection
                                                    functionName: foo
                                                )
                                                default: Scalar_Float(
                                                    rawValue: 2.0
                                                    nodeId: 11
                                                    parent: nodeId(9)
                                                    isInsideFunction: true
                                                    isOnFunctionSignature: true
                                                    isStrictTypes: false
                                                    functionScope: nodeId(4)
                                                    reflectionClass: Infection\Reflection\CoreClassReflection
                                                    functionName: foo
                                                )
                                                nodeId: 9
                                                parent: nodeId(4)
                                                isInsideFunction: true
                                                isOnFunctionSignature: true
                                                isStrictTypes: false
                                                functionScope: nodeId(4)
                                                reflectionClass: Infection\Reflection\CoreClassReflection
                                                functionName: foo
                                            )
                                        )
                                        returnType: Identifier(
                                            nodeId: 12
                                            parent: nodeId(4)
                                            isInsideFunction: true
                                            isStrictTypes: false
                                            functionScope: nodeId(4)
                                            reflectionClass: Infection\Reflection\CoreClassReflection
                                            functionName: foo
                                        )
                                        stmts: array(
                                            0: Stmt_Return(
                                                expr: Expr_BinaryOp_Identical(
                                                    left: Expr_FuncCall(
                                                        name: Name(
                                                            nodeId: 16
                                                            namespacedName: nodeId(16)
                                                            parent: nodeId(15)
                                                            isInsideFunction: true
                                                            isStrictTypes: false
                                                            functionScope: nodeId(4)
                                                            reflectionClass: Infection\Reflection\CoreClassReflection
                                                            functionName: foo
                                                        )
                                                        args: array(
                                                            0: Arg(
                                                                value: Expr_Array(
                                                                    kind: KIND_SHORT (2)
                                                                    nodeId: 18
                                                                    parent: nodeId(17)
                                                                    isInsideFunction: true
                                                                    isStrictTypes: false
                                                                    functionScope: nodeId(4)
                                                                    reflectionClass: Infection\Reflection\CoreClassReflection
                                                                    functionName: foo
                                                                )
                                                                nodeId: 17
                                                                parent: nodeId(15)
                                                                isInsideFunction: true
                                                                isStrictTypes: false
                                                                functionScope: nodeId(4)
                                                                reflectionClass: Infection\Reflection\CoreClassReflection
                                                                functionName: foo
                                                            )
                                                        )
                                                        nodeId: 15
                                                        parent: nodeId(14)
                                                        isInsideFunction: true
                                                        isStrictTypes: false
                                                        functionScope: nodeId(4)
                                                        reflectionClass: Infection\Reflection\CoreClassReflection
                                                        functionName: foo
                                                    )
                                                    right: Scalar_Int(
                                                        rawValue: 1
                                                        kind: KIND_DEC (10)
                                                        nodeId: 19
                                                        parent: nodeId(14)
                                                        isInsideFunction: true
                                                        isStrictTypes: false
                                                        functionScope: nodeId(4)
                                                        reflectionClass: Infection\Reflection\CoreClassReflection
                                                        functionName: foo
                                                    )
                                                    nodeId: 14
                                                    parent: nodeId(13)
                                                    isInsideFunction: true
                                                    isStrictTypes: false
                                                    functionScope: nodeId(4)
                                                    reflectionClass: Infection\Reflection\CoreClassReflection
                                                    functionName: foo
                                                )
                                                nodeId: 13
                                                parent: nodeId(4)
                                                isInsideFunction: true
                                                isStrictTypes: false
                                                functionScope: nodeId(4)
                                                reflectionClass: Infection\Reflection\CoreClassReflection
                                                functionName: foo
                                            )
                                        )
                                        nodeId: 4
                                        parent: nodeId(2)
                                        isOnFunctionSignature: true
                                        isStrictTypes: false
                                        reflectionClass: Infection\Reflection\CoreClassReflection
                                        functionName: foo
                                    )
                                )
                                nodeId: 2
                                parent: nodeId(0)
                            )
                        )
                        kind: 1
                        nodeId: 0
                    )
                )
                AST,
        ];

        yield 'it marks nodes which are part of the function signature with attributes' => [
            file_get_contents(
                self::FIXTURES_DIR . '/Reflection/rv-part-of-signature-flag-with-attributes.php',
            ),
            null,
            <<<'AST'
                array(
                    0: Stmt_Namespace(
                        name: Name(
                            nodeId: 1
                            parent: nodeId(0)
                        )
                        stmts: array(
                            0: Stmt_Class(
                                name: Identifier(
                                    nodeId: 3
                                    parent: nodeId(2)
                                )
                                stmts: array(
                                    0: Stmt_ClassMethod(
                                        name: Identifier(
                                            nodeId: 5
                                            parent: nodeId(4)
                                            isInsideFunction: true
                                            isStrictTypes: false
                                            functionScope: nodeId(4)
                                            reflectionClass: Infection\Reflection\CoreClassReflection
                                            functionName: foo
                                        )
                                        params: array(
                                            0: Param(
                                                type: Identifier(
                                                    nodeId: 7
                                                    parent: nodeId(6)
                                                    isInsideFunction: true
                                                    isOnFunctionSignature: true
                                                    isStrictTypes: false
                                                    functionScope: nodeId(4)
                                                    reflectionClass: Infection\Reflection\CoreClassReflection
                                                    functionName: foo
                                                )
                                                var: Expr_Variable(
                                                    nodeId: 8
                                                    parent: nodeId(6)
                                                    isInsideFunction: true
                                                    isOnFunctionSignature: true
                                                    isStrictTypes: false
                                                    functionScope: nodeId(4)
                                                    reflectionClass: Infection\Reflection\CoreClassReflection
                                                    functionName: foo
                                                )
                                                nodeId: 6
                                                parent: nodeId(4)
                                                isInsideFunction: true
                                                isOnFunctionSignature: true
                                                isStrictTypes: false
                                                functionScope: nodeId(4)
                                                reflectionClass: Infection\Reflection\CoreClassReflection
                                                functionName: foo
                                            )
                                            1: Param(
                                                attrGroups: array(
                                                    0: AttributeGroup(
                                                        attrs: array(
                                                            0: Attribute(
                                                                name: Name(
                                                                    nodeId: 12
                                                                    resolvedName: nodeId(12)
                                                                    parent: nodeId(11)
                                                                    isInsideFunction: true
                                                                    isOnFunctionSignature: true
                                                                    isStrictTypes: false
                                                                    functionScope: nodeId(4)
                                                                    reflectionClass: Infection\Reflection\CoreClassReflection
                                                                    functionName: foo
                                                                )
                                                                args: array(
                                                                    0: Arg(
                                                                        value: Expr_ConstFetch(
                                                                            name: Name(
                                                                                nodeId: 15
                                                                                namespacedName: nodeId(15)
                                                                                parent: nodeId(14)
                                                                                isInsideFunction: true
                                                                                isOnFunctionSignature: true
                                                                                isStrictTypes: false
                                                                                functionScope: nodeId(4)
                                                                                reflectionClass: Infection\Reflection\CoreClassReflection
                                                                                functionName: foo
                                                                            )
                                                                            nodeId: 14
                                                                            parent: nodeId(13)
                                                                            isInsideFunction: true
                                                                            isOnFunctionSignature: true
                                                                            isStrictTypes: false
                                                                            functionScope: nodeId(4)
                                                                            reflectionClass: Infection\Reflection\CoreClassReflection
                                                                            functionName: foo
                                                                        )
                                                                        nodeId: 13
                                                                        parent: nodeId(11)
                                                                        isInsideFunction: true
                                                                        isOnFunctionSignature: true
                                                                        isStrictTypes: false
                                                                        functionScope: nodeId(4)
                                                                        reflectionClass: Infection\Reflection\CoreClassReflection
                                                                        functionName: foo
                                                                    )
                                                                )
                                                                nodeId: 11
                                                                parent: nodeId(10)
                                                                isInsideFunction: true
                                                                isOnFunctionSignature: true
                                                                isStrictTypes: false
                                                                functionScope: nodeId(4)
                                                                reflectionClass: Infection\Reflection\CoreClassReflection
                                                                functionName: foo
                                                            )
                                                        )
                                                        nodeId: 10
                                                        parent: nodeId(9)
                                                        isInsideFunction: true
                                                        isOnFunctionSignature: true
                                                        isStrictTypes: false
                                                        functionScope: nodeId(4)
                                                        reflectionClass: Infection\Reflection\CoreClassReflection
                                                        functionName: foo
                                                    )
                                                )
                                                var: Expr_Variable(
                                                    nodeId: 16
                                                    parent: nodeId(9)
                                                    isInsideFunction: true
                                                    isOnFunctionSignature: true
                                                    isStrictTypes: false
                                                    functionScope: nodeId(4)
                                                    reflectionClass: Infection\Reflection\CoreClassReflection
                                                    functionName: foo
                                                )
                                                default: Scalar_Float(
                                                    rawValue: 2.0
                                                    nodeId: 17
                                                    parent: nodeId(9)
                                                    isInsideFunction: true
                                                    isOnFunctionSignature: true
                                                    isStrictTypes: false
                                                    functionScope: nodeId(4)
                                                    reflectionClass: Infection\Reflection\CoreClassReflection
                                                    functionName: foo
                                                )
                                                nodeId: 9
                                                parent: nodeId(4)
                                                isInsideFunction: true
                                                isOnFunctionSignature: true
                                                isStrictTypes: false
                                                functionScope: nodeId(4)
                                                reflectionClass: Infection\Reflection\CoreClassReflection
                                                functionName: foo
                                            )
                                        )
                                        returnType: Identifier(
                                            nodeId: 18
                                            parent: nodeId(4)
                                            isInsideFunction: true
                                            isStrictTypes: false
                                            functionScope: nodeId(4)
                                            reflectionClass: Infection\Reflection\CoreClassReflection
                                            functionName: foo
                                        )
                                        stmts: array(
                                            0: Stmt_Return(
                                                expr: Expr_BinaryOp_Identical(
                                                    left: Expr_FuncCall(
                                                        name: Name(
                                                            nodeId: 22
                                                            namespacedName: nodeId(22)
                                                            parent: nodeId(21)
                                                            isInsideFunction: true
                                                            isStrictTypes: false
                                                            functionScope: nodeId(4)
                                                            reflectionClass: Infection\Reflection\CoreClassReflection
                                                            functionName: foo
                                                        )
                                                        args: array(
                                                            0: Arg(
                                                                value: Expr_Array(
                                                                    kind: KIND_SHORT (2)
                                                                    nodeId: 24
                                                                    parent: nodeId(23)
                                                                    isInsideFunction: true
                                                                    isStrictTypes: false
                                                                    functionScope: nodeId(4)
                                                                    reflectionClass: Infection\Reflection\CoreClassReflection
                                                                    functionName: foo
                                                                )
                                                                nodeId: 23
                                                                parent: nodeId(21)
                                                                isInsideFunction: true
                                                                isStrictTypes: false
                                                                functionScope: nodeId(4)
                                                                reflectionClass: Infection\Reflection\CoreClassReflection
                                                                functionName: foo
                                                            )
                                                        )
                                                        nodeId: 21
                                                        parent: nodeId(20)
                                                        isInsideFunction: true
                                                        isStrictTypes: false
                                                        functionScope: nodeId(4)
                                                        reflectionClass: Infection\Reflection\CoreClassReflection
                                                        functionName: foo
                                                    )
                                                    right: Scalar_Int(
                                                        rawValue: 1
                                                        kind: KIND_DEC (10)
                                                        nodeId: 25
                                                        parent: nodeId(20)
                                                        isInsideFunction: true
                                                        isStrictTypes: false
                                                        functionScope: nodeId(4)
                                                        reflectionClass: Infection\Reflection\CoreClassReflection
                                                        functionName: foo
                                                    )
                                                    nodeId: 20
                                                    parent: nodeId(19)
                                                    isInsideFunction: true
                                                    isStrictTypes: false
                                                    functionScope: nodeId(4)
                                                    reflectionClass: Infection\Reflection\CoreClassReflection
                                                    functionName: foo
                                                )
                                                nodeId: 19
                                                parent: nodeId(4)
                                                isInsideFunction: true
                                                isStrictTypes: false
                                                functionScope: nodeId(4)
                                                reflectionClass: Infection\Reflection\CoreClassReflection
                                                functionName: foo
                                            )
                                        )
                                        nodeId: 4
                                        parent: nodeId(2)
                                        isOnFunctionSignature: true
                                        isStrictTypes: false
                                        reflectionClass: Infection\Reflection\CoreClassReflection
                                        functionName: foo
                                    )
                                )
                                nodeId: 2
                                parent: nodeId(0)
                            )
                        )
                        kind: 1
                        nodeId: 0
                    )
                )
                AST,
        ];

        yield 'it detects if it is traversing inside a class method' => [
            file_get_contents(
                self::FIXTURES_DIR . '/Reflection/rv-inside-class-method.php',
            ),
            null,
            <<<'AST'
                array(
                    0: Stmt_Namespace(
                        name: Name(
                            nodeId: 1
                            parent: nodeId(0)
                        )
                        stmts: array(
                            0: Stmt_Class(
                                name: Identifier(
                                    nodeId: 3
                                    parent: nodeId(2)
                                )
                                stmts: array(
                                    0: Stmt_ClassMethod(
                                        name: Identifier(
                                            nodeId: 5
                                            parent: nodeId(4)
                                            isInsideFunction: true
                                            isStrictTypes: false
                                            functionScope: nodeId(4)
                                            reflectionClass: Infection\Reflection\CoreClassReflection
                                            functionName: test
                                        )
                                        stmts: array(
                                            0: Stmt_Return(
                                                expr: Scalar_Int(
                                                    rawValue: 1
                                                    kind: KIND_DEC (10)
                                                    nodeId: 7
                                                    parent: nodeId(6)
                                                    isInsideFunction: true
                                                    isStrictTypes: false
                                                    functionScope: nodeId(4)
                                                    reflectionClass: Infection\Reflection\CoreClassReflection
                                                    functionName: test
                                                )
                                                nodeId: 6
                                                parent: nodeId(4)
                                                isInsideFunction: true
                                                isStrictTypes: false
                                                functionScope: nodeId(4)
                                                reflectionClass: Infection\Reflection\CoreClassReflection
                                                functionName: test
                                            )
                                        )
                                        nodeId: 4
                                        parent: nodeId(2)
                                        isOnFunctionSignature: true
                                        isStrictTypes: false
                                        reflectionClass: Infection\Reflection\CoreClassReflection
                                        functionName: test
                                    )
                                )
                                nodeId: 2
                                parent: nodeId(0)
                            )
                        )
                        kind: 1
                        nodeId: 0
                    )
                )
                AST,
        ];

        yield 'it does not annotate nodes of a regular global or namespaced function' => [
            file_get_contents(
                self::FIXTURES_DIR . '/Reflection/rv-inside-function.php',
            ),
            null,
            <<<'AST'
                array(
                    0: Stmt_Namespace(
                        name: Name(
                            nodeId: 1
                            parent: nodeId(0)
                        )
                        stmts: array(
                            0: Stmt_Function(
                                name: Identifier(
                                    nodeId: 3
                                    parent: nodeId(2)
                                )
                                stmts: array(
                                    0: Stmt_Return(
                                        expr: Expr_Closure(
                                            params: array(
                                                0: Param(
                                                    var: Expr_Variable(
                                                        nodeId: 7
                                                        parent: nodeId(6)
                                                    )
                                                    nodeId: 6
                                                    parent: nodeId(5)
                                                )
                                            )
                                            stmts: array(
                                                0: Stmt_Return(
                                                    expr: Scalar_Int(
                                                        rawValue: 1
                                                        kind: KIND_DEC (10)
                                                        nodeId: 9
                                                        parent: nodeId(8)
                                                    )
                                                    nodeId: 8
                                                    parent: nodeId(5)
                                                )
                                            )
                                            nodeId: 5
                                            parent: nodeId(4)
                                        )
                                        nodeId: 4
                                        parent: nodeId(2)
                                    )
                                )
                                nodeId: 2
                                parent: nodeId(0)
                                isStrictTypes: false
                            )
                        )
                        kind: 1
                        nodeId: 0
                    )
                )
                AST,
        ];

        yield 'it traverses a plain function inside a class' => [
            file_get_contents(
                self::FIXTURES_DIR . '/Reflection/rv-inside-plain-function-in-class.php',
            ),
            null,
            <<<'AST'
                array(
                    0: Stmt_Namespace(
                        name: Name(
                            nodeId: 1
                            parent: nodeId(0)
                        )
                        stmts: array(
                            0: Stmt_Class(
                                name: Identifier(
                                    nodeId: 3
                                    parent: nodeId(2)
                                )
                                stmts: array(
                                    0: Stmt_ClassMethod(
                                        name: Identifier(
                                            nodeId: 5
                                            parent: nodeId(4)
                                            isInsideFunction: true
                                            isStrictTypes: false
                                            functionScope: nodeId(4)
                                            reflectionClass: Infection\Reflection\CoreClassReflection
                                            functionName: foo
                                        )
                                        stmts: array(
                                            0: Stmt_Function(
                                                name: Identifier(
                                                    nodeId: 7
                                                    parent: nodeId(6)
                                                    isInsideFunction: true
                                                    isStrictTypes: false
                                                    functionScope: nodeId(4)
                                                    reflectionClass: Infection\Reflection\CoreClassReflection
                                                    functionName: foo
                                                )
                                                stmts: array(
                                                    0: Stmt_Expression(
                                                        expr: Expr_FuncCall(
                                                            name: Name(
                                                                nodeId: 10
                                                                namespacedName: nodeId(10)
                                                                parent: nodeId(9)
                                                                isInsideFunction: true
                                                                isStrictTypes: false
                                                                functionScope: nodeId(4)
                                                                reflectionClass: Infection\Reflection\CoreClassReflection
                                                                functionName: foo
                                                            )
                                                            args: array(
                                                                0: Arg(
                                                                    value: Expr_Array(
                                                                        kind: KIND_SHORT (2)
                                                                        nodeId: 12
                                                                        parent: nodeId(11)
                                                                        isInsideFunction: true
                                                                        isStrictTypes: false
                                                                        functionScope: nodeId(4)
                                                                        reflectionClass: Infection\Reflection\CoreClassReflection
                                                                        functionName: foo
                                                                    )
                                                                    nodeId: 11
                                                                    parent: nodeId(9)
                                                                    isInsideFunction: true
                                                                    isStrictTypes: false
                                                                    functionScope: nodeId(4)
                                                                    reflectionClass: Infection\Reflection\CoreClassReflection
                                                                    functionName: foo
                                                                )
                                                            )
                                                            nodeId: 9
                                                            parent: nodeId(8)
                                                            isInsideFunction: true
                                                            isStrictTypes: false
                                                            functionScope: nodeId(4)
                                                            reflectionClass: Infection\Reflection\CoreClassReflection
                                                            functionName: foo
                                                        )
                                                        nodeId: 8
                                                        parent: nodeId(6)
                                                        isInsideFunction: true
                                                        isStrictTypes: false
                                                        functionScope: nodeId(4)
                                                        reflectionClass: Infection\Reflection\CoreClassReflection
                                                        functionName: foo
                                                    )
                                                )
                                                nodeId: 6
                                                parent: nodeId(4)
                                                isStrictTypes: false
                                                isInsideFunction: true
                                                functionScope: nodeId(4)
                                                reflectionClass: Infection\Reflection\CoreClassReflection
                                                functionName: foo
                                            )
                                        )
                                        nodeId: 4
                                        parent: nodeId(2)
                                        isOnFunctionSignature: true
                                        isStrictTypes: false
                                        reflectionClass: Infection\Reflection\CoreClassReflection
                                        functionName: foo
                                    )
                                )
                                nodeId: 2
                                parent: nodeId(0)
                            )
                        )
                        kind: 1
                        nodeId: 0
                    )
                )
                AST,
        ];

        yield 'it traverses a plain function inside a closure' => [
            file_get_contents(
                self::FIXTURES_DIR . '/Reflection/rv-inside-plain-function-in-closure.php',
            ),
            null,
            <<<'AST'
                array(
                    0: Stmt_Namespace(
                        name: Name(
                            nodeId: 1
                            parent: nodeId(0)
                        )
                        stmts: array(
                            0: Stmt_Expression(
                                expr: Expr_Assign(
                                    var: Expr_Variable(
                                        nodeId: 4
                                        parent: nodeId(3)
                                    )
                                    expr: Expr_Closure(
                                        params: array(
                                            0: Param(
                                                type: Identifier(
                                                    nodeId: 7
                                                    parent: nodeId(6)
                                                )
                                                var: Expr_Variable(
                                                    nodeId: 8
                                                    parent: nodeId(6)
                                                )
                                                nodeId: 6
                                                parent: nodeId(5)
                                            )
                                        )
                                        stmts: array(
                                            0: Stmt_Function(
                                                name: Identifier(
                                                    nodeId: 10
                                                    parent: nodeId(9)
                                                )
                                                stmts: array(
                                                    0: Stmt_Expression(
                                                        expr: Expr_FuncCall(
                                                            name: Name(
                                                                nodeId: 13
                                                                namespacedName: nodeId(13)
                                                                parent: nodeId(12)
                                                            )
                                                            args: array(
                                                                0: Arg(
                                                                    value: Expr_Array(
                                                                        kind: KIND_SHORT (2)
                                                                        nodeId: 15
                                                                        parent: nodeId(14)
                                                                    )
                                                                    nodeId: 14
                                                                    parent: nodeId(12)
                                                                )
                                                            )
                                                            nodeId: 12
                                                            parent: nodeId(11)
                                                        )
                                                        nodeId: 11
                                                        parent: nodeId(9)
                                                    )
                                                )
                                                nodeId: 9
                                                parent: nodeId(5)
                                                isStrictTypes: false
                                            )
                                        )
                                        nodeId: 5
                                        parent: nodeId(3)
                                    )
                                    nodeId: 3
                                    parent: nodeId(2)
                                )
                                nodeId: 2
                                parent: nodeId(0)
                            )
                        )
                        kind: 1
                        nodeId: 0
                    )
                )
                AST,
        ];

        yield 'it detects it is traversing inside a closure' => [
            file_get_contents(
                self::FIXTURES_DIR . '/Reflection/rv-inside-closure.php',
            ),
            null,
            <<<'AST'
                array(
                    0: Stmt_Namespace(
                        name: Name(
                            nodeId: 1
                            parent: nodeId(0)
                        )
                        stmts: array(
                            0: Stmt_Class(
                                name: Identifier(
                                    nodeId: 3
                                    parent: nodeId(2)
                                )
                                stmts: array(
                                    0: Stmt_ClassMethod(
                                        name: Identifier(
                                            nodeId: 5
                                            parent: nodeId(4)
                                            isInsideFunction: true
                                            isStrictTypes: false
                                            functionScope: nodeId(4)
                                            reflectionClass: Infection\Reflection\CoreClassReflection
                                            functionName: bar
                                        )
                                        stmts: array(
                                            0: Stmt_Return(
                                                expr: Expr_Closure(
                                                    stmts: array(
                                                        0: Stmt_Return(
                                                            expr: Scalar_Int(
                                                                rawValue: 1
                                                                kind: KIND_DEC (10)
                                                                nodeId: 9
                                                                parent: nodeId(8)
                                                                isInsideFunction: true
                                                                isStrictTypes: false
                                                                functionScope: nodeId(7)
                                                                reflectionClass: Infection\Reflection\CoreClassReflection
                                                                functionName: bar
                                                            )
                                                            nodeId: 8
                                                            parent: nodeId(7)
                                                            isInsideFunction: true
                                                            isStrictTypes: false
                                                            functionScope: nodeId(7)
                                                            reflectionClass: Infection\Reflection\CoreClassReflection
                                                            functionName: bar
                                                        )
                                                    )
                                                    nodeId: 7
                                                    parent: nodeId(6)
                                                    isInsideFunction: true
                                                    isOnFunctionSignature: true
                                                    isStrictTypes: false
                                                    reflectionClass: Infection\Reflection\CoreClassReflection
                                                    functionName: bar
                                                )
                                                nodeId: 6
                                                parent: nodeId(4)
                                                isInsideFunction: true
                                                isStrictTypes: false
                                                functionScope: nodeId(4)
                                                reflectionClass: Infection\Reflection\CoreClassReflection
                                                functionName: bar
                                            )
                                        )
                                        nodeId: 4
                                        parent: nodeId(2)
                                        isOnFunctionSignature: true
                                        isStrictTypes: false
                                        reflectionClass: Infection\Reflection\CoreClassReflection
                                        functionName: bar
                                    )
                                )
                                nodeId: 2
                                parent: nodeId(0)
                            )
                        )
                        kind: 1
                        nodeId: 0
                    )
                )
                AST,
        ];

        yield 'it does not add the inside function flag if not necessary' => [
            file_get_contents(
                self::FIXTURES_DIR . '/Reflection/rv-without-function.php',
            ),
            null,
            <<<'AST'
                array(
                    0: Stmt_Namespace(
                        name: Name(
                            nodeId: 1
                            parent: nodeId(0)
                        )
                        stmts: array(
                            0: Stmt_Use(
                                uses: array(
                                    0: UseItem(
                                        name: Name(
                                            nodeId: 4
                                            parent: nodeId(3)
                                        )
                                        nodeId: 3
                                        parent: nodeId(2)
                                    )
                                )
                                nodeId: 2
                                parent: nodeId(0)
                            )
                            1: Stmt_Expression(
                                expr: Expr_New(
                                    class: Name(
                                        nodeId: 7
                                        resolvedName: nodeId(7)
                                        parent: nodeId(6)
                                    )
                                    nodeId: 6
                                    parent: nodeId(5)
                                )
                                nodeId: 5
                                parent: nodeId(0)
                            )
                            2: Stmt_Return(
                                expr: Expr_BinaryOp_Minus(
                                    left: Scalar_Int(
                                        rawValue: 1
                                        kind: KIND_DEC (10)
                                        nodeId: 10
                                        parent: nodeId(9)
                                    )
                                    right: Scalar_Int(
                                        rawValue: 3
                                        kind: KIND_DEC (10)
                                        nodeId: 11
                                        parent: nodeId(9)
                                    )
                                    nodeId: 9
                                    parent: nodeId(8)
                                )
                                nodeId: 8
                                parent: nodeId(0)
                            )
                        )
                        kind: 1
                        nodeId: 0
                    )
                )
                AST,
        ];

        yield 'test it can mark nodes as inside function for an anonymous class' => [
            file_get_contents(
                self::FIXTURES_DIR . '/Reflection/rv-anonymous-class.php',
            ),
            null,
            <<<'AST'
                array(
                    0: Stmt_Namespace(
                        name: Name(
                            nodeId: 1
                            parent: nodeId(0)
                        )
                        stmts: array(
                            0: Stmt_Return(
                                expr: Expr_New(
                                    class: Stmt_Class(
                                        stmts: array(
                                            0: Stmt_ClassMethod(
                                                name: Identifier(
                                                    nodeId: 6
                                                    parent: nodeId(5)
                                                    isInsideFunction: true
                                                    isStrictTypes: false
                                                    functionScope: nodeId(5)
                                                    reflectionClass: Infection\Reflection\NullReflection
                                                    functionName: test
                                                )
                                                stmts: array(
                                                    0: Stmt_Return(
                                                        expr: Scalar_Int(
                                                            rawValue: 1
                                                            kind: KIND_DEC (10)
                                                            nodeId: 8
                                                            parent: nodeId(7)
                                                            isInsideFunction: true
                                                            isStrictTypes: false
                                                            functionScope: nodeId(5)
                                                            reflectionClass: Infection\Reflection\NullReflection
                                                            functionName: test
                                                        )
                                                        nodeId: 7
                                                        parent: nodeId(5)
                                                        isInsideFunction: true
                                                        isStrictTypes: false
                                                        functionScope: nodeId(5)
                                                        reflectionClass: Infection\Reflection\NullReflection
                                                        functionName: test
                                                    )
                                                )
                                                nodeId: 5
                                                parent: nodeId(4)
                                                isOnFunctionSignature: true
                                                isStrictTypes: false
                                                reflectionClass: Infection\Reflection\NullReflection
                                                functionName: test
                                            )
                                        )
                                        nodeId: 4
                                        parent: nodeId(3)
                                    )
                                    nodeId: 3
                                    parent: nodeId(2)
                                )
                                nodeId: 2
                                parent: nodeId(0)
                            )
                        )
                        kind: 1
                        nodeId: 0
                    )
                )
                AST,
        ];

        yield 'test it sets reflection class to nodes in anonymous class' => [
            file_get_contents(
                self::FIXTURES_DIR . '/Reflection/rv-anonymous-class-inside-class.php',
            ),
            null,
            <<<'AST'
                array(
                    0: Stmt_Namespace(
                        name: Name(
                            nodeId: 1
                            parent: nodeId(0)
                        )
                        stmts: array(
                            0: Stmt_Class(
                                name: Identifier(
                                    nodeId: 3
                                    parent: nodeId(2)
                                )
                                stmts: array(
                                    0: Stmt_ClassMethod(
                                        name: Identifier(
                                            nodeId: 5
                                            parent: nodeId(4)
                                            isInsideFunction: true
                                            isStrictTypes: false
                                            functionScope: nodeId(4)
                                            reflectionClass: Infection\Reflection\CoreClassReflection
                                            functionName: createAnonymousClass
                                        )
                                        stmts: array(
                                            0: Stmt_Expression(
                                                expr: Expr_New(
                                                    class: Stmt_Class(
                                                        stmts: array(
                                                            0: Stmt_ClassMethod(
                                                                name: Identifier(
                                                                    nodeId: 10
                                                                    parent: nodeId(9)
                                                                    isInsideFunction: true
                                                                    isStrictTypes: false
                                                                    functionScope: nodeId(9)
                                                                    reflectionClass: Infection\Reflection\NullReflection
                                                                    functionName: foo
                                                                )
                                                                nodeId: 9
                                                                parent: nodeId(8)
                                                                isInsideFunction: true
                                                                isOnFunctionSignature: true
                                                                isStrictTypes: false
                                                                reflectionClass: Infection\Reflection\NullReflection
                                                                functionName: foo
                                                            )
                                                        )
                                                        nodeId: 8
                                                        parent: nodeId(7)
                                                        isInsideFunction: true
                                                        isStrictTypes: false
                                                        functionScope: nodeId(4)
                                                        reflectionClass: Infection\Reflection\NullReflection
                                                        functionName: createAnonymousClass
                                                    )
                                                    nodeId: 7
                                                    parent: nodeId(6)
                                                    isInsideFunction: true
                                                    isStrictTypes: false
                                                    functionScope: nodeId(4)
                                                    reflectionClass: Infection\Reflection\CoreClassReflection
                                                    functionName: createAnonymousClass
                                                )
                                                nodeId: 6
                                                parent: nodeId(4)
                                                isInsideFunction: true
                                                isStrictTypes: false
                                                functionScope: nodeId(4)
                                                reflectionClass: Infection\Reflection\CoreClassReflection
                                                functionName: createAnonymousClass
                                            )
                                        )
                                        nodeId: 4
                                        parent: nodeId(2)
                                        isOnFunctionSignature: true
                                        isStrictTypes: false
                                        reflectionClass: Infection\Reflection\CoreClassReflection
                                        functionName: createAnonymousClass
                                    )
                                )
                                nodeId: 2
                                parent: nodeId(0)
                            )
                        )
                        kind: 1
                        nodeId: 0
                    )
                )
                AST,
        ];

        yield 'test it sets reflection class to nodes in anonymous class that extends' => [
            file_get_contents(
                self::FIXTURES_DIR . '/Reflection/rv-anonymous-class-inside-class-that-extends.php',
            ),
            null,
            <<<'AST'
                array(
                    0: Stmt_Namespace(
                        name: Name(
                            nodeId: 1
                            parent: nodeId(0)
                        )
                        stmts: array(
                            0: Stmt_Class(
                                name: Identifier(
                                    nodeId: 3
                                    parent: nodeId(2)
                                )
                                nodeId: 2
                                parent: nodeId(0)
                            )
                            1: Stmt_Class(
                                name: Identifier(
                                    nodeId: 5
                                    parent: nodeId(4)
                                )
                                stmts: array(
                                    0: Stmt_ClassMethod(
                                        name: Identifier(
                                            nodeId: 7
                                            parent: nodeId(6)
                                            isInsideFunction: true
                                            isStrictTypes: false
                                            functionScope: nodeId(6)
                                            reflectionClass: Infection\Reflection\CoreClassReflection
                                            functionName: createAnonymousClass
                                        )
                                        stmts: array(
                                            0: Stmt_Expression(
                                                expr: Expr_New(
                                                    class: Stmt_Class(
                                                        extends: Name(
                                                            nodeId: 11
                                                            resolvedName: nodeId(11)
                                                            parent: nodeId(10)
                                                            isInsideFunction: true
                                                            isStrictTypes: false
                                                            functionScope: nodeId(6)
                                                            reflectionClass: Infection\Reflection\AnonymousClassReflection
                                                            functionName: createAnonymousClass
                                                        )
                                                        stmts: array(
                                                            0: Stmt_ClassMethod(
                                                                name: Identifier(
                                                                    nodeId: 13
                                                                    parent: nodeId(12)
                                                                    isInsideFunction: true
                                                                    isStrictTypes: false
                                                                    functionScope: nodeId(12)
                                                                    reflectionClass: Infection\Reflection\AnonymousClassReflection
                                                                    functionName: foo
                                                                )
                                                                nodeId: 12
                                                                parent: nodeId(10)
                                                                isInsideFunction: true
                                                                isOnFunctionSignature: true
                                                                isStrictTypes: false
                                                                reflectionClass: Infection\Reflection\AnonymousClassReflection
                                                                functionName: foo
                                                            )
                                                        )
                                                        nodeId: 10
                                                        parent: nodeId(9)
                                                        isInsideFunction: true
                                                        isStrictTypes: false
                                                        functionScope: nodeId(6)
                                                        reflectionClass: Infection\Reflection\AnonymousClassReflection
                                                        functionName: createAnonymousClass
                                                    )
                                                    nodeId: 9
                                                    parent: nodeId(8)
                                                    isInsideFunction: true
                                                    isStrictTypes: false
                                                    functionScope: nodeId(6)
                                                    reflectionClass: Infection\Reflection\CoreClassReflection
                                                    functionName: createAnonymousClass
                                                )
                                                nodeId: 8
                                                parent: nodeId(6)
                                                isInsideFunction: true
                                                isStrictTypes: false
                                                functionScope: nodeId(6)
                                                reflectionClass: Infection\Reflection\CoreClassReflection
                                                functionName: createAnonymousClass
                                            )
                                        )
                                        nodeId: 6
                                        parent: nodeId(4)
                                        isOnFunctionSignature: true
                                        isStrictTypes: false
                                        reflectionClass: Infection\Reflection\CoreClassReflection
                                        functionName: createAnonymousClass
                                    )
                                )
                                nodeId: 4
                                parent: nodeId(0)
                            )
                        )
                        kind: 1
                        nodeId: 0
                    )
                )
                AST,
        ];
    }

    private static function createNameResolver(): NameResolver
    {
        return new NameResolver(null, [
            'preserveOriginalNames' => true,
            'replaceNodes' => false,
        ]);
    }
}
