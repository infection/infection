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

use Infection\Mutator\Boolean\LogicalOrSingleSubExprNegation;
use Infection\Testing\BaseMutatorTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(LogicalOrSingleSubExprNegation::class)]
final class LogicalOrSingleSubExprNegationTest extends BaseMutatorTestCase
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
        yield 'It mutates array item fetch' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $var = $array[0] || $array[1];
                    PHP,
            ),
            [
                self::wrapCodeInMethod(
                    <<<'PHP'
                        $var = !$array[0] || $array[1];
                        PHP,
                ),
                self::wrapCodeInMethod(
                    <<<'PHP'
                        $var = $array[0] || !$array[1];
                        PHP,
                ),
            ],
        ];

        yield 'It mutates variable' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $var = $foo || $bar;
                    PHP,
            ),
            [
                self::wrapCodeInMethod(
                    <<<'PHP'
                        $var = !$foo || $bar;
                        PHP,
                ),
                self::wrapCodeInMethod(
                    <<<'PHP'
                        $var = $foo || !$bar;
                        PHP,
                ),
            ],
        ];

        yield 'It mutates method call' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $var = $this->foo() || $bar->baz();
                    PHP,
            ),
            [
                self::wrapCodeInMethod(
                    <<<'PHP'
                        $var = !$this->foo() || $bar->baz();
                        PHP,
                ),
                self::wrapCodeInMethod(
                    <<<'PHP'
                        $var = $this->foo() || !$bar->baz();
                        PHP,
                ),
            ],
        ];

        yield 'It mutates static calls' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $var = self::foo() || static::bar() || Test::baz();
                    PHP,
            ),
            [
                self::wrapCodeInMethod(
                    <<<'PHP'
                        $var = !self::foo() || static::bar() || Test::baz();
                        PHP,
                ),
                self::wrapCodeInMethod(
                    <<<'PHP'
                        $var = self::foo() || !static::bar() || Test::baz();
                        PHP,
                ),
                self::wrapCodeInMethod(
                    <<<'PHP'
                        $var = self::foo() || static::bar() || !Test::baz();
                        PHP,
                ),
            ],
        ];

        yield 'It mutates constant calls' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $var = self::FOO || self::BAR;
                    PHP,
            ),
            [
                self::wrapCodeInMethod(
                    <<<'PHP'
                        $var = !self::FOO || self::BAR;
                        PHP,
                ),
                self::wrapCodeInMethod(
                    <<<'PHP'
                        $var = self::FOO || !self::BAR;
                        PHP,
                ),
            ],
        ];

        yield 'It mutates closure calls' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $var = $foo() || $bar();
                    PHP,
            ),
            [
                self::wrapCodeInMethod(
                    <<<'PHP'
                        $var = !$foo() || $bar();
                        PHP,
                ),
                self::wrapCodeInMethod(
                    <<<'PHP'
                        $var = $foo() || !$bar();
                        PHP,
                ),
            ],
        ];

        yield 'It mutates invoke calls' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $var = ($this->foo)() || ($this->bar)();
                    PHP,
            ),
            [
                self::wrapCodeInMethod(
                    <<<'PHP'
                        $var = !($this->foo)() || ($this->bar)();
                        PHP,
                ),
                self::wrapCodeInMethod(
                    <<<'PHP'
                        $var = ($this->foo)() || !($this->bar)();
                        PHP,
                ),
            ],
        ];

        yield 'It mutates function calls' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $var = a() || b();
                    PHP,
            ),
            [
                self::wrapCodeInMethod(
                    <<<'PHP'
                        $var = !a() || b();
                        PHP,
                ),
                self::wrapCodeInMethod(
                    <<<'PHP'
                        $var = a() || !b();
                        PHP,
                ),
            ],
        ];

        yield 'It mutates and with more expressions' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $var = a() || b() || c() || d();
                    PHP,
            ),
            [
                self::wrapCodeInMethod(
                    <<<'PHP'
                        $var = !a() || b() || c() || d();
                        PHP,
                ),
                self::wrapCodeInMethod(
                    <<<'PHP'
                        $var = a() || !b() || c() || d();
                        PHP,
                ),
                self::wrapCodeInMethod(
                    <<<'PHP'
                        $var = a() || b() || !c() || d();
                        PHP,
                ),
                self::wrapCodeInMethod(
                    <<<'PHP'
                        $var = a() || b() || c() || !d();
                        PHP,
                ),
            ],
        ];

        yield 'It does not mutate equal\'s expressions' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $var = a() != 1 || b() == 1;
                    PHP,
            ),
        ];

        yield 'It does not mutate identical\'s expressions' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $var = a() !== 1 || b() === 1;
                    PHP,
            ),
        ];

        yield 'It does not mutate already negated expressions' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $var = !(a() || !b());
                    PHP,
            ),
            [
                self::wrapCodeInMethod(
                    <<<'PHP'
                        $var = !(!a() || !b());
                        PHP,
                ),
            ],
        ];

        yield 'It mutates expressions with logical and' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $var = a() || b() && c();
                    PHP,
            ),
            [
                self::wrapCodeInMethod(
                    <<<'PHP'
                        $var = !a() || b() && c();
                        PHP,
                ),
            ],
        ];

        yield 'It preserves formatting for non-modified code' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    class TestFormatPreserving {
                        // some comment
                        public function test(): bool { // and comment here
                            return 1

                              && 2;
                        }
                    }

                    $var = a() || b();
                    PHP,
            ),
            [
                self::wrapCodeInMethod(
                    <<<'PHP'
                        class TestFormatPreserving {
                            // some comment
                            public function test(): bool { // and comment here
                                return 1

                                  && 2;
                            }
                        }

                        $var = !a() || b();
                        PHP,
                ),
                self::wrapCodeInMethod(
                    <<<'PHP'
                        class TestFormatPreserving {
                            // some comment
                            public function test(): bool { // and comment here
                                return 1

                                  && 2;
                            }
                        }

                        $var = a() || !b();
                        PHP,
                ),
            ],
        ];
    }
}
