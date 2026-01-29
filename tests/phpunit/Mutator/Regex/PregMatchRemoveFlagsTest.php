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

namespace Infection\Tests\Mutator\Regex;

use Infection\Mutator\Regex\PregMatchRemoveFlags;
use Infection\Testing\BaseMutatorTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @internal
 */
#[CoversClass(PregMatchRemoveFlags::class)]
final class PregMatchRemoveFlagsTest extends BaseMutatorTestCase
{
    /**
     * @param string|string[]|null $expected
     */
    #[DataProvider('provideMutationCases')]
    public function test_mutator(string $input, string|array|null $expected = null): void
    {
        $this->assertMutatesInput($input, $expected);
    }

    public static function provideMutationCases(): iterable
    {
        yield 'It removes flags one by one' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    preg_match('~some-regexp$~igu', 'irrelevant');
                    PHP,
            ),
            [
                self::wrapCodeInMethod(
                    <<<'PHP'
                        preg_match('~some-regexp$~gu', 'irrelevant');
                        PHP,
                ),
                self::wrapCodeInMethod(
                    <<<'PHP'
                        preg_match('~some-regexp$~iu', 'irrelevant');
                        PHP,
                ),
                self::wrapCodeInMethod(
                    <<<'PHP'
                        preg_match('~some-regexp$~ig', 'irrelevant');
                        PHP,
                ),
            ],
        ];

        yield 'It does not mutate when no flags are used' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    preg_match('~some-regexp$~', 'irrelevant');
                    PHP,
            ),
        ];

        yield 'It mutates correctly preg_match function is wrongly capitalized' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    pReG_MaTcH('~some-regexp$~ig', 'irrelevant');
                    PHP,
            ),
            [
                self::wrapCodeInMethod(
                    <<<'PHP'
                        pReG_MaTcH('~some-regexp$~g', 'irrelevant');
                        PHP,
                ),
                self::wrapCodeInMethod(
                    <<<'PHP'
                        pReG_MaTcH('~some-regexp$~i', 'irrelevant');
                        PHP,
                ),
            ],
        ];

        yield 'It mutates correctly when delimeter is not standard' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    pReG_MaTcH('^some-regexp$^ig', 'irrelevant');
                    PHP,
            ),
            [
                self::wrapCodeInMethod(
                    <<<'PHP'
                        pReG_MaTcH('^some-regexp$^g', 'irrelevant');
                        PHP,
                ),
                self::wrapCodeInMethod(
                    <<<'PHP'
                        pReG_MaTcH('^some-regexp$^i', 'irrelevant');
                        PHP,
                ),
            ],
        ];

        yield 'It does not mutate regular expression with an encapsed variable' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    preg_match("/^-\s*{$regexWithEscapedDelimiters}$/mu", $diff);
                    PHP,
            ),
        ];

        yield 'It does not mutate regular expression when provided with an unpacked array' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    preg_match(...foo());
                    PHP,
            ),
        ];

        yield 'It does not mutate regular expression when provided with a variable' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    preg_match($regex, 'irrelevant');
                    PHP,
            ),
        ];

        yield 'It does not mutate when provided with a variable function name' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $f = 'preg_match';

                    $f('~some-regexp$~ig', 'irrelevant');
                    PHP,
            ),
        ];
    }
}
