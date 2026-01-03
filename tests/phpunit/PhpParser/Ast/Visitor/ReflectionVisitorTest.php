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

namespace Infection\Tests\PhpParser\Ast\Visitor;

use Infection\Tests\PhpParser\Ast\Visitor\AddIdToTraversedNodesVisitor\AddIdToTraversedNodesVisitor;
use function file_get_contents;
use Infection\PhpParser\Visitor\ReflectionVisitor;
use Infection\Tests\PhpParser\Ast\Visitor\KeepDesiredAttributesVisitor\KeepDesiredAttributesVisitor;
use Infection\Tests\PhpParser\Ast\Visitor\MarkTraversedNodesAsVisitedVisitor\MarkTraversedNodesAsVisitedVisitor;
use Infection\Tests\PhpParser\Ast\VisitorTestCase;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\NodeVisitor\ParentConnectingVisitor;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(ReflectionVisitor::class)]
final class ReflectionVisitorTest extends VisitorTestCase
{
    private const FIXTURES_DIR = __DIR__ . '/../../../../autoloaded/mutator-fixtures';

    /**
     * @param list<string>|null $desiredAttributes
     */
    #[DataProvider('nodeProvider')]
    public function test_it_annotates_excluded_nodes_and_stops_the_traversal(
        string $code,
        ?array $desiredAttributes,
        string $expected,
    ): void {
        $nodes = $this->createParser()->parse($code);

        (new NodeTraverser(
            new AddIdToTraversedNodesVisitor(),
            new ParentConnectingVisitor(),
            self::createNameResolver(),
            new ReflectionVisitor(),
            new MarkTraversedNodesAsVisitedVisitor(),
        ))->traverse($nodes);

        if (null !== $desiredAttributes) {
            (new NodeTraverser(
                new KeepDesiredAttributesVisitor(
                    MarkTraversedNodesAsVisitedVisitor::VISITED_ATTRIBUTE,
                    ...$desiredAttributes,
                ),
            ))->traverse($nodes);
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
                        name: Name
                        stmts: array(
                            0: Stmt_Class(
                                name: Identifier
                                stmts: array(
                                    0: Stmt_ClassMethod(
                                        name: Identifier(
                                            isInsideFunction: true
                                            isStrictTypes: false
                                            reflectionClass: Infection\Reflection\CoreClassReflection
                                            functionName: foo
                                        )
                                        params: array(
                                            0: Param(
                                                type: Identifier(
                                                    isInsideFunction: true
                                                    isOnFunctionSignature: true
                                                    isStrictTypes: false
                                                    reflectionClass: Infection\Reflection\CoreClassReflection
                                                    functionName: foo
                                                )
                                                var: Expr_Variable(
                                                    isInsideFunction: true
                                                    isOnFunctionSignature: true
                                                    isStrictTypes: false
                                                    reflectionClass: Infection\Reflection\CoreClassReflection
                                                    functionName: foo
                                                )
                                                isInsideFunction: true
                                                isOnFunctionSignature: true
                                                isStrictTypes: false
                                                reflectionClass: Infection\Reflection\CoreClassReflection
                                                functionName: foo
                                            )
                                            1: Param(
                                                var: Expr_Variable(
                                                    isInsideFunction: true
                                                    isOnFunctionSignature: true
                                                    isStrictTypes: false
                                                    reflectionClass: Infection\Reflection\CoreClassReflection
                                                    functionName: foo
                                                )
                                                default: Scalar_Float(
                                                    isInsideFunction: true
                                                    isOnFunctionSignature: true
                                                    isStrictTypes: false
                                                    reflectionClass: Infection\Reflection\CoreClassReflection
                                                    functionName: foo
                                                )
                                                isInsideFunction: true
                                                isOnFunctionSignature: true
                                                isStrictTypes: false
                                                reflectionClass: Infection\Reflection\CoreClassReflection
                                                functionName: foo
                                            )
                                        )
                                        returnType: Identifier(
                                            isInsideFunction: true
                                            isStrictTypes: false
                                            reflectionClass: Infection\Reflection\CoreClassReflection
                                            functionName: foo
                                        )
                                        stmts: array(
                                            0: Stmt_Return(
                                                expr: Expr_BinaryOp_Identical(
                                                    left: Expr_FuncCall(
                                                        name: Name(
                                                            isInsideFunction: true
                                                            isStrictTypes: false
                                                            reflectionClass: Infection\Reflection\CoreClassReflection
                                                            functionName: foo
                                                        )
                                                        args: array(
                                                            0: Arg(
                                                                value: Expr_Array(
                                                                    isInsideFunction: true
                                                                    isStrictTypes: false
                                                                    reflectionClass: Infection\Reflection\CoreClassReflection
                                                                    functionName: foo
                                                                )
                                                                isInsideFunction: true
                                                                isStrictTypes: false
                                                                reflectionClass: Infection\Reflection\CoreClassReflection
                                                                functionName: foo
                                                            )
                                                        )
                                                        isInsideFunction: true
                                                        isStrictTypes: false
                                                        reflectionClass: Infection\Reflection\CoreClassReflection
                                                        functionName: foo
                                                    )
                                                    right: Scalar_Int(
                                                        isInsideFunction: true
                                                        isStrictTypes: false
                                                        reflectionClass: Infection\Reflection\CoreClassReflection
                                                        functionName: foo
                                                    )
                                                    isInsideFunction: true
                                                    isStrictTypes: false
                                                    reflectionClass: Infection\Reflection\CoreClassReflection
                                                    functionName: foo
                                                )
                                                isInsideFunction: true
                                                isStrictTypes: false
                                                reflectionClass: Infection\Reflection\CoreClassReflection
                                                functionName: foo
                                            )
                                        )
                                        isOnFunctionSignature: true
                                        isStrictTypes: false
                                        reflectionClass: Infection\Reflection\CoreClassReflection
                                        functionName: foo
                                    )
                                )
                            )
                        )
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
                        name: Name
                        stmts: array(
                            0: Stmt_Class(
                                name: Identifier
                                stmts: array(
                                    0: Stmt_ClassMethod(
                                        name: Identifier(
                                            isInsideFunction: true
                                            isStrictTypes: false
                                            reflectionClass: Infection\Reflection\CoreClassReflection
                                            functionName: foo
                                        )
                                        params: array(
                                            0: Param(
                                                type: Identifier(
                                                    isInsideFunction: true
                                                    isOnFunctionSignature: true
                                                    isStrictTypes: false
                                                    reflectionClass: Infection\Reflection\CoreClassReflection
                                                    functionName: foo
                                                )
                                                var: Expr_Variable(
                                                    isInsideFunction: true
                                                    isOnFunctionSignature: true
                                                    isStrictTypes: false
                                                    reflectionClass: Infection\Reflection\CoreClassReflection
                                                    functionName: foo
                                                )
                                                isInsideFunction: true
                                                isOnFunctionSignature: true
                                                isStrictTypes: false
                                                reflectionClass: Infection\Reflection\CoreClassReflection
                                                functionName: foo
                                            )
                                            1: Param(
                                                attrGroups: array(
                                                    0: AttributeGroup(
                                                        attrs: array(
                                                            0: Attribute(
                                                                name: Name(
                                                                    isInsideFunction: true
                                                                    isOnFunctionSignature: true
                                                                    isStrictTypes: false
                                                                    reflectionClass: Infection\Reflection\CoreClassReflection
                                                                    functionName: foo
                                                                )
                                                                args: array(
                                                                    0: Arg(
                                                                        value: Expr_ConstFetch(
                                                                            name: Name(
                                                                                isInsideFunction: true
                                                                                isOnFunctionSignature: true
                                                                                isStrictTypes: false
                                                                                reflectionClass: Infection\Reflection\CoreClassReflection
                                                                                functionName: foo
                                                                            )
                                                                            isInsideFunction: true
                                                                            isOnFunctionSignature: true
                                                                            isStrictTypes: false
                                                                            reflectionClass: Infection\Reflection\CoreClassReflection
                                                                            functionName: foo
                                                                        )
                                                                        isInsideFunction: true
                                                                        isOnFunctionSignature: true
                                                                        isStrictTypes: false
                                                                        reflectionClass: Infection\Reflection\CoreClassReflection
                                                                        functionName: foo
                                                                    )
                                                                )
                                                                isInsideFunction: true
                                                                isOnFunctionSignature: true
                                                                isStrictTypes: false
                                                                reflectionClass: Infection\Reflection\CoreClassReflection
                                                                functionName: foo
                                                            )
                                                        )
                                                        isInsideFunction: true
                                                        isOnFunctionSignature: true
                                                        isStrictTypes: false
                                                        reflectionClass: Infection\Reflection\CoreClassReflection
                                                        functionName: foo
                                                    )
                                                )
                                                var: Expr_Variable(
                                                    isInsideFunction: true
                                                    isOnFunctionSignature: true
                                                    isStrictTypes: false
                                                    reflectionClass: Infection\Reflection\CoreClassReflection
                                                    functionName: foo
                                                )
                                                default: Scalar_Float(
                                                    isInsideFunction: true
                                                    isOnFunctionSignature: true
                                                    isStrictTypes: false
                                                    reflectionClass: Infection\Reflection\CoreClassReflection
                                                    functionName: foo
                                                )
                                                isInsideFunction: true
                                                isOnFunctionSignature: true
                                                isStrictTypes: false
                                                reflectionClass: Infection\Reflection\CoreClassReflection
                                                functionName: foo
                                            )
                                        )
                                        returnType: Identifier(
                                            isInsideFunction: true
                                            isStrictTypes: false
                                            reflectionClass: Infection\Reflection\CoreClassReflection
                                            functionName: foo
                                        )
                                        stmts: array(
                                            0: Stmt_Return(
                                                expr: Expr_BinaryOp_Identical(
                                                    left: Expr_FuncCall(
                                                        name: Name(
                                                            isInsideFunction: true
                                                            isStrictTypes: false
                                                            reflectionClass: Infection\Reflection\CoreClassReflection
                                                            functionName: foo
                                                        )
                                                        args: array(
                                                            0: Arg(
                                                                value: Expr_Array(
                                                                    isInsideFunction: true
                                                                    isStrictTypes: false
                                                                    reflectionClass: Infection\Reflection\CoreClassReflection
                                                                    functionName: foo
                                                                )
                                                                isInsideFunction: true
                                                                isStrictTypes: false
                                                                reflectionClass: Infection\Reflection\CoreClassReflection
                                                                functionName: foo
                                                            )
                                                        )
                                                        isInsideFunction: true
                                                        isStrictTypes: false
                                                        reflectionClass: Infection\Reflection\CoreClassReflection
                                                        functionName: foo
                                                    )
                                                    right: Scalar_Int(
                                                        isInsideFunction: true
                                                        isStrictTypes: false
                                                        reflectionClass: Infection\Reflection\CoreClassReflection
                                                        functionName: foo
                                                    )
                                                    isInsideFunction: true
                                                    isStrictTypes: false
                                                    reflectionClass: Infection\Reflection\CoreClassReflection
                                                    functionName: foo
                                                )
                                                isInsideFunction: true
                                                isStrictTypes: false
                                                reflectionClass: Infection\Reflection\CoreClassReflection
                                                functionName: foo
                                            )
                                        )
                                        isOnFunctionSignature: true
                                        isStrictTypes: false
                                        reflectionClass: Infection\Reflection\CoreClassReflection
                                        functionName: foo
                                    )
                                )
                            )
                        )
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
                        name: Name
                        stmts: array(
                            0: Stmt_Class(
                                name: Identifier
                                stmts: array(
                                    0: Stmt_ClassMethod(
                                        name: Identifier(
                                            isInsideFunction: true
                                            isStrictTypes: false
                                            reflectionClass: Infection\Reflection\CoreClassReflection
                                            functionName: test
                                        )
                                        stmts: array(
                                            0: Stmt_Return(
                                                expr: Scalar_Int(
                                                    isInsideFunction: true
                                                    isStrictTypes: false
                                                    reflectionClass: Infection\Reflection\CoreClassReflection
                                                    functionName: test
                                                )
                                                isInsideFunction: true
                                                isStrictTypes: false
                                                reflectionClass: Infection\Reflection\CoreClassReflection
                                                functionName: test
                                            )
                                        )
                                        isOnFunctionSignature: true
                                        isStrictTypes: false
                                        reflectionClass: Infection\Reflection\CoreClassReflection
                                        functionName: test
                                    )
                                )
                            )
                        )
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
                        name: Name
                        stmts: array(
                            0: Stmt_Function(
                                name: Identifier
                                stmts: array(
                                    0: Stmt_Return(
                                        expr: Expr_Closure(
                                            params: array(
                                                0: Param(
                                                    var: Expr_Variable
                                                )
                                            )
                                            stmts: array(
                                                0: Stmt_Return(
                                                    expr: Scalar_Int
                                                )
                                            )
                                        )
                                    )
                                )
                                isStrictTypes: false
                            )
                        )
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
                        name: Name
                        stmts: array(
                            0: Stmt_Class(
                                name: Identifier
                                stmts: array(
                                    0: Stmt_ClassMethod(
                                        name: Identifier(
                                            isInsideFunction: true
                                            isStrictTypes: false
                                            reflectionClass: Infection\Reflection\CoreClassReflection
                                            functionName: foo
                                        )
                                        stmts: array(
                                            0: Stmt_Function(
                                                name: Identifier(
                                                    isInsideFunction: true
                                                    isStrictTypes: false
                                                    reflectionClass: Infection\Reflection\CoreClassReflection
                                                    functionName: foo
                                                )
                                                stmts: array(
                                                    0: Stmt_Expression(
                                                        expr: Expr_FuncCall(
                                                            name: Name(
                                                                isInsideFunction: true
                                                                isStrictTypes: false
                                                                reflectionClass: Infection\Reflection\CoreClassReflection
                                                                functionName: foo
                                                            )
                                                            args: array(
                                                                0: Arg(
                                                                    value: Expr_Array(
                                                                        isInsideFunction: true
                                                                        isStrictTypes: false
                                                                        reflectionClass: Infection\Reflection\CoreClassReflection
                                                                        functionName: foo
                                                                    )
                                                                    isInsideFunction: true
                                                                    isStrictTypes: false
                                                                    reflectionClass: Infection\Reflection\CoreClassReflection
                                                                    functionName: foo
                                                                )
                                                            )
                                                            isInsideFunction: true
                                                            isStrictTypes: false
                                                            reflectionClass: Infection\Reflection\CoreClassReflection
                                                            functionName: foo
                                                        )
                                                        isInsideFunction: true
                                                        isStrictTypes: false
                                                        reflectionClass: Infection\Reflection\CoreClassReflection
                                                        functionName: foo
                                                    )
                                                )
                                                isStrictTypes: false
                                                isInsideFunction: true
                                                reflectionClass: Infection\Reflection\CoreClassReflection
                                                functionName: foo
                                            )
                                        )
                                        isOnFunctionSignature: true
                                        isStrictTypes: false
                                        reflectionClass: Infection\Reflection\CoreClassReflection
                                        functionName: foo
                                    )
                                )
                            )
                        )
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
                        name: Name
                        stmts: array(
                            0: Stmt_Expression(
                                expr: Expr_Assign(
                                    var: Expr_Variable
                                    expr: Expr_Closure(
                                        params: array(
                                            0: Param(
                                                type: Identifier
                                                var: Expr_Variable
                                            )
                                        )
                                        stmts: array(
                                            0: Stmt_Function(
                                                name: Identifier
                                                stmts: array(
                                                    0: Stmt_Expression(
                                                        expr: Expr_FuncCall(
                                                            name: Name
                                                            args: array(
                                                                0: Arg(
                                                                    value: Expr_Array
                                                                )
                                                            )
                                                        )
                                                    )
                                                )
                                                isStrictTypes: false
                                            )
                                        )
                                    )
                                )
                            )
                        )
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
                        name: Name
                        stmts: array(
                            0: Stmt_Class(
                                name: Identifier
                                stmts: array(
                                    0: Stmt_ClassMethod(
                                        name: Identifier(
                                            isInsideFunction: true
                                            isStrictTypes: false
                                            reflectionClass: Infection\Reflection\CoreClassReflection
                                            functionName: bar
                                        )
                                        stmts: array(
                                            0: Stmt_Return(
                                                expr: Expr_Closure(
                                                    stmts: array(
                                                        0: Stmt_Return(
                                                            expr: Scalar_Int(
                                                                isInsideFunction: true
                                                                isStrictTypes: false
                                                                reflectionClass: Infection\Reflection\CoreClassReflection
                                                                functionName: bar
                                                            )
                                                            isInsideFunction: true
                                                            isStrictTypes: false
                                                            reflectionClass: Infection\Reflection\CoreClassReflection
                                                            functionName: bar
                                                        )
                                                    )
                                                    isInsideFunction: true
                                                    isOnFunctionSignature: true
                                                    isStrictTypes: false
                                                    reflectionClass: Infection\Reflection\CoreClassReflection
                                                    functionName: bar
                                                )
                                                isInsideFunction: true
                                                isStrictTypes: false
                                                reflectionClass: Infection\Reflection\CoreClassReflection
                                                functionName: bar
                                            )
                                        )
                                        isOnFunctionSignature: true
                                        isStrictTypes: false
                                        reflectionClass: Infection\Reflection\CoreClassReflection
                                        functionName: bar
                                    )
                                )
                            )
                        )
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
                        name: Name
                        stmts: array(
                            0: Stmt_Use(
                                uses: array(
                                    0: UseItem(
                                        name: Name
                                    )
                                )
                            )
                            1: Stmt_Expression(
                                expr: Expr_New(
                                    class: Name
                                )
                            )
                            2: Stmt_Return(
                                expr: Expr_BinaryOp_Minus(
                                    left: Scalar_Int
                                    right: Scalar_Int
                                )
                            )
                        )
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
                        name: Name
                        stmts: array(
                            0: Stmt_Return(
                                expr: Expr_New(
                                    class: Stmt_Class(
                                        stmts: array(
                                            0: Stmt_ClassMethod(
                                                name: Identifier(
                                                    isInsideFunction: true
                                                    isStrictTypes: false
                                                    reflectionClass: Infection\Reflection\NullReflection
                                                    functionName: test
                                                )
                                                stmts: array(
                                                    0: Stmt_Return(
                                                        expr: Scalar_Int(
                                                            isInsideFunction: true
                                                            isStrictTypes: false
                                                            reflectionClass: Infection\Reflection\NullReflection
                                                            functionName: test
                                                        )
                                                        isInsideFunction: true
                                                        isStrictTypes: false
                                                        reflectionClass: Infection\Reflection\NullReflection
                                                        functionName: test
                                                    )
                                                )
                                                isOnFunctionSignature: true
                                                isStrictTypes: false
                                                reflectionClass: Infection\Reflection\NullReflection
                                                functionName: test
                                            )
                                        )
                                    )
                                )
                            )
                        )
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
                        name: Name
                        stmts: array(
                            0: Stmt_Class(
                                name: Identifier
                                stmts: array(
                                    0: Stmt_ClassMethod(
                                        name: Identifier(
                                            isInsideFunction: true
                                            isStrictTypes: false
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
                                                                    isInsideFunction: true
                                                                    isStrictTypes: false
                                                                    reflectionClass: Infection\Reflection\NullReflection
                                                                    functionName: foo
                                                                )
                                                                isInsideFunction: true
                                                                isOnFunctionSignature: true
                                                                isStrictTypes: false
                                                                reflectionClass: Infection\Reflection\NullReflection
                                                                functionName: foo
                                                            )
                                                        )
                                                        isInsideFunction: true
                                                        isStrictTypes: false
                                                        reflectionClass: Infection\Reflection\NullReflection
                                                        functionName: createAnonymousClass
                                                    )
                                                    isInsideFunction: true
                                                    isStrictTypes: false
                                                    reflectionClass: Infection\Reflection\CoreClassReflection
                                                    functionName: createAnonymousClass
                                                )
                                                isInsideFunction: true
                                                isStrictTypes: false
                                                reflectionClass: Infection\Reflection\CoreClassReflection
                                                functionName: createAnonymousClass
                                            )
                                        )
                                        isOnFunctionSignature: true
                                        isStrictTypes: false
                                        reflectionClass: Infection\Reflection\CoreClassReflection
                                        functionName: createAnonymousClass
                                    )
                                )
                            )
                        )
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
                        name: Name
                        stmts: array(
                            0: Stmt_Class(
                                name: Identifier
                            )
                            1: Stmt_Class(
                                name: Identifier
                                stmts: array(
                                    0: Stmt_ClassMethod(
                                        name: Identifier(
                                            isInsideFunction: true
                                            isStrictTypes: false
                                            reflectionClass: Infection\Reflection\CoreClassReflection
                                            functionName: createAnonymousClass
                                        )
                                        stmts: array(
                                            0: Stmt_Expression(
                                                expr: Expr_New(
                                                    class: Stmt_Class(
                                                        extends: Name(
                                                            isInsideFunction: true
                                                            isStrictTypes: false
                                                            reflectionClass: Infection\Reflection\AnonymousClassReflection
                                                            functionName: createAnonymousClass
                                                        )
                                                        stmts: array(
                                                            0: Stmt_ClassMethod(
                                                                name: Identifier(
                                                                    isInsideFunction: true
                                                                    isStrictTypes: false
                                                                    reflectionClass: Infection\Reflection\AnonymousClassReflection
                                                                    functionName: foo
                                                                )
                                                                isInsideFunction: true
                                                                isOnFunctionSignature: true
                                                                isStrictTypes: false
                                                                reflectionClass: Infection\Reflection\AnonymousClassReflection
                                                                functionName: foo
                                                            )
                                                        )
                                                        isInsideFunction: true
                                                        isStrictTypes: false
                                                        reflectionClass: Infection\Reflection\AnonymousClassReflection
                                                        functionName: createAnonymousClass
                                                    )
                                                    isInsideFunction: true
                                                    isStrictTypes: false
                                                    reflectionClass: Infection\Reflection\CoreClassReflection
                                                    functionName: createAnonymousClass
                                                )
                                                isInsideFunction: true
                                                isStrictTypes: false
                                                reflectionClass: Infection\Reflection\CoreClassReflection
                                                functionName: createAnonymousClass
                                            )
                                        )
                                        isOnFunctionSignature: true
                                        isStrictTypes: false
                                        reflectionClass: Infection\Reflection\CoreClassReflection
                                        functionName: createAnonymousClass
                                    )
                                )
                            )
                        )
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
