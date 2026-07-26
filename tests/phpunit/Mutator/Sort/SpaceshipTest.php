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

namespace Infection\Tests\Mutator\Sort;

use Infection\Mutator\Sort\Spaceship;
use Infection\Testing\BaseMutatorTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(Spaceship::class)]
final class SpaceshipTest extends BaseMutatorTestCase
{
    public function test_get_name(): void
    {
        $this->assertSame('Spaceship', $this->createMutator()->getName());
    }

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
        yield 'It swaps spaceship operators' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $a <=> $b;
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    $b <=> $a;
                    PHP,
            ),
        ];

        yield 'It does not swap operators when result is identical zero on the right side' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    ($a <=> $b) === 0;
                    PHP,
            ),
        ];

        yield 'It does not swap operators when result is identical zero on the left side' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    0 === ($a <=> $b);
                    PHP,
            ),
        ];

        yield 'It does not swap operators when result is equal to zero on the right side' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    ($a <=> $b) == 0;
                    PHP,
            ),
        ];

        yield 'It does not swap operators when result is equal to zero as string on the right side' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    ($a <=> $b) == '0';
                    PHP,
            ),
        ];

        yield 'It does not swap operators when result is equal to zero on the left side' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    0 == ($a <=> $b);
                    PHP,
            ),
        ];

        yield 'It does not swap operators when result is equal to zero as string on the left side' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    '0' == ($a <=> $b);
                    PHP,
            ),
        ];

        yield 'It does not swap operators when result is equal to zero in float format on the right side' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    ($a <=> $b) == 0.0;
                    PHP,
            ),
        ];

        yield 'It does not swap operators when result is equal to zero in float format as string on the right side' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    ($a <=> $b) == '0.0';
                    PHP,
            ),
        ];

        yield 'It does not swap operators when result is equal to zero in float format on the left side' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    0.0 == ($a <=> $b);
                    PHP,
            ),
        ];

        yield 'It does not swap operators when result is equal to zero in float format as string on the left side' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    '0.0' == ($a <=> $b);
                    PHP,
            ),
        ];
    }
}
