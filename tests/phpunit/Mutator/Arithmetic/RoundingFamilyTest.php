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

namespace Infection\Tests\Mutator\Arithmetic;

use Infection\Mutator\Arithmetic\RoundingFamily;
use Infection\Testing\BaseMutatorTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(RoundingFamily::class)]
final class RoundingFamilyTest extends BaseMutatorTestCase
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
        yield 'It mutates round() to floor() and ceil()' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $var = round(1.23);
                    PHP,
            ),
            [
                self::wrapCodeInMethod(
                    <<<'PHP'
                        $var = floor(1.23);
                        PHP,
                ),
                self::wrapCodeInMethod(
                    <<<'PHP'
                        $var = ceil(1.23);
                        PHP,
                ),
            ],
        ];

        yield 'It mutates floor() to round() and ceil()' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $var = floor(1.23);
                    PHP,
            ),
            [
                self::wrapCodeInMethod(
                    <<<'PHP'
                        $var = ceil(1.23);
                        PHP,
                ),
                self::wrapCodeInMethod(
                    <<<'PHP'
                        $var = round(1.23);
                        PHP,
                ),
            ],
        ];

        yield 'It mutates ceil() to round() and floor()' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $var = ceil(1.23);
                    PHP,
            ),
            [
                self::wrapCodeInMethod(
                    <<<'PHP'
                        $var = floor(1.23);
                        PHP,
                ),
                self::wrapCodeInMethod(
                    <<<'PHP'
                        $var = round(1.23);
                        PHP,
                ),
            ],
        ];

        yield 'It mutates if function name is incorrectly cased' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $var = CeIl(1.23);
                    PHP,
            ),
            [
                self::wrapCodeInMethod(
                    <<<'PHP'
                        $var = floor(1.23);
                        PHP,
                ),
                self::wrapCodeInMethod(
                    <<<'PHP'
                        $var = round(1.23);
                        PHP,
                ),
            ],
        ];

        yield 'It does not mutate if the function is a variable' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $foo = 'floor';
                    $foo(1.23);
                    PHP,
            ),
        ];

        yield 'It mutates round() to floor() and ceil() and leaves only 1 argument' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $var = round(1.23, 2, PHP_ROUND_HALF_UP);
                    PHP,
            ),
            [
                self::wrapCodeInMethod(
                    <<<'PHP'
                        $var = floor(1.23);
                        PHP,
                ),
                self::wrapCodeInMethod(
                    <<<'PHP'
                        $var = ceil(1.23);
                        PHP,
                ),
            ],
        ];

        yield 'It does not mutate other functions' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                     strtolower('lower');
                    PHP,
            ),
        ];

        yield 'It mutates \ceil() to round() and floor()' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $float = 1.23;
                    return \ceil($float);
                    PHP,
            ),
            [
                self::wrapCodeInMethod(
                    <<<'PHP'
                        $float = 1.23;
                        return floor($float);
                        PHP,
                ),
                self::wrapCodeInMethod(
                    <<<'PHP'
                        $float = 1.23;
                        return round($float);
                        PHP,
                ),
            ],
        ];

        yield 'It mutates \floor() to round() and ceil() in a control flow statement' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    while (\floor(1.23)) {
                    }
                    PHP,
            ),
            [
                self::wrapCodeInMethod(
                    <<<'PHP'
                        while (ceil(1.23)) {
                        }
                        PHP,
                ),
                self::wrapCodeInMethod(
                    <<<'PHP'
                        while (round(1.23)) {
                        }
                        PHP,
                ),
            ],
        ];

        yield 'It mutates ceil() to round() and floor() while assigning inside the function call' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    echo ceil($result = $this->average());
                    PHP,
            ),
            [
                self::wrapCodeInMethod(
                    <<<'PHP'
                        echo floor($result = $this->average());
                        PHP,
                ),
                self::wrapCodeInMethod(
                    <<<'PHP'
                        echo round($result = $this->average());
                        PHP,
                ),
            ],
        ];

        yield 'It mutates round() to ceil() and floor() during arithmetic operations' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    return round($this->positive / $this->total);
                    PHP,
            ),
            [
                self::wrapCodeInMethod(
                    <<<'PHP'
                        return floor($this->positive / $this->total);
                        PHP,
                ),
                self::wrapCodeInMethod(
                    <<<'PHP'
                        return ceil($this->positive / $this->total);
                        PHP,
                ),
            ],
        ];
    }
}
