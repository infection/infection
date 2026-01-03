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

use Infection\PhpParser\Visitor\IgnoreAllMutationsAnnotationReaderVisitor;
use Infection\PhpParser\Visitor\IgnoreNode\ChangingIgnorer;
use Infection\PhpParser\Visitor\NonMutableNodesIgnorerVisitor;
use Infection\PhpParser\Visitor\ReflectionVisitor;
use Infection\Tests\PhpParser\Ast\Visitor\MarkTraversedNodesAsVisitedVisitor\MarkTraversedNodesAsVisitedVisitor;
use Infection\Tests\PhpParser\Ast\Visitor\RemoveUndesiredAttributesVisitor\RemoveUndesiredAttributesVisitor;
use Infection\Tests\PhpParser\Ast\VisitorTestCase;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\NodeVisitor\ParentConnectingVisitor;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use SplObjectStorage;
use function file_get_contents;

#[CoversClass(ReflectionVisitor::class)]
final class ReflectionVisitorTest extends VisitorTestCase
{
    private const FIXTURES_DIR = __DIR__ . '/../../../../autoloaded/mutator-fixtures';

    #[DataProvider('nodeProvider')]
    public function test_it_annotates_excluded_nodes_and_stops_the_traversal(
        string $code,
        string $expected,
    ): void {
        $nodes = $this->createParser()->parse($code);

        (new NodeTraverser(
            new ParentConnectingVisitor(),
            self::createNameResolver(),
            new ReflectionVisitor(),
            new MarkTraversedNodesAsVisitedVisitor(),
        ))->traverse($nodes);

        (new NodeTraverser(
            new RemoveUndesiredAttributesVisitor(
                ReflectionVisitor::STRICT_TYPES_KEY,
                ReflectionVisitor::REFLECTION_CLASS_KEY,
                ReflectionVisitor::IS_INSIDE_FUNCTION_KEY,
                ReflectionVisitor::IS_ON_FUNCTION_SIGNATURE,
                // Cannot include this one because currently there is no way to
                // provide a string representation of a function...
                // ReflectionVisitor::FUNCTION_SCOPE_KEY,
                ReflectionVisitor::FUNCTION_NAME,
                MarkTraversedNodesAsVisitedVisitor::VISITED_ATTRIBUTE,
            )
        ))->traverse($nodes);

        $actual = $this->dumper->dump($nodes);

        $this->assertSame($expected, $actual);
    }

