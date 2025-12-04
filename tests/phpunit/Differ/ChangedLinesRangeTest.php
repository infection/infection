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

namespace Infection\Tests\Differ;

use Infection\Differ\ChangedLinesRange;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(ChangedLinesRange::class)]
final class ChangedLinesRangeTest extends TestCase
{
    public function test_it_can_be_created_for_a_line(): void
    {
        $expected = ChangedLinesRange::create(3, 3);
        $actual = ChangedLinesRange::forLine(3);

        $this->assertEquals($expected, $actual);
    }

    public function test_it_can_be_created_for_a_range(): void
    {
        $expected = ChangedLinesRange::create(12, 18);
        $actual = ChangedLinesRange::forRange(12, 7);

        $this->assertEquals($expected, $actual);
    }

    public function test_it_can_be_created_with_an_end_line_lesser_than_a_start_line(): void
    {
        $this->expectException(InvalidArgumentException::class);

        ChangedLinesRange::create(12, 11);
    }

    /**
     * @param positive-int|0 $startLine
     * @param positive-int|0 $endLine
     */
    #[DataProvider('rangeProvider')]
    public function test_it_can_check_if_it_contains_the_given_range(
        ChangedLinesRange $range,
        int $startLine,
        int $endLine,
        bool $expected,
    ): void {
        $actual = $range->touches($startLine, $endLine);

        $this->assertSame($expected, $actual);
    }

    public static function rangeProvider(): iterable
    {
        yield 'the mutation touches some of the changed lines' => [
            ChangedLinesRange::create(10, 20),
            11,
            19,
            true,
        ];

        yield 'the mutation touches all the changed lines' => [
            ChangedLinesRange::create(10, 20),
            10,
            20,
            true,
        ];

        yield 'the mutation touches all changed lines and more' => [
            ChangedLinesRange::create(10, 20),
            11,
            21,
            true,
        ];

        yield 'the first line of the mutation touches the changed lines' => [
            ChangedLinesRange::forLine(11),
            11,
            19,
            true,
        ];

        yield 'the last line of the mutation touches the changed lines' => [
            ChangedLinesRange::forLine(19),
            11,
            19,
            true,
        ];

        yield 'the mutation touches the changed lines' => [
            ChangedLinesRange::forLine(15),
            11,
            19,
            true,
        ];

        yield 'the mutation touches some of the changed lines (before)' => [
            ChangedLinesRange::create(10, 20),
            9,
            18,
            true,
        ];

        yield 'the mutation touches some of the changed lines (after)' => [
            ChangedLinesRange::create(10, 20),
            12,
            21,
            true,
        ];

        yield 'the mutation does not affect any changed lines (before)' => [
            ChangedLinesRange::create(10, 20),
            7,
            9,
            false,
        ];

        yield 'the mutation does not affect any changed lines (after)' => [
            ChangedLinesRange::create(10, 20),
            21,
            23,
            false,
        ];

        yield 'invalid range given (start & end inversed) still contained' => [
            ChangedLinesRange::create(10, 20),
            18,
            12,
            true,
        ];
    }
}
