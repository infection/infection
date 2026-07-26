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

use Infection\Mutator\Boolean\TrueValue;
use Infection\Testing\BaseMutatorTestCase;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Name;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(TrueValue::class)]
final class TrueValueTest extends BaseMutatorTestCase
{
    /**
     * @param string|string[] $expected
     * @param array<string, bool> $settings
     */
    #[DataProvider('mutationsProvider')]
    public function test_it_can_mutate(string $input, string|array $expected = [], array $settings = []): void
    {
        $this->assertMutatesInput($input, $expected, $settings);
    }

    public static function mutationsProvider(): iterable
    {
        yield 'It mutates true to false' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    return true;
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    return false;
                    PHP,
            ),
        ];

        yield 'It mutates inside function call when function is a variable' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $a = 'foo';
                    $a(true);
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    $a = 'foo';
                    $a(false);
                    PHP,
            ),
        ];

        yield 'It mutates inside function call when function is a string' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    ('function_name')(true);
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    ('function_name')(false);
                    PHP,
            ),
        ];

        yield 'It does not mutate the string true to false' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    return 'true';
                    PHP,
            ),
        ];

        yield 'It does not mutate switch true to false' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    switch (true) {}
                    PHP,
            ),
        ];

        yield 'It does not mutate in ternary condition to prevent overlap with TernaryMutator' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $x == true ? 'yes' : 'no';
                    PHP,
            ),
        ];

        yield 'It does not mutate in conditions to prevent overlap with equal' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    if ($x == true) {
                    } else {
                    }
                    PHP,
            ),
        ];

        yield 'It does not mutate in conditions to prevent overlap with not-equal' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    if ($x != true) {
                    } else {
                    }
                    PHP,
            ),
        ];

        yield 'It does not mutate in conditions to prevent overlap with identical' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    if ($x === true) {
                    } else {
                    }
                    PHP,
            ),
        ];

        yield 'It does not mutate in conditions to prevent overlap with not-identical' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    if ($x !== true) {
                    } else {
                    }
                    PHP,
            ),
        ];

        yield 'It does not mutate match(true) to prevent overlap with MatchArmRemoval' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    match(true) {
                        $count > 0 && $count <= 10 => 'small',
                        $count <= 50 => 'medium',
                        $count > 50 => 'huge',
                    };
                    PHP,
            ),
        ];

        yield 'It mutates all caps true to false' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    return TRUE;
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    return false;
                    PHP,
            ),
        ];

        yield 'It does not mutate when used in "in_array" function by default' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    in_array($a, $b, true);
                    PHP,
            ),
        ];

        yield 'It does not mutate when used in "\in_array" function by default' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    \in_array($a, $b, true);
                    PHP,
            ),
        ];

        yield 'It mutates when used in a method named "in_array"' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $a->in_array($b, $c, true);
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    $a->in_array($b, $c, false);
                    PHP,
            ),
        ];

        yield 'It mutates when used in "\in_array" function and explicitly enabled in settings' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    \in_array($a, $b, true);
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    \in_array($a, $b, false);
                    PHP,
            ),
            ['in_array' => true],
        ];

        yield 'It does not mutate when used in "\in_array" function and explicitly disabled' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    \in_array($a, $b, true);
                    PHP,
            ),
            [],
            ['in_array' => false],
        ];

        yield 'It does not mutate when used in "array_search" function by default' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    array_search($a, $b, true);
                    PHP,
            ),
        ];

        yield 'It does not mutate when used in "\array_search" function by default' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    \array_search($a, $b, true);
                    PHP,
            ),
        ];

        yield 'It mutates when used in a method named "array_search"' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $a->array_search($b, $c, true);
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    $a->array_search($b, $c, false);
                    PHP,
            ),
        ];

        yield 'It mutates when used in "array_search" function and explicitly enabled in settings' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    array_search($a, $b, true);
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    array_search($a, $b, false);
                    PHP,
            ),
            ['array_search' => true],
        ];

        yield 'It does not mutate when used in "\array_search" function and explicitly disabled' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    \array_search($a, $b, true);
                    PHP,
            ),
            [],
            ['array_search' => false],
        ];

        yield 'It does not mutate when used in "\array_search" function and explicitly disabled and function is wrongly capitalized' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    \aRrAy_SeArCh($a, $b, true);
                    PHP,
            ),
            [],
            ['array_search' => false],
        ];
    }

    public function test_mutates_true_value(): void
    {
        $falseValue = new ConstFetch(new Name('true'));

        $this->assertTrue($this->mutator->canMutate($falseValue));
    }

    public function test_does_not_mutate_false_value(): void
    {
        $trueValue = new ConstFetch(new Name('false'));

        $this->assertFalse($this->mutator->canMutate($trueValue));
    }
}
