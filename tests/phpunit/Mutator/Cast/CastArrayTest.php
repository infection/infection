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

use Infection\Mutator\Cast\CastArray;
use Infection\Testing\BaseMutatorTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(CastArray::class)]
final class CastArrayTest extends BaseMutatorTestCase
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
        yield 'It removes casting to array' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    (array) 1.0;
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    1.0;
                    PHP,
            ),
        ];

        yield 'It removes casting to array in conditions' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    if ((array) implode()) {
                        echo 'Hello';
                    }
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    if (implode()) {
                        echo 'Hello';
                    }
                    PHP,
            ),
        ];

        yield 'It removes casting to array in global return' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    return (array) implode();
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    return implode();
                    PHP,
            ),
        ];

        yield 'It removes casting to array in return of untyped-function' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    function noReturnType()
                    {
                        return (array) implode();
                    }
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    function noReturnType()
                    {
                        return implode();
                    }
                    PHP,
            ),
        ];

        yield 'It removes casting to array in return of array-function when strict-types=0' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    declare (strict_types=0);
                    function returnsArray(): array
                    {
                        return (array) implode();
                    }
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    declare (strict_types=0);
                    function returnsArray(): array
                    {
                        return implode();
                    }
                    PHP,
            ),
        ];

        yield 'It not removes casting to array in return of array-function when strict-types=1' => [
            <<<'PHP'
                <?php declare(strict_types=1);

                function returnsArray(): array {
                    return (array) implode();
                }
                PHP,
        ];

        yield 'It not removes casting to array in nested return of array-function when strict-types=1' => [
            <<<'PHP'
                <?php declare(strict_types=1);

                function returnsArray(): array {
                    if (true) {
                        return (array) implode();
                    }
                    return [];
                }
                PHP,
        ];

        yield 'It removes casting to array in function parameters when strict-types=0' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    declare (strict_types=0);
                    function doFoo()
                    {
                        implode((array) 5);
                    }
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    declare (strict_types=0);
                    function doFoo()
                    {
                        implode(5);
                    }
                    PHP,
            ),
        ];

        yield 'It not removes casting to array in function parameters when strict-types=1' => [
            <<<'PHP'
                <?php declare(strict_types=1);

                function doFoo()
                {
                    implode((array) 5);
                }
                PHP,
        ];
    }
}
