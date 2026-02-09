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

use Infection\Mutator\Cast\CastInt;
use Infection\Testing\BaseMutatorTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(CastInt::class)]
final class CastIntTest extends BaseMutatorTestCase
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
        yield 'It removes casting to int' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    (int) 1.0;
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    1.0;
                    PHP,
            ),
        ];

        yield 'It removes casting to integer' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    (integer) 1.0;
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    1.0;
                    PHP,
            ),
        ];

        yield 'It removes casting to integer in conditions' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    if ((int) round()) {
                        echo 'Hello';
                    }
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    if (round()) {
                        echo 'Hello';
                    }
                    PHP,
            ),
        ];

        yield 'It removes casting to integer in global return' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    return (int) round();
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    return round();
                    PHP,
            ),
        ];

        yield 'It removes casting to integer in return of untyped-function' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    function noReturnType()
                    {
                        return (int) round();
                    }
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    function noReturnType()
                    {
                        return round();
                    }
                    PHP,
            ),
        ];

        yield 'It removes casting to integer in return of int-function when strict-types=0' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    declare (strict_types=0);
                    function returnsInt(): int
                    {
                        return (int) round();
                    }
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    declare (strict_types=0);
                    function returnsInt(): int
                    {
                        return round();
                    }
                    PHP,
            ),
        ];

        yield 'It not removes casting to integer in return of int-function when strict-types=1' => [
            <<<'PHP'
                <?php declare(strict_types=1);

                function returnsInt(): int {
                    return (int) round();
                }
                PHP,
        ];

        yield 'It not removes casting to integer in nested return of int-function when strict-types=1' => [
            <<<'PHP'
                <?php declare(strict_types=1);

                function returnsInt(): int {
                    if (true) {
                        return (int) round();
                    }
                    return 0;
                }
                PHP,
        ];

        yield 'It removes casting to int in function parameters when strict-types=0' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    declare (strict_types=0);
                    function doFoo()
                    {
                        range((int) $s);
                    }
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    declare (strict_types=0);
                    function doFoo()
                    {
                        range($s);
                    }
                    PHP,
            ),
        ];

        yield 'It not removes casting to int in function parameters when strict-types=1' => [
            <<<'PHP'
                <?php declare(strict_types=1);

                function doFoo()
                {
                    range((int) $s);
                }
                PHP,
        ];
    }
}
