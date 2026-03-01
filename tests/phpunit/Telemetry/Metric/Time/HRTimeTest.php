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

namespace Infection\Tests\Telemetry\Metric\Time;

use Infection\Telemetry\Metric\Time\Duration;
use Infection\Telemetry\Metric\Time\HRTime;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(HRTime::class)]
final class HRTimeTest extends TestCase
{
    #[DataProvider('validTimeProvider')]
    public function test_it_can_be_created_with_valid_values(
        int $seconds,
        int $nanoseconds,
    ): void {
        $hrTime = HRTime::fromSecondsAndNanoseconds($seconds, $nanoseconds);

        $this->assertSame($seconds, $hrTime->seconds);
        $this->assertSame($nanoseconds, $hrTime->nanoseconds);
    }

    #[DataProvider('invalidTimeProvider')]
    public function test_it_rejects_invalid_values(
        int $seconds,
        int $nanoseconds,
        InvalidArgumentException $expectedException,
    ): void {
        $this->expectExceptionObject($expectedException);

        HRTime::fromSecondsAndNanoseconds($seconds, $nanoseconds);
    }

    #[DataProvider('durationProvider')]
    public function test_it_can_calculate_duration_between_two_time_points(
        HRTime $start,
        HRTime $end,
        Duration $expectedDuration,
    ): void {
        $actualDuration = $end->getDuration($start);

        $this->assertEquals($expectedDuration, $actualDuration);
    }

    public static function validTimeProvider(): iterable
    {
        yield 'zero time' => [
            0,
            0,
        ];

        yield 'positive seconds only' => [
            42,
            0,
        ];

        yield 'positive nanoseconds only' => [
            0,
            123_456_789,
        ];

        yield 'both positive' => [
            10,
            500_000_000,
        ];

        yield 'max valid nanoseconds' => [
            5,
            999_999_999,
        ];

        yield 'large seconds value' => [
            1_000_000,
            0,
        ];
    }

    public static function invalidTimeProvider(): iterable
    {
        yield 'negative seconds' => [
            -1,
            0,
            new InvalidArgumentException('Value for seconds must not be negative.'),
        ];

        yield 'negative nanoseconds' => [
            0,
            -1,
            new InvalidArgumentException('Value for nanoseconds must not be negative.'),
        ];

        yield 'both negative' => [
            -5,
            -100,
            new InvalidArgumentException('Value for seconds must not be negative.'),
        ];

        yield 'nanoseconds equal to 1 billion' => [
            0,
            1_000_000_000,
            new InvalidArgumentException('Value for nanoseconds must not be greater or equal than 1000000000.'),
        ];

        yield 'nanoseconds greater than 1 billion' => [
            0,
            1_500_000_000,
            new InvalidArgumentException('Value for nanoseconds must not be greater or equal than 1000000000.'),
        ];
    }

    public static function durationProvider(): iterable
    {
        yield 'same time point' => [
            HRTime::fromSecondsAndNanoseconds(10, 100_000_000),
            HRTime::fromSecondsAndNanoseconds(10, 100_000_000),
            Duration::fromSecondsAndNanoseconds(0, 0),
        ];

        yield 'simple difference without nanosecond borrowing' => [
            HRTime::fromSecondsAndNanoseconds(5, 100_000_000),
            HRTime::fromSecondsAndNanoseconds(10, 300_000_000),
            Duration::fromSecondsAndNanoseconds(5, 200_000_000),
        ];

        yield 'difference with nanosecond borrowing' => [
            HRTime::fromSecondsAndNanoseconds(5, 700_000_000),
            HRTime::fromSecondsAndNanoseconds(10, 300_000_000),
            Duration::fromSecondsAndNanoseconds(4, 600_000_000),
        ];

        yield 'start at zero' => [
            HRTime::fromSecondsAndNanoseconds(0, 0),
            HRTime::fromSecondsAndNanoseconds(3, 500_000_000),
            Duration::fromSecondsAndNanoseconds(3, 500_000_000),
        ];

        yield 'one second difference' => [
            HRTime::fromSecondsAndNanoseconds(10, 0),
            HRTime::fromSecondsAndNanoseconds(11, 0),
            Duration::fromSecondsAndNanoseconds(1, 0),
        ];

        yield 'one nanosecond difference' => [
            HRTime::fromSecondsAndNanoseconds(0, 0),
            HRTime::fromSecondsAndNanoseconds(0, 1),
            Duration::fromSecondsAndNanoseconds(0, 1),
        ];

        yield 'end before start returns zero duration' => [
            HRTime::fromSecondsAndNanoseconds(10, 500_000_000),
            HRTime::fromSecondsAndNanoseconds(5, 300_000_000),
            Duration::fromSecondsAndNanoseconds(0, 0),
        ];

        yield 'end same seconds but fewer nanoseconds returns zero duration' => [
            HRTime::fromSecondsAndNanoseconds(10, 500_000_000),
            HRTime::fromSecondsAndNanoseconds(10, 300_000_000),
            Duration::fromSecondsAndNanoseconds(0, 0),
        ];

        yield 'maximum nanosecond values' => [
            HRTime::fromSecondsAndNanoseconds(0, 0),
            HRTime::fromSecondsAndNanoseconds(0, 999_999_999),
            Duration::fromSecondsAndNanoseconds(0, 999_999_999),
        ];

        yield 'nanosecond borrowing at boundary' => [
            HRTime::fromSecondsAndNanoseconds(5, 1),
            HRTime::fromSecondsAndNanoseconds(10, 0),
            Duration::fromSecondsAndNanoseconds(4, 999_999_999),
        ];

        yield 'large time difference' => [
            HRTime::fromSecondsAndNanoseconds(100, 0),
            HRTime::fromSecondsAndNanoseconds(1_000, 0),
            Duration::fromSecondsAndNanoseconds(900, 0),
        ];
    }
}
