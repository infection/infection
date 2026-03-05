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
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Duration::class)]
final class DurationTest extends TestCase
{
    #[DataProvider('validDurationProvider')]
    public function test_it_can_be_created_with_valid_values(
        int $seconds,
        int $nanoseconds,
    ): void {
        $duration = Duration::fromSecondsAndNanoseconds($seconds, $nanoseconds);

        $this->assertSame($seconds, $duration->seconds);
        $this->assertSame($nanoseconds, $duration->nanoseconds);
    }

    #[DataProvider('invalidDurationProvider')]
    public function test_it_rejects_invalid_values(
        int $seconds,
        int $nanoseconds,
        InvalidArgumentException $expectedException,
    ): void {
        $this->expectExceptionObject($expectedException);

        Duration::fromSecondsAndNanoseconds($seconds, $nanoseconds);
    }

    #[DataProvider('toSecondsProvider')]
    public function test_it_can_convert_to_seconds(
        Duration $duration,
        float $expectedSeconds,
    ): void {
        $actualSeconds = $duration->toSeconds();

        $this->assertSame($expectedSeconds, $actualSeconds);
    }

    #[DataProvider('percentageProvider')]
    public function test_it_can_calculate_percentage_of_total_duration(
        Duration $duration,
        Duration $total,
        int $expectedPercentage,
    ): void {
        $actualPercentage = $duration->getPercentage($total);

        $this->assertSame($expectedPercentage, $actualPercentage);
    }

    public static function validDurationProvider(): iterable
    {
        yield 'zero duration' => [
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

        yield 'one millisecond' => [
            0,
            1_000_000,
        ];

        yield 'one microsecond' => [
            0,
            1_000,
        ];
    }

    public static function invalidDurationProvider(): iterable
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

        yield 'nanoseconds equal to 10 billion' => [
            0,
            10_000_000_000,
            new InvalidArgumentException('Value for nanoseconds must not be greater or equal than 10000000000.'),
        ];

        yield 'nanoseconds greater than 10 billion' => [
            0,
            15_000_000_000,
            new InvalidArgumentException('Value for nanoseconds must not be greater or equal than 10000000000.'),
        ];
    }

    public static function toSecondsProvider(): iterable
    {
        yield 'zero duration' => [
            Duration::fromSecondsAndNanoseconds(0, 0),
            0.0,
        ];

        yield 'one second' => [
            Duration::fromSecondsAndNanoseconds(1, 0),
            1.0,
        ];

        yield 'half second' => [
            Duration::fromSecondsAndNanoseconds(0, 500_000_000),
            0.5,
        ];

        yield 'one and a half seconds' => [
            Duration::fromSecondsAndNanoseconds(1, 500_000_000),
            1.5,
        ];

        yield 'one millisecond' => [
            Duration::fromSecondsAndNanoseconds(0, 1_000_000),
            0.001,
        ];

        yield 'one microsecond' => [
            Duration::fromSecondsAndNanoseconds(0, 1_000),
            0.000001,
        ];

        yield 'one nanosecond' => [
            Duration::fromSecondsAndNanoseconds(0, 1),
            0.000000001,
        ];

        yield 'multiple seconds with nanoseconds' => [
            Duration::fromSecondsAndNanoseconds(45, 750_000_000),
            45.75,
        ];

        yield 'large duration' => [
            Duration::fromSecondsAndNanoseconds(3600, 0),
            3600.0,
        ];

        yield 'max nanoseconds' => [
            Duration::fromSecondsAndNanoseconds(0, 999_999_999),
            0.999999999,
        ];
    }

    public static function percentageProvider(): iterable
    {
        yield 'zero of zero is zero percent' => [
            Duration::fromSecondsAndNanoseconds(0, 0),
            Duration::fromSecondsAndNanoseconds(0, 0),
            0,
        ];

        yield 'any value of zero total is zero percent' => [
            Duration::fromSecondsAndNanoseconds(10, 0),
            Duration::fromSecondsAndNanoseconds(0, 0),
            0,
        ];

        yield 'half of total is 50 percent' => [
            Duration::fromSecondsAndNanoseconds(5, 0),
            Duration::fromSecondsAndNanoseconds(10, 0),
            50,
        ];

        yield 'equal to total is 100 percent' => [
            Duration::fromSecondsAndNanoseconds(10, 0),
            Duration::fromSecondsAndNanoseconds(10, 0),
            100,
        ];

        yield 'quarter of total is 25 percent' => [
            Duration::fromSecondsAndNanoseconds(1, 0),
            Duration::fromSecondsAndNanoseconds(4, 0),
            25,
        ];

        yield 'three quarters of total is 75 percent' => [
            Duration::fromSecondsAndNanoseconds(3, 0),
            Duration::fromSecondsAndNanoseconds(4, 0),
            75,
        ];

        yield 'one percent of total' => [
            Duration::fromSecondsAndNanoseconds(1, 0),
            Duration::fromSecondsAndNanoseconds(100, 0),
            1,
        ];

        yield 'percentage rounds down at 0.4' => [
            Duration::fromSecondsAndNanoseconds(1, 400_000_000),
            Duration::fromSecondsAndNanoseconds(10, 0),
            14,
        ];

        yield 'percentage rounds up at 0.5' => [
            Duration::fromSecondsAndNanoseconds(1, 500_000_000),
            Duration::fromSecondsAndNanoseconds(10, 0),
            15,
        ];

        yield 'percentage rounds up at 0.6' => [
            Duration::fromSecondsAndNanoseconds(1, 600_000_000),
            Duration::fromSecondsAndNanoseconds(10, 0),
            16,
        ];

        yield 'exceeding total is capped at 100 percent' => [
            Duration::fromSecondsAndNanoseconds(200, 0),
            Duration::fromSecondsAndNanoseconds(100, 0),
            100,
        ];

        yield 'very small percentage rounds to 0' => [
            Duration::fromSecondsAndNanoseconds(0, 1_000_000),
            Duration::fromSecondsAndNanoseconds(1000, 0),
            0,
        ];

        yield 'percentage with nanoseconds' => [
            Duration::fromSecondsAndNanoseconds(2, 500_000_000),
            Duration::fromSecondsAndNanoseconds(5, 0),
            50,
        ];

        yield '33.33% rounds to 33' => [
            Duration::fromSecondsAndNanoseconds(1, 0),
            Duration::fromSecondsAndNanoseconds(3, 0),
            33,
        ];

        yield '66.66% rounds to 67' => [
            Duration::fromSecondsAndNanoseconds(2, 0),
            Duration::fromSecondsAndNanoseconds(3, 0),
            67,
        ];

        yield 'exactly 0.5% rounds to 1' => [
            Duration::fromSecondsAndNanoseconds(1, 0),
            Duration::fromSecondsAndNanoseconds(200, 0),
            1,
        ];
    }
}
