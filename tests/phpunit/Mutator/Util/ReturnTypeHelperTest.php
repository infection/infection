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

namespace Infection\Tests\Mutator\Util;

use Infection\Mutator\Util\ReturnTypeHelper;
use Infection\PhpParser\Visitor\NextConnectingVisitor;
use Infection\PhpParser\Visitor\ReflectionVisitor;
use PhpParser\Node;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(ReturnTypeHelper::class)]
final class ReturnTypeHelperTest extends TestCase
{
    public function test_it_detects_default_attributes(): void
    {
        $returnNode = new Node\Stmt\Return_();
        $returnNode->setAttribute(
            ReflectionVisitor::FUNCTION_SCOPE_KEY,
            new Node\Stmt\Function_('hello'),
        );

        $returnTypeHelper = new ReturnTypeHelper($returnNode);

        $this->assertFalse($returnTypeHelper->hasVoidReturnType());
        $this->assertFalse($returnTypeHelper->hasSpecificReturnType());
        $this->assertTrue($returnTypeHelper->isNullReturn());
        $this->assertFalse($returnTypeHelper->hasNextStmtNode());
    }

    #[DataProvider('voidReturnTypeProvider')]
    public function test_it_detects_void_return_type(Node\FunctionLike $function, bool $expected): void
    {
        $returnNode = new Node\Stmt\Return_();
        $returnNode->setAttribute(ReflectionVisitor::FUNCTION_SCOPE_KEY, $function);

        $returnTypeHelper = new ReturnTypeHelper($returnNode);

        $this->assertSame($expected, $returnTypeHelper->hasVoidReturnType());
    }

    public static function voidReturnTypeProvider(): iterable
    {
        yield 'function with void return type' => [
            new Node\Stmt\Function_('test', ['returnType' => new Node\Identifier('void')]),
            true,
        ];

        yield 'function without return type' => [
            new Node\Stmt\Function_('test'),
            false,
        ];

        yield 'function with string return type' => [
            new Node\Stmt\Function_('test', ['returnType' => new Node\Identifier('string')]),
            false,
        ];

        yield 'function with nullable return type' => [
            new Node\Stmt\Function_('test', ['returnType' => new Node\NullableType(new Node\Identifier('string'))]),
            false,
        ];

        yield 'function with union return type' => [
            new Node\Stmt\Function_('test', ['returnType' => new Node\UnionType([
                new Node\Name('string'),
                new Node\Name('int'),
            ])]),
            false,
        ];
    }

    #[DataProvider('specificReturnTypeProvider')]
    public function test_it_detects_specific_return_type(Node\FunctionLike $function, bool $expected): void
    {
        $returnNode = new Node\Stmt\Return_();
        $returnNode->setAttribute(ReflectionVisitor::FUNCTION_SCOPE_KEY, $function);

        $returnTypeHelper = new ReturnTypeHelper($returnNode);

        $this->assertSame($expected, $returnTypeHelper->hasSpecificReturnType());
    }

    public static function specificReturnTypeProvider(): iterable
    {
        yield 'function without return type' => [
            new Node\Stmt\Function_('test'),
            false,
        ];

        yield 'function with void return type' => [
            new Node\Stmt\Function_('test', ['returnType' => new Node\Identifier('void')]),
            false,
        ];

        yield 'function with string return type' => [
            new Node\Stmt\Function_('test', ['returnType' => new Node\Identifier('string')]),
            true,
        ];

        yield 'function with int return type' => [
            new Node\Stmt\Function_('test', ['returnType' => new Node\Identifier('int')]),
            true,
        ];

        yield 'function with nullable return type' => [
            new Node\Stmt\Function_('test', ['returnType' => new Node\NullableType(new Node\Identifier('string'))]),
            true,
        ];

        yield 'function with union return type' => [
            new Node\Stmt\Function_('test', ['returnType' => new Node\UnionType([
                new Node\Name('string'),
                new Node\Name('int'),
            ])]),
            true,
        ];

        yield 'function with intersection return type' => [
            new Node\Stmt\Function_('test', ['returnType' => new Node\IntersectionType([
                new Node\Name('Countable'),
                new Node\Name('Iterator'),
            ])]),
            true,
        ];
    }

    #[DataProvider('nullReturnProvider')]
    public function test_it_detects_null_return(Node\Stmt\Return_ $returnNode, bool $expected): void
    {
        $returnNode->setAttribute(
            ReflectionVisitor::FUNCTION_SCOPE_KEY,
            new Node\Stmt\Function_('test'),
        );

        $returnTypeHelper = new ReturnTypeHelper($returnNode);

        $this->assertSame($expected, $returnTypeHelper->isNullReturn());
    }

