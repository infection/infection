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

namespace Infection\Tests\PhpParser;

use function array_key_exists;
use Closure;
use function in_array;
use Infection\Tests\UnsupportedMethod;
use function is_string;
use PhpParser\Token;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;

#[CoversClass(FakeToken::class)]
final class FakeTokenTest extends TestCase
{
    /**
     * @param string|Closure(Token):mixed $method
     */
    #[DataProvider('methodProvider')]
    public function test_it_does_not_allow_any_of_its_methods_to_be_called(
        string|Closure $method,
    ): void {
        $token = FakeToken::create();

        $this->expectException(UnsupportedMethod::class);

        if (is_string($method)) {
            $token->$method();
        } else {
            $method($token);
        }
    }

    public static function methodProvider(): iterable
    {
        $methods = (new ReflectionClass(Token::class))->getMethods(
            ReflectionMethod::IS_STATIC | ReflectionMethod::IS_PUBLIC,
        );

        $predefinedMethods = [
            'tokenize' => static fn (Token $token) => $token::tokenize(''),
            'is' => static fn (Token $token) => $token->is(10),
        ];

        $skippedMethods = [
            '__construct',
        ];

        foreach ($methods as $method) {
            $methodName = $method->getName();

            if (array_key_exists($methodName, $predefinedMethods)) {
                yield $methodName => [$predefinedMethods[$methodName]];
            } elseif (in_array($methodName, $skippedMethods, true)) {
                continue;
            } else {
                yield $methodName => [$methodName];
            }
        }
    }
}
