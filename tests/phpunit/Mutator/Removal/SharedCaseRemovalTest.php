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

namespace Infection\Tests\Mutator\Removal;

use Infection\Mutator\Removal\SharedCaseRemoval;
use Infection\Testing\BaseMutatorTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(SharedCaseRemoval::class)]
final class SharedCaseRemovalTest extends BaseMutatorTestCase
{
    /**
     * @param string|string[]|null $expected
     * @param mixed[] $settings
     */
    #[DataProvider('mutationsProvider')]
    public function test_it_can_mutate(string $input, string|array|null $expected = [], array $settings = []): void
    {
        $this->assertMutatesInput($input, $expected, $settings);
    }

    public static function mutationsProvider(): iterable
    {
        yield 'It does not mutate single cases with a body' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    switch(true) {
                        case true:
                            $a = [];
                            break;
                        case false:
                            $a = [];
                            break;
                    }
                    PHP,
            ),
        ];

        yield 'It removes cases that share a body' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    switch(true) {
                        case true:
                        case false:
                            $a = [];
                            break;
                        case null:
                            $a = [];
                            break;
                    }
                    PHP,
            ),
            [
                self::wrapCodeInMethod(
                    <<<'PHP'
                        switch (true) {
                            case false:
                                $a = [];
                                break;
                            case null:
                                $a = [];
                                break;
                        }
                        PHP,
                ),
                self::wrapCodeInMethod(
                    <<<'PHP'
                        switch (true) {
                            case true:
                                $a = [];
                                break;
                            case null:
                                $a = [];
                                break;
                        }
                        PHP,
                ),
            ],
        ];

        yield 'It removes default if it shares a body with a case' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    switch(true) {
                        case true:
                            $a = [];
                            break;
                        case false:
                        default:
                            $b = [];
                            break;
                    }
                    PHP,
            ),
            [
                self::wrapCodeInMethod(
                    <<<'PHP'
                        switch (true) {
                            case true:
                                $a = [];
                                break;
                            default:
                                $b = [];
                                break;
                        }
                        PHP,
                ),
                self::wrapCodeInMethod(
                    <<<'PHP'
                        switch (true) {
                            case true:
                                $a = [];
                                break;
                            case false:
                                $b = [];
                                break;
                        }
                        PHP,
                ),
            ],
        ];
    }
}
