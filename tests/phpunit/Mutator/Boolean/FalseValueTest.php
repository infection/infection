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

use Infection\Mutator\Boolean\FalseValue;
use Infection\Testing\BaseMutatorTestCase;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Name;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(FalseValue::class)]
final class FalseValueTest extends BaseMutatorTestCase
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
        yield 'It mutates false to true' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    return false;
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    return true;
                    PHP,
            ),
        ];

        yield 'It does not mutate the string false to true' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    return 'false';
                    PHP,
            ),
        ];

        yield 'It does not mutate switch(false) to true' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    switch (false) {}
                    PHP,
            ),
        ];

        yield 'It does not mutate match(false) to true to prevent overlap with MatchArmRemoval' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    match(false) {
                        $count > 0 && $count <= 10 => 'small',
                        $count <= 50 => 'medium',
                        $count > 50 => 'huge',
                    };
                    PHP,
            ),
        ];

        yield 'It does not mutate in ternary condition to prevent overlap with TernaryMutator' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $x == false ? 'yes' : 'no';
                    PHP,
            ),
        ];

        yield 'It does not mutate in conditions to prevent overlap with equal' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    if ($x == false) {
                    } else {
                    }
                    PHP,
            ),
        ];

        yield 'It does not mutate in conditions to prevent overlap with not-equal' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    if ($x != false) {
                    } else {
                    }
                    PHP,
            ),
        ];

        yield 'It does not mutate in conditions to prevent overlap with identical' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    if ($x === false) {
                    } else {
                    }
                    PHP,
            ),
        ];

        yield 'It does not mutate in conditions to prevent overlap with not-identical' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    if ($x !== false) {
                    } else {
                    }
                    PHP,
            ),
        ];

        yield 'It mutates all caps false to true' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    return FALSE;
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    return true;
                    PHP,
            ),
        ];
    }

    public function test_mutates_false_value(): void
    {
        $falseValue = new ConstFetch(new Name('false'));

        $this->assertTrue($this->mutator->canMutate($falseValue));
    }

    public function test_does_not_mutate_true_value(): void
    {
        $falseValue = new ConstFetch(new Name('true'));

        $this->assertFalse($this->mutator->canMutate($falseValue));
    }
}
