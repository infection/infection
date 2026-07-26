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

namespace Infection\Tests\Mutator\Cast;

use Infection\Mutator\Cast\CastString;
use Infection\Testing\BaseMutatorTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(CastString::class)]
final class CastStringTest extends BaseMutatorTestCase
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
        yield 'It removes casting to string' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    (string) 1.0;
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    1.0;
                    PHP,
            ),
        ];

        yield 'It removes casting to string in conditions' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    if ((string) random_int()) {
                        echo 'Hello';
                    }
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    if (random_int()) {
                        echo 'Hello';
                    }
                    PHP,
            ),
        ];

        yield 'It removes casting to string in global return' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    return (string) random_int();
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    return random_int();
                    PHP,
            ),
        ];

        yield 'It removes casting to string in return of untyped-method' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    function noReturnType()
                    {
                        return (string) random_int();
                    }
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    function noReturnType()
                    {
                        return random_int();
                    }
                    PHP,
            ),
        ];

        yield 'It removes casting to string in return of string-method when strict-types=0' => [
            <<<'PHP'
                <?php
                declare (strict_types=0);

                class Demo {
                    function returnsString(): string
                    {
                        return (string) random_int();
                    }
                }
                PHP,
            <<<'PHP'
                <?php
                declare (strict_types=0);

                class Demo {
                    function returnsString(): string
                    {
                        return random_int();
                    }
                }
                PHP,
        ];

        yield 'It not removes casting to string in return of string-method when strict-types=1' => [
            <<<'PHP'
                <?php declare(strict_types=1);

                class Demo {
                    function returnsString(): string {
                        return (string) random_int();
                    }
                }
                PHP,
        ];

        yield 'It not removes casting to string in nested return of string-method when strict-types=1' => [
            <<<'PHP'
                <?php declare(strict_types=1);

                class Demo {
                    function returnsString(): string {
                        if (true) {
                            return (string) random_int();
                        }
                        return "x";
                    }
                }
                PHP,
        ];

        yield 'It removes casting to string in method parameters when strict-types=0' => [
            <<<'PHP'
                <?php
                declare (strict_types=0);

                class Demo {
                    function doFoo()
                    {
                        trim((string) 5);
                    }
                }
                PHP,
            <<<'PHP'
                <?php
                declare (strict_types=0);

                class Demo {
                    function doFoo()
                    {
                        trim(5);
                    }
                }
                PHP,
        ];

        yield 'It not removes casting to string in function parameters when strict-types=1' => [
            <<<'PHP'
                <?php declare(strict_types=1);

                class Demo {
                    function doFoo()
                    {
                        trim((string) 5);
                    }
                }
                PHP,
        ];
    }
}
