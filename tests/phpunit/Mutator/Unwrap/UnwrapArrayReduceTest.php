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

use Infection\Mutator\Unwrap\UnwrapArrayReduce;
use Infection\Testing\BaseMutatorTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(UnwrapArrayReduce::class)]
final class UnwrapArrayReduceTest extends BaseMutatorTestCase
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
        yield 'It mutates correctly when the $initial parameter is provided as an array' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $a = array_reduce(
                        ['A', 1, 'C'],
                        function ($carry, $item) {
                           return $item;
                        },
                        ['D']
                    );
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    $a = ['D'];
                    PHP,
            ),
        ];

        yield 'It mutates correctly when the $initial parameter is provided as a constant' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $a = array_reduce(
                        ['A', 1, 'C'],
                        function ($carry, $item) {
                           return $item;
                        },
                        \Class_With_Const::Const
                    );
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    $a = \Class_With_Const::Const;
                    PHP,
            ),
        ];

        yield 'It mutates correctly when the $initial parameter is provided and a backslash is in front of array_reduce' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $a = \array_reduce(
                        ['A', 1, 'C'],
                        function ($carry, $item) {
                           return $item;
                        },
                        ['D']
                    );
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    $a = ['D'];
                    PHP,
            ),
        ];

        yield 'It mutates correctly within if statements when the $initial parameter is provided' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $a = ['A', 1, 'C'];
                    if (array_reduce($a, function ($carry, $item) { return $item; }, ['D']) === $a) {
                        return true;
                    }
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    $a = ['A', 1, 'C'];
                    if (['D'] === $a) {
                        return true;
                    }
                    PHP,
            ),
        ];

        yield 'It mutates correctly when the $initial parameter is provided and array_reduce is wrongly capitalized' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $a = aRrAy_ReDuCe(
                        ['A', 1, 'C'],
                        function ($carry, $item) {
                            return $item;
                        },
                        ['D']
                    );
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    $a = ['D'];
                    PHP,
            ),
        ];

        yield 'It mutates correctly when the $initial parameter is provided and array_reduce uses other functions as input' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $a = array_reduce(
                        $foo->bar(),
                        $foo->baz(),
                        $foo->qux()
                    );
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    $a = $foo->qux();
                    PHP,
            ),
        ];

        yield 'It mutates correctly when the $initial parameter is provided in a more complex situation' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $a = array_map('strtolower', array_reduce(['A', 1, 'C'], $callback, ['D']));
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    $a = array_map('strtolower', ['D']);
                    PHP,
            ),
        ];

        yield 'It mutates correctly when the the $initial parameter is provided and the $callback parameter is provided as a variable' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $a = array_reduce(['A', 1, 'C'], $callback, ['D']);
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    $a = ['D'];
                    PHP,
            ),
        ];

        yield 'It does not mutate when the $initial parameter is not provided' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $a = array_reduce(['A', 1, 'C'], function ($carry, $item) {
                        return $item;
                    });
                    PHP,
            ),
        ];

        yield 'It does not mutate other array_ calls' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $a = array_map('strtolower', ['A', 'B', 'C']);
                    PHP,
            ),
        ];

        yield 'It does not mutate functions named array_reduce' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    function array_reduce($array, $callback, $initial = null)
                    {
                    }
                    PHP,
            ),
        ];

        yield 'It does not break when provided with a variable function name' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $a = 'array_reduce';

                    $b = $a('strtolower', [3,4,5]);
                    PHP,
            ),
        ];
    }
}
