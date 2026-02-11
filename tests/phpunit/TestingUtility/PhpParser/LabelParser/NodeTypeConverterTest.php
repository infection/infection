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

namespace Infection\Tests\TestingUtility\PhpParser\LabelParser;

use PhpParser\Node\Expr\Cast\Int_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Function_;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;

#[CoversClass(NodeTypeConverter::class)]
final class NodeTypeConverterTest extends TestCase
{
    #[DataProvider('validNodeTypeProvider')]
    public function test_it_converts_valid_node_types_to_fqn(
        string $nodeType,
        string $expectedFqn,
    ): void {
        $actual = NodeTypeConverter::convertToFqn($nodeType, 'test-label', 1);

        $this->assertSame($expectedFqn, $actual);
    }

    public static function validNodeTypeProvider(): iterable
    {
        yield 'simple expression' => [
            'Expr_Variable',
            Variable::class,
        ];

        yield 'statement with trailing underscore' => [
            'Stmt_Function',
            Function_::class,
        ];

        yield 'nested namespace' => [
            'Expr_Cast_Int',
            Int_::class,
        ];

        yield 'scalar with trailing underscore' => [
            'Scalar_String_',
            String_::class,
        ];
    }

    #[DataProvider('invalidNodeTypeProvider')]
    public function test_it_throws_exception_for_invalid_node_types(
        string $nodeType,
        string $label,
        int $line,
        string $expectedMessage,
    ): void {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage($expectedMessage);

        NodeTypeConverter::convertToFqn($nodeType, $label, $line);
    }

    public static function invalidNodeTypeProvider(): iterable
    {
        yield 'nonexistent node type' => [
            'Expr_NonExistent',
            'my-label',
            5,
            'Invalid node type "Expr_NonExistent" for label "my-label" at line 5. Expected a valid PhpParser node type (e.g., Expr_Variable, Stmt_Function).',
        ];

        yield 'typo in node type' => [
            'Expr_Variabel',
            'typo-label',
            10,
            'Invalid node type "Expr_Variabel" for label "typo-label" at line 10. Expected a valid PhpParser node type (e.g., Expr_Variable, Stmt_Function).',
        ];

        yield 'completely invalid' => [
            'NotANode',
            'invalid',
            1,
            'Invalid node type "NotANode" for label "invalid" at line 1. Expected a valid PhpParser node type (e.g., Expr_Variable, Stmt_Function).',
        ];
    }
}
