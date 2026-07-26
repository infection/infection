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

namespace Infection\Tests\Mutator\Regex;

use Infection\Mutator\Regex\PregQuote;
use Infection\Testing\BaseMutatorTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(PregQuote::class)]
final class PregQuoteTest extends BaseMutatorTestCase
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
                    $a = preg_quote('bbbb');
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    $a = 'bbbb';
                    PHP,
            ),
        ];

        yield 'It mutates correctly when provided with a variable' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $a = 'to_quote';
                    $a = preg_quote($a);
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    $a = 'to_quote';
                    $a = $a;
                    PHP,
            ),
        ];

        yield 'It mutates correctly when provided with a constant' => [
            self::wrapCodeInMethod(
                <<<'PHP'

                    $a = preg_quote(\Class_With_Const::Const);
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'

                    $a = \Class_With_Const::Const;
                    PHP,
            ),
        ];

        yield 'It mutates correctly when a backslash is in front of the preg_quote' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $a = \preg_quote('bbbb');
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    $a = 'bbbb';
                    PHP,
            ),
        ];

        yield 'It mutates correctly when preg_quote has a second parameter' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $a = preg_quote('bbbb','/');
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    $a = 'bbbb';
                    PHP,
            ),
        ];

        yield 'It does not mutate other regex calls' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $a = preg_match('bbbb', '/');
                    PHP,
            ),
        ];

        yield 'It does not mutate functions named preg_quote' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    function preg_quote($text, $other)
                    {
                    }
                    PHP,
            ),
        ];

        yield 'It mutates correctly within if statements' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $a = 'string';
                    if (preg_quote($a) === $a) {
                        return true;
                    }
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    $a = 'string';
                    if ($a === $a) {
                        return true;
                    }
                    PHP,
            ),
        ];

        yield 'It mutates correctly when preg_quote is wrongly capitalized' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $a = PreG_qUotE('bbbb','/');
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    $a = 'bbbb';
                    PHP,
            ),
        ];

        yield 'It mutates correctly when preg_quote uses another function as input' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $a = preg_quote($foo->bar(),'/');
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    $a = $foo->bar();
                    PHP,
            ),
        ];

        yield 'It mutates correctly within a more complex situation' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $bar = function ($input) {
                        return array_map(function ($key, $value) {
                            return strtolower(preg_quote($key . (ctype_alnum($value) ? '' : $value), '/'));
                        }, $input);
                    };
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    $bar = function ($input) {
                        return array_map(function ($key, $value) {
                            return strtolower($key . (ctype_alnum($value) ? '' : $value));
                        }, $input);
                    };
                    PHP,
            ),
        ];

        yield 'It does not mutate when the function name can\'t be determined' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $a = $method($foo->bar(), '/');
                    $b = ('preg_quote')('/asdf/', '/');
                    $c = $class->{'foo'}('/asdf/');
                    $d = Foo::{'foo'}('/asdf/');
                    $e = Foo::$bar('/asdf/');
                    $f = ($foo->bar)('/asdf/');

                    PHP,
            ),
        ];
    }
}
