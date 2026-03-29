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

use Infection\Mutator\Unwrap\UnwrapArrayMap;
use Infection\Testing\BaseMutatorTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(UnwrapArrayMap::class)]
final class UnwrapArrayMapTest extends BaseMutatorTestCase
{
    /**
     * @param string|string[] $expected
     */
    #[DataProvider('mutationsProvider')]
    public function test_it_can_mutate(string $input, string|array $expected = []): void
    {
        $this->assertMutatesInput($input, $expected);
    }

    public static function mutationsProvider(): iterable
    {
        yield 'It mutates correctly when provided with an array' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $a = array_map('strtolower', ['A', 'B', 'C']);
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    $a = ['A', 'B', 'C'];
                    PHP,
            ),
        ];

        yield 'It mutates correctly when provided with a constant' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $a = array_map('strtolower', \Class_With_Const::Const);
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    $a = \Class_With_Const::Const;
                    PHP,
            ),
        ];

        yield 'It mutates correctly when a backslash is in front of array_map' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $a = \array_map('strtolower', ['A', 'B', 'C']);
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    $a = ['A', 'B', 'C'];
                    PHP,
            ),
        ];

        yield 'It does not mutate other array_ calls' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $a = array_filter([1, 2, 3], 'is_int');
                    PHP,
            ),
        ];

        yield 'It does not mutate functions named array_map' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    function array_map($text, $other)
                    {
                    }
                    PHP,
            ),
        ];

        yield 'It mutates correctly within if statements' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $a = ['A', 'B', 'C'];
                    if (array_map('strtolower', $a) === $a) {
                        return true;
                    }
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    $a = ['A', 'B', 'C'];
                    if ($a === $a) {
                        return true;
                    }
                    PHP,
            ),
        ];

        yield 'It mutates correctly when array_map is wrongly capitalized' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $a = ArRaY_mAp('strtolower', ['A', 'B', 'C']);
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    $a = ['A', 'B', 'C'];
                    PHP,
            ),
        ];

        yield 'It mutates correctly when array_map uses another function as input' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $a = array_map('strtolower', $foo->bar());
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
                    $a = array_filter(array_map(function(string $letter): string {
                        return strtolower($letter);
                    }, ['A', 'B', 'C']), 'is_int');
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    $a = array_filter(['A', 'B', 'C'], 'is_int');
                    PHP,
            ),
        ];

        yield 'It mutates correctly when provided with more than two parameters' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $a = array_map('strtolower', ['A', 'B', 'C'], \Class_With_Const::Const, $foo->bar());
                    PHP,
            ),
            [
                self::wrapCodeInMethod(
                    <<<'PHP'
                        $a = ['A', 'B', 'C'];
                        PHP,
                ),
                self::wrapCodeInMethod(
                    <<<'PHP'
                        $a = \Class_With_Const::Const;
                        PHP,
                ),
                self::wrapCodeInMethod(
                    <<<'PHP'
                        $a = $foo->bar();
                        PHP,
                ),
            ],
        ];

        yield 'It does not break when provided with a variable function name' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $a = 'array_map';

                    $b = $a('strtolower', [3,4,5]);
                    PHP,
            ),
        ];
    }
}
