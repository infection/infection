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

use Infection\Mutator\Unwrap\UnwrapStrRepeat;
use Infection\Testing\BaseMutatorTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(UnwrapStrRepeat::class)]
final class UnwrapStrRepeatTest extends BaseMutatorTestCase
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
        yield 'It mutates correctly when provided with a string' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $a = str_repeat('Hello!', 3);
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    $a = 'Hello!';
                    PHP,
            ),
        ];

        yield 'It mutates correctly when provided with a constant' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $a = str_repeat(\Class_With_Const::Const, 3);
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    $a = \Class_With_Const::Const;
                    PHP,
            ),
        ];

        yield 'It mutates correctly when a backslash is in front of str_repeat' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $a = \str_repeat('Hello!');
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    $a = 'Hello!';
                    PHP,
            ),
        ];

        yield 'It mutates correctly within if statements' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $a = 'Hello!';
                    if (str_repeat($a, 3) === $b) {
                        return true;
                    }
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    $a = 'Hello!';
                    if ($a === $b) {
                        return true;
                    }
                    PHP,
            ),
        ];

        yield 'It mutates correctly when str_repeat is wrongly capitalized' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $a = sTr_RePeAt('Hello!', 3);
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    $a = 'Hello!';
                    PHP,
            ),
        ];

        yield 'It mutates correctly when str_repeat uses other functions as input' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $a = str_repeat(
                        $foo->bar(),
                        $foo->baz()
                    );
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
                    $a = array_map(function ($b) {
                        return str_repeat($b, 3);
                    }, ['A', 'B']);
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    $a = array_map(function ($b) {
                        return $b;
                    }, ['A', 'B']);
                    PHP,
            ),
        ];

        yield 'It does not mutate other str_ calls' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $a = str_pad('Hello!', 8, '!');
                    PHP,
            ),
        ];

        yield 'It does not mutate functions named str_repeat' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    function str_repeat($input, $multiplier)
                    {
                    }
                    PHP,
            ),
        ];

        yield 'It does not break when provided with a variable function name' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $a = 'str_repeat';

                    $b = $a('foo', 3);
                    PHP,
            ),
        ];
    }
}
