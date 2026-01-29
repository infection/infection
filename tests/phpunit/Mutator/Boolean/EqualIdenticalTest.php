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

namespace Infection\Tests\Mutator\Boolean;

use Infection\Mutator\Boolean\EqualIdentical;
use Infection\Testing\BaseMutatorTestCase;
use Infection\Tests\Mutator\MutatorFixturesProvider;
use const PHP_VERSION_ID;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(EqualIdentical::class)]
final class EqualIdenticalTest extends BaseMutatorTestCase
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
        yield 'It mutates with two variables' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $a == $b;
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    $a === $b;
                    PHP,
            ),
        ];

        yield 'It mutates with a cast' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    (int) $c == 2;
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    (int) $c === 2;
                    PHP,
            ),
        ];

        yield 'It mutates with a constant' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $d == null;
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    $d === null;
                    PHP,
            ),
        ];

        yield 'It mutates with a function' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    false == strpos();
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    false === strpos();
                    PHP,
            ),
        ];

        yield 'It mutates equal operator into identical operator for maybe same type operations (string)' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $var == trim();
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    $var === trim();
                    PHP,
            ),
        ];

        yield 'It mutates equal operator into identical operator for maybe same type operations with inverse operands (string)' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    trim() == $var;
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    trim() === $var;
                    PHP,
            ),
        ];

        yield 'It does not mutate equal operator into identical operator for same type operations (string)' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    '' == trim();
                    PHP,
            ),
        ];

        yield 'It does not mutate equal operator into identical operator for inverse same type operations (string)' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    trim() == '';
                    PHP,
            ),
        ];

        yield 'It does not mutate equal operator into identical operator for same type operations (bool)' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    false == is_array();
                    PHP,
            ),
        ];

        yield 'It does not mutate equal operator into identical operator for inverse same type operations (bool)' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    is_array() == false;
                    PHP,
            ),
        ];

        yield 'It does not mutate equal operator into identical operator for same type operations (true)' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    true == is_array();
                    PHP,
            ),
        ];

        yield 'It does not mutate equal operator into identical operator for inverse same type operations (true)' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    is_array() == true;
                    PHP,
            ),
        ];

        yield 'It does not mutate equal operator into identical operator for same type operations (int)' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    5 == random_int();
                    PHP,
            ),
        ];

        yield 'It does not mutate equal operator into identical operator for inverse same type operations (int)' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    random_int() == 5;
                    PHP,
            ),
        ];

        yield 'It does not mutate equal operator into identical operator for same type operations (float)' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    3.0 == round();
                    PHP,
            ),
        ];

        yield 'It does not mutate equal operator into identical operator for inverse same type operations (float)' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    round() == 3.0;
                    PHP,
            ),
        ];

        yield 'It mutates equal operator into identical operator for different type operations with function operands (string)' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    strchr() == substr();
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    strchr() === substr();
                    PHP,
            ),
        ];

        yield 'It does not mutate equal operator into identical operator for same type operations with function operands (string)' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    trim() == substr();
                    PHP,
            ),
        ];

        yield 'It mutates equal operator into identical operator for incompatible types in operation' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    true == trim();
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    true === trim();
                    PHP,
            ),
        ];

        yield 'It mutates equal operator into identical operator for incompatible types in operation (inversed)' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    trim() == false;
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    trim() === false;
                    PHP,
            ),
        ];

        yield 'It mutates equal operator into identical operator for union type operations with falsy operand' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    preg_match() == 0;
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    preg_match() === 0;
                    PHP,
            ),
        ];

        yield 'It mutates equal operator into identical operator for union type operations with falsy operand (inverse)' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    0 == preg_match();
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    0 === preg_match();
                    PHP,
            ),
        ];

        yield 'It does not mutate equal operator into identical operator for union type operations with non-falsy operand' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    preg_match() == 1;
                    PHP,
            ),
        ];

        yield 'It does not mutate equal operator into identical operator for union type operations with non-falsy operand (inverse)' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    1 == preg_match();
                    PHP,
            ),
        ];

        yield 'It mutates equal operator into identical operator for union type operations which values cannot be narrowed to a single type' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    array_key_last() == null;
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    array_key_last() === null;
                    PHP,
            ),
        ];

        yield 'It mutates equal operator into identical operator for comparison of union type operands' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    preg_match() == array_key_last();
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    preg_match() === array_key_last();
                    PHP,
            ),
        ];

        yield 'It mutates equal operator into identical operator for comparison of different return-typed operands (union vs. named-type)' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    preg_match() == count();
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    preg_match() === count();
                    PHP,
            ),
        ];

        yield 'It mutates equal operator of non-reflectable functions' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    nooneKnowsThisFunction() == nooneKnowsThisFunction();
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    nooneKnowsThisFunction() === nooneKnowsThisFunction();
                    PHP,
            ),
        ];

        yield 'It mutates equal operator into identical operator for comparison of intersection types' => [
            MutatorFixturesProvider::getFixtureFileContent(self::class, 'identical-intersection-type.php'),
            <<<'PHP'
                <?php

                namespace EqualIdenticalIntersectionType;

                interface A
                {
                }
                interface B
                {
                }
                class C implements A, B
                {
                }
                function doFoo(): A&B
                {
                    return new C();
                }

                class Demo {
                    function compareFoos() {
                        doFoo() === doFoo();
                        doFoo() === doFoo();
                    }
                }

                PHP,
        ];

        yield 'It does not mutate equal operator into identical operator for empty array type' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    [] == explode();
                    PHP,
            ),
        ];

        yield 'It mutates equal operator into identical operator for non-empty-array type' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    ['abc'] == explode();
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    ['abc'] === explode();
                    PHP,
            ),
        ];

        yield 'It does not mutate equal operator into identical operator for static method call' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    [] == PhpToken::tokenize();
                    PHP,
            ),
        ];

        yield 'It mutates equal operator into identical operator for dynamic class static method call' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    [] == $s::tokenize();
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    [] === $s::tokenize();
                    PHP,
            ),
        ];

        yield 'It mutates equal operator into identical operator for dynamic static method call' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    [] == SomeClass::$method();
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    [] === SomeClass::$method();
                    PHP,
            ),
        ];

        yield 'It does not mutate equal operator into identical operator for class constant fetches' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    random_int() == RegexIterator::USE_KEY;
                    PHP,
            ),
        ];

        yield 'It mutates equal operator into identical operator for class constant fetches of different type' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    round() == RegexIterator::USE_KEY;
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    round() === RegexIterator::USE_KEY;
                    PHP,
            ),
        ];

        if (PHP_VERSION_ID >= 80400) {
            yield 'It does not mutate equal operator into identical operator for known int global constants' => [
                self::wrapCodeInMethod(
                    <<<'PHP'
                        PHP_MAJOR_VERSION == 5;
                        PHP,
                ),
            ];
        } else {
            yield 'It mutates equal operator into identical operator for global int constants without reflection info (PHP 8.3)' => [
                self::wrapCodeInMethod(
                    <<<'PHP'
                        PHP_MAJOR_VERSION == 5;
                        PHP,
                ),
                self::wrapCodeInMethod(
                    <<<'PHP'
                        PHP_MAJOR_VERSION === 5;
                        PHP,
                ),
            ];
        }

        yield 'It does not mutate equal operator into identical operator for known global constants' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    PHP_SAPI == 'phpdbg';
                    PHP,
            ),
        ];

        yield 'It does not mutate equal operator into identical operator for unknown global constants' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    NOONE_KNOWS_THIS_CONSTANT == 'phpdbg';
                    PHP,
            ),
        ];

        yield 'It mutates equal operator into identical operator for comparison against empty literal string' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $x == '';
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    $x === '';
                    PHP,
            ),
        ];

        yield 'It mutates equal operator into identical operator for comparison against empty literal string (inversed)' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    '' == $x;
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    '' === $x;
                    PHP,
            ),
        ];

        yield 'It mutates equal operator into identical operator for comparison against numeric literal string' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $x == '123';
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    $x === '123';
                    PHP,
            ),
        ];

        yield 'It mutates equal operator into identical operator for comparison against numeric literal string (inversed)' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    '123' == $x;
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    '123' === $x;
                    PHP,
            ),
        ];

        yield 'It does not mutate equal operator into identical operator for comparison against non-numeric&non-empty literal string' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $x == 'hello';
                    PHP,
            ),
        ];

        yield 'It does not mutate equal operator into identical operator for comparison against non-numeric&non-empty literal string (inversed)' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    'hello' == $x;
                    PHP,
            ),
        ];

        yield 'It does not mutate equal operator into identical operator for comparison against non-numeric&non-empty literal string (class constant)' => [
            MutatorFixturesProvider::getFixtureFileContent(self::class, 'non-numeric-non-empty-class-const.php'),
        ];
    }
}
