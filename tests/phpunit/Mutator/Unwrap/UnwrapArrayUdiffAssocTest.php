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

namespace Infection\Tests\Mutator\Unwrap;

use Infection\Mutator\Unwrap\UnwrapArrayUdiffAssoc;
use Infection\Testing\BaseMutatorTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(UnwrapArrayUdiffAssoc::class)]
final class UnwrapArrayUdiffAssocTest extends BaseMutatorTestCase
{
    /**
     * @param string|string[]|null $expected
     */
    #[DataProvider('mutationsProvider')]
    public function test_it_can_mutate(string $input, string|array|null $expected = []): void
    {
        $this->assertMutatesInput($input, $expected);
    }

    public static function mutationsProvider(): iterable
    {
        yield 'It mutates correctly when provided with an array' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $a = array_udiff_assoc(['foo' => 'bar'], ['baz' => 'bar'], $valueCompareFunc);
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    $a = ['foo' => 'bar'];
                    PHP,
            ),
        ];

        yield 'It mutates correctly when provided with a constant' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $a = array_udiff_assoc(\Class_With_Const::Const, ['baz' => 'bar'], $valueCompareFunc);
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    $a = \Class_With_Const::Const;
                    PHP,
            ),
        ];

        yield 'It mutates correctly when a backslash is in front of array_udiff_assoc' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $a = \array_udiff_assoc(['foo' => 'bar'], ['baz' => 'bar'], $valueCompareFunc);
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    $a = ['foo' => 'bar'];
                    PHP,
            ),
        ];

        yield 'It mutates correctly within if statements' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $a = ['foo' => 'bar'];
                    if (array_udiff_assoc($a, ['baz' => 'bar'], $valueCompareFunc) === $a) {
                        return true;
                    }
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    $a = ['foo' => 'bar'];
                    if ($a === $a) {
                        return true;
                    }
                    PHP,
            ),
        ];

        yield 'It mutates correctly when array_udiff_assoc is wrongly capitalized' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $a = aRrAy_UdIfF_aSsOc(['foo' => 'bar'], ['baz' => 'bar'], $valueCompareFunc);
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    $a = ['foo' => 'bar'];
                    PHP,
            ),
        ];

        yield 'It mutates correctly when array_udiff_assoc uses functions as input' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $a = array_udiff_assoc($foo->bar(), $foo->baz(), $valueCompareFunc);
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    $a = $foo->bar();
                    PHP,
            ),
        ];

        yield 'It mutates correctly when provided with a more complex situation' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $a = array_map('strtolower', array_udiff_assoc(['foo' => 'bar'], ['baz' => 'bar'], $valueCompareFunc));
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    $a = array_map('strtolower', ['foo' => 'bar']);
                    PHP,
            ),
        ];

        yield 'It mutates correctly when more than two parameters are present' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $a = array_udiff_assoc(['foo' => 'bar'], ['baz' => 'bar'], ['qux' => 'bar'], $valueCompareFunc);
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    $a = ['foo' => 'bar'];
                    PHP,
            ),
        ];

        yield 'It does not mutate other array_ calls' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $a = array_map('strtolower', ['foo' => 'bar']);
                    PHP,
            ),
        ];

        yield 'It does not mutate functions named array_udiff_assoc' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    function array_udiff_assoc($array, $array1, $array2)
                    {
                    }
                    PHP,
            ),
        ];

        yield 'It does not mutate when a variable function name is used' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $a = 'array_udiff_assoc';

                    $b = $a(['foo' => 'bar'], ['baz' => 'bar'], $valueCompareFunc);
                    PHP,
            ),
        ];
    }
}
