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

use Infection\Mutator\Cast\CastBool;
use Infection\Testing\BaseMutatorTestCase;
use Infection\Tests\Mutator\MutatorFixturesProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(CastBool::class)]
final class CastBoolTest extends BaseMutatorTestCase
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
        yield 'It removes casting to bool with "bool"' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    (bool) 1;
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    1;
                    PHP,
            ),
        ];

        yield 'It removes casting to bool with "boolean"' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    (boolean) 1;
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    1;
                    PHP,
            ),
        ];

        yield 'It removes casting to bool in conditions' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    if ((bool) preg_match()) {
                        echo 'Hello';
                    }
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    if (preg_match()) {
                        echo 'Hello';
                    }
                    PHP,
            ),
        ];

        yield 'It removes casting to bool in global return' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    return (bool) preg_match();
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    return preg_match();
                    PHP,
            ),
        ];

        yield 'It removes casting to bool in return of untyped-function' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    function noReturnType()
                    {
                        return (bool) preg_match();
                    }
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    function noReturnType()
                    {
                        return preg_match();
                    }
                    PHP,
            ),
        ];

        yield 'It removes casting to bool in return of bool-function when strict-types=0' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    declare (strict_types=0);
                    function returnsBool(): bool
                    {
                        return (bool) preg_match();
                    }
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    declare (strict_types=0);
                    function returnsBool(): bool
                    {
                        return preg_match();
                    }
                    PHP,
            ),
        ];

        yield 'It not removes casting to bool in return of bool-function when strict-types=1' => [
            <<<'PHP'
                <?php declare(strict_types=1);

                function returnsBool(): bool {
                    return (bool) preg_match();
                }
                PHP,
        ];

        yield 'It not removes casting to bool in nested return of bool-function when strict-types=1' => [
            <<<'PHP'
                <?php declare(strict_types=1);

                function returnsBool(): bool {
                    if (true) {
                        return (bool) preg_match();
                    }
                    return false;
                }
                PHP,
        ];

        yield 'It not removes casting to bool in return of bool-method when strict-types=1' => [
            MutatorFixturesProvider::getFixtureFileContent(self::class, 'bool-method.php'),
        ];

        yield 'It removes casting to bool in function parameters when strict-types=0' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    declare (strict_types=0);
                    function doFoo()
                    {
                        in_array(strict: (bool) $s);
                    }
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    declare (strict_types=0);
                    function doFoo()
                    {
                        in_array(strict: $s);
                    }
                    PHP,
            ),
        ];

        yield 'It not removes casting to bool in function parameters when strict-types=1' => [
            <<<'PHP'
                <?php declare(strict_types=1);

                function doFoo()
                {
                    in_array(strict: (bool) $s);
                }
                PHP,
        ];
    }
}
