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

use Infection\Mutator\Removal\MatchArmRemoval;
use Infection\Testing\BaseMutatorTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(MatchArmRemoval::class)]
final class MatchArmRemovalTest extends BaseMutatorTestCase
{
    /**
     * @param string|string[] $expected
     */
    #[DataProvider('mutationsProvider')]
    public function test_it_can_mutate(string $input, array|string $expected = []): void
    {
        $this->assertMutatesInput($input, $expected);
    }

    public static function mutationsProvider(): iterable
    {
        yield 'It removes match arm when more than one is defined' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    match ($x) {
                        0 => false,
                        1 => true,
                        2 => null,
                        default => throw new \Exception(),
                    };
                    PHP,
            ),
            [
                self::wrapCodeInMethod(
                    <<<'PHP'
                        match ($x) {
                            1 => true,
                            2 => null,
                            default => throw new \Exception(),
                        };
                        PHP,
                ),
                self::wrapCodeInMethod(
                    <<<'PHP'
                        match ($x) {
                            0 => false,
                            2 => null,
                            default => throw new \Exception(),
                        };
                        PHP,
                ),
                self::wrapCodeInMethod(
                    <<<'PHP'
                        match ($x) {
                            0 => false,
                            1 => true,
                            default => throw new \Exception(),
                        };
                        PHP,
                ),
                self::wrapCodeInMethod(
                    <<<'PHP'
                        match ($x) {
                            0 => false,
                            1 => true,
                            2 => null,
                        };
                        PHP,
                ),
            ],
        ];

        yield 'It does not remove one match arm' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    match ($x) {
                        0 => false,
                    };
                    PHP,
            ),
        ];

        yield 'It removes match arm condition when more than one is defined' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    match ($x) {
                        'cond1', 'cond2', 'cond3' => false,
                        2 => null,
                        default => throw new \Exception(),
                    };
                    PHP,
            ),
            [
                self::wrapCodeInMethod(
                    <<<'PHP'
                        match ($x) {
                            'cond2', 'cond3' => false,
                            2 => null,
                            default => throw new \Exception(),
                        };
                        PHP,
                ),
                self::wrapCodeInMethod(
                    <<<'PHP'
                        match ($x) {
                            'cond1', 'cond3' => false,
                            2 => null,
                            default => throw new \Exception(),
                        };
                        PHP,
                ),
                self::wrapCodeInMethod(
                    <<<'PHP'
                        match ($x) {
                            'cond1', 'cond2' => false,
                            2 => null,
                            default => throw new \Exception(),
                        };
                        PHP,
                ),
                self::wrapCodeInMethod(
                    <<<'PHP'
                        match ($x) {
                            'cond1', 'cond2', 'cond3' => false,
                            default => throw new \Exception(),
                        };
                        PHP,
                ),
                self::wrapCodeInMethod(
                    <<<'PHP'
                        match ($x) {
                            'cond1', 'cond2', 'cond3' => false,
                            2 => null,
                        };
                        PHP,
                ),
            ],
        ];
    }
}