    public static function nullReturnProvider(): iterable
    {
        yield 'empty return' => [
            new Node\Stmt\Return_(),
            true,
        ];

        yield 'return null' => [
            new Node\Stmt\Return_(new Node\Expr\ConstFetch(new Node\Name('null'))),
            true,
        ];

        yield 'return NULL (uppercase)' => [
            new Node\Stmt\Return_(new Node\Expr\ConstFetch(new Node\Name('NULL'))),
            true,
        ];

        yield 'return string' => [
            new Node\Stmt\Return_(new Node\Scalar\String_('hello')),
            false,
        ];

        yield 'return integer' => [
            new Node\Stmt\Return_(new Node\Scalar\Int_(42)),
            false,
        ];

        yield 'return variable' => [
            new Node\Stmt\Return_(new Node\Expr\Variable('foo')),
            false,
        ];

        yield 'return true' => [
            new Node\Stmt\Return_(new Node\Expr\ConstFetch(new Node\Name('true'))),
            false,
        ];

        yield 'return false' => [
            new Node\Stmt\Return_(new Node\Expr\ConstFetch(new Node\Name('false'))),
            false,
        ];
    }

    public function test_it_detects_next_statement_node(): void
    {
        $function = new Node\Stmt\Function_('test');

        // Return with next statement
        $returnWithNext = new Node\Stmt\Return_();
        $returnWithNext->setAttribute(ReflectionVisitor::FUNCTION_SCOPE_KEY, $function);
        $returnWithNext->setAttribute(NextConnectingVisitor::NEXT_ATTRIBUTE, new Node\Stmt\Echo_([new Node\Scalar\String_('hello')]));

        $helperWithNext = new ReturnTypeHelper($returnWithNext);
        $this->assertTrue($helperWithNext->hasNextStmtNode());

        // Return without next statement
        $returnWithoutNext = new Node\Stmt\Return_();
        $returnWithoutNext->setAttribute(ReflectionVisitor::FUNCTION_SCOPE_KEY, $function);

        $helperWithoutNext = new ReturnTypeHelper($returnWithoutNext);
        $this->assertFalse($helperWithoutNext->hasNextStmtNode());
    }

    public function test_it_handles_method_return_types(): void
    {
        // Test with a class method
        $method = new Node\Stmt\ClassMethod('getData', [
            'returnType' => new Node\Name('array'),
        ]);

        $returnNode = new Node\Stmt\Return_(new Node\Expr\Array_());
        $returnNode->setAttribute(ReflectionVisitor::FUNCTION_SCOPE_KEY, $method);

        $returnTypeHelper = new ReturnTypeHelper($returnNode);

        $this->assertFalse($returnTypeHelper->hasVoidReturnType());
        $this->assertTrue($returnTypeHelper->hasSpecificReturnType());
        $this->assertFalse($returnTypeHelper->isNullReturn());
    }

    public function test_it_handles_closure_return_types(): void
    {
        // Test with a closure
        $closure = new Node\Expr\Closure([
            'returnType' => new Node\NullableType(new Node\Identifier('string')),
        ]);

        $returnNode = new Node\Stmt\Return_(new Node\Scalar\String_('test'));
        $returnNode->setAttribute(ReflectionVisitor::FUNCTION_SCOPE_KEY, $closure);

        $returnTypeHelper = new ReturnTypeHelper($returnNode);

        $this->assertFalse($returnTypeHelper->hasVoidReturnType());
        $this->assertTrue($returnTypeHelper->hasSpecificReturnType());
        $this->assertFalse($returnTypeHelper->isNullReturn());
    }

    public function test_it_handles_arrow_function_return_types(): void
    {
        // Test with an arrow function
        $arrowFunction = new Node\Expr\ArrowFunction([
            'returnType' => new Node\Identifier('int'),
            'expr' => new Node\Scalar\Int_(42),
        ]);

        $returnNode = new Node\Stmt\Return_(new Node\Scalar\Int_(42));
        $returnNode->setAttribute(ReflectionVisitor::FUNCTION_SCOPE_KEY, $arrowFunction);

        $returnTypeHelper = new ReturnTypeHelper($returnNode);

        $this->assertFalse($returnTypeHelper->hasVoidReturnType());
        $this->assertTrue($returnTypeHelper->hasSpecificReturnType());
        $this->assertFalse($returnTypeHelper->isNullReturn());
    }
}