    public static function nodeProvider(): iterable
    {
        yield 'it marks nodes which are part of the function signature' => [
            file_get_contents(
                self::FIXTURES_DIR.'/Reflection/rv-part-of-signature-flag.php',
            ),
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
                                            reflectionClass: Infection\Reflection\NullReflection
                                            functionName: foo
                                        )
                                        params: array(
                                            0: Param(
                                                type: Identifier(
                                                    isInsideFunction: true
                                                    isOnFunctionSignature: true
                                                    isStrictTypes: false
                                                    reflectionClass: Infection\Reflection\NullReflection
                                                    functionName: foo
                                                )
                                                var: Expr_Variable(
                                                    isInsideFunction: true
                                                    isOnFunctionSignature: true
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
                                            1: Param(
                                                var: Expr_Variable(
                                                    isInsideFunction: true
                                                    isOnFunctionSignature: true
                                                    isStrictTypes: false
                                                    reflectionClass: Infection\Reflection\NullReflection
                                                    functionName: foo
                                                )
                                                default: Scalar_Float(
                                                    isInsideFunction: true
                                                    isOnFunctionSignature: true
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
                                        returnType: Identifier(
                                            isInsideFunction: true
                                            isStrictTypes: false
                                            reflectionClass: Infection\Reflection\NullReflection
                                            functionName: foo
                                        )
                                        stmts: array(
                                            0: Stmt_Return(
                                                expr: Expr_BinaryOp_Identical(
                                                    left: Expr_FuncCall(
                                                        name: Name(
                                                            isInsideFunction: true
                                                            isStrictTypes: false
                                                            reflectionClass: Infection\Reflection\NullReflection
                                                            functionName: foo
                                                        )
                                                        args: array(
                                                            0: Arg(
                                                                value: Expr_Array(
                                                                    isInsideFunction: true
                                                                    isStrictTypes: false
                                                                    reflectionClass: Infection\Reflection\NullReflection
                                                                    functionName: foo
                                                                )
                                                                isInsideFunction: true
                                                                isStrictTypes: false
                                                                reflectionClass: Infection\Reflection\NullReflection
                                                                functionName: foo
                                                            )
                                                        )
                                                        isInsideFunction: true
                                                        isStrictTypes: false
                                                        reflectionClass: Infection\Reflection\NullReflection
                                                        functionName: foo
                                                    )
                                                    right: Scalar_Int(
                                                        isInsideFunction: true
                                                        isStrictTypes: false
                                                        reflectionClass: Infection\Reflection\NullReflection
                                                        functionName: foo
                                                    )
                                                    isInsideFunction: true
                                                    isStrictTypes: false
                                                    reflectionClass: Infection\Reflection\NullReflection
                                                    functionName: foo
                                                )
                                                isInsideFunction: true
                                                isStrictTypes: false
                                                reflectionClass: Infection\Reflection\NullReflection
                                                functionName: foo
                                            )
                                        )
                                        isOnFunctionSignature: true
                                        isStrictTypes: false
                                        reflectionClass: Infection\Reflection\NullReflection
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
                self::FIXTURES_DIR.'/Reflection/rv-part-of-signature-flag-with-attributes.php',
            ),
            <<<'AST'
                array(
                )
                AST,
        ];

        yield 'it detects if it is traversing inside a class method' => [
            file_get_contents(
                self::FIXTURES_DIR.'/Reflection/rv-inside-class-method.php',
            ),
            <<<'AST'
                array(
                )
                AST,
        ];

        yield 'it does not traverse a regular global or namespaced function' => [
            file_get_contents(
                self::FIXTURES_DIR.'/Reflection/rv-inside-function.php',
            ),
            <<<'AST'
                array(
                )
                AST,
        ];

        yield 'it traverses a plain function inside a class' => [
            file_get_contents(
                self::FIXTURES_DIR.'/Reflection/rv-inside-plain-function-in-class.php',
            ),
            <<<'AST'
                array(
                )
                AST,
        ];

        yield 'it traverses a plain function inside a closure' => [
            file_get_contents(
                self::FIXTURES_DIR.'/Reflection/rv-inside-plain-function-in-closure.php',
            ),
            <<<'AST'
                array(
                )
                AST,
        ];

        yield 'it detects it is traversing inside a closure' => [
            file_get_contents(
                self::FIXTURES_DIR.'/Reflection/rv-inside-closure.php',
            ),
            <<<'AST'
                array(
                )
                AST,
        ];

        yield 'it does not add the inside function flag if not necessary' => [
            file_get_contents(
                self::FIXTURES_DIR.'/Reflection/rv-without-function.php',
            ),
            <<<'AST'
                array(
                )
                AST,
        ];

        yield 'test it can mark nodes as inside function for an anonymous class' => [
            file_get_contents(
                self::FIXTURES_DIR.'/Reflection/rv-anonymous-class.php',
            ),
            <<<'AST'
                array(
                )
                AST,
        ];

        yield 'test it sets reflection class to nodes in anonymous class' => [
            file_get_contents(
                self::FIXTURES_DIR.'/Reflection/rv-anonymous-class-inside-class.php',
            ),
            <<<'AST'
                array(
                )
                AST,
        ];

        yield 'test it sets reflection class to nodes in anonymous class that extends' => [
            file_get_contents(
                self::FIXTURES_DIR.'/Reflection/rv-anonymous-class-inside-class-that-extends.php',
            ),
            <<<'AST'
                array(
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
