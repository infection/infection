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

namespace Infection\Tests\Mutator\Operator;

use Infection\Mutator\Operator\IfNegation;
use Infection\Testing\BaseMutatorTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(IfNegation::class)]
final class IfNegationTest extends BaseMutatorTestCase
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
        yield 'It preserve elseif and else expression' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    if ($this->fooBar()) {
                        return 1;
                    } elseif ($this->barFoo()) {
                        return 2;
                    } else {
                        return 3;
                    }
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    if (!$this->fooBar()) {
                        return 1;
                    } elseif ($this->barFoo()) {
                        return 2;
                    } else {
                        return 3;
                    }
                    PHP,
            ),
        ];

        yield 'It mutates array item fetch' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    if ($array[0]) {
                    }
                    PHP,
            ),
            [
                self::wrapCodeInMethod(
                    <<<'PHP'
                        if (!$array[0]) {
                        }
                        PHP,
                ),
            ],
        ];

        yield 'It mutates variable' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    if ($foo) {
                    }
                    PHP,
            ),
            [
                self::wrapCodeInMethod(
                    <<<'PHP'
                        if (!$foo) {
                        }
                        PHP,
                ),
            ],
        ];

        yield 'It mutates method call' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    if ($this->foo()) {
                    }
                    PHP,
            ),
            [
                self::wrapCodeInMethod(
                    <<<'PHP'
                        if (!$this->foo()) {
                        }
                        PHP,
                ),
            ],
        ];

        yield 'It mutates static calls' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    if (self::foo()) {
                    }
                    PHP,
            ),
            [
                self::wrapCodeInMethod(
                    <<<'PHP'
                        if (!self::foo()) {
                        }
                        PHP,
                ),
            ],
        ];

        yield 'It mutates constant calls' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    if (self::FOO) {
                    }
                    PHP,
            ),
            [
                self::wrapCodeInMethod(
                    <<<'PHP'
                        if (!self::FOO) {
                        }
                        PHP,
                ),
            ],
        ];

        yield 'It mutates closure calls' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    if ($foo()) {
                    }
                    PHP,
            ),
            [
                self::wrapCodeInMethod(
                    <<<'PHP'
                        if (!$foo()) {
                        }
                        PHP,
                ),
            ],
        ];

        yield 'It mutates invoke calls' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    if (($this->foo)()) {
                    }
                    PHP,
            ),
            [
                self::wrapCodeInMethod(
                    <<<'PHP'
                        if (!($this->foo)()) {
                        }
                        PHP,
                ),
            ],
        ];

        yield 'It mutates function calls' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    if (a()) {
                    }
                    PHP,
            ),
            [
                self::wrapCodeInMethod(
                    <<<'PHP'
                        if (!a()) {
                        }
                        PHP,
                ),
            ],
        ];

        yield 'It does not mutate already negated expression' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    if (!$this->fooBar()) {
                    }
                    PHP,
            ),
        ];

        yield 'It does not mutate equal comparison' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    if ($this->fooBar() == 1) {
                    }
                    PHP,
            ),
        ];

        yield 'It does not mutate not equal comparison' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    if ($this->fooBar() != 1) {
                    }
                    PHP,
            ),
        ];

        yield 'It does not mutate identical comparison' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    if ($this->fooBar() === true) {
                    }
                    PHP,
            ),
        ];

        yield 'It does not mutate not identical comparison' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    if ($this->fooBar() !== true) {
                    }
                    PHP,
            ),
        ];

        yield 'It does not mutate and condition' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    if (a() && b()) {
                    }
                    PHP,
            ),
        ];

        yield 'It does not mutate or condition' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    if (a() || b()) {
                    }
                    PHP,
            ),
        ];
    }
}
