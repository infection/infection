<?php

namespace Infection\Tests\Telemetry\Metric\Time;

use Infection\Telemetry\Metric\Time\Duration;
use Infection\Telemetry\Metric\Time\DurationFormatter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(DurationFormatter::class)]
final class DurationFormatterTest extends TestCase
{
    #[DataProvider('durationProvider')]
    public function test_it_can_print_a_duration_in_a_human_readable_way(
        Duration $duration,
        string $expected,
    ): void
    {
        $formatter = new DurationFormatter();

        $actual = $formatter->toHumanReadableString($duration);

        self::assertSame($expected, $actual);
    }

    public static function durationProvider(): iterable
    {
        yield 'zero duration' => [
            Duration::fromSecondsAndNanoseconds(0, 0),
            '0ms',
        ];

        yield 'less than 1ms' => [
            Duration::fromSecondsAndNanoseconds(0, 500_000),
            '>1ms',
        ];

        yield 'exactly 1ms' => [
            Duration::fromSecondsAndNanoseconds(0, 1_000_000),
            '1ms',
        ];

        yield 'slightly over 1ms' => [
            Duration::fromSecondsAndNanoseconds(0, 1_100_000),
            '1ms',
        ];

        yield '1.5ms' => [
            Duration::fromSecondsAndNanoseconds(0, 1_500_000),
            '1.5ms',
        ];

        yield 'multiple milliseconds' => [
            Duration::fromSecondsAndNanoseconds(0, 128_000_000),
            '128ms',
        ];

        yield 'just under 1 second' => [
            Duration::fromSecondsAndNanoseconds(0, 999_000_000),
            '999ms',
        ];

        yield 'exactly 1 second' => [
            Duration::fromSecondsAndNanoseconds(1, 0),
            '1s',
        ];

        yield '1.5 seconds' => [
            Duration::fromSecondsAndNanoseconds(1, 500_000_000),
            '1.5s',
        ];

        yield 'multiple seconds' => [
            Duration::fromSecondsAndNanoseconds(45, 750_000_000),
            '45.8s',
        ];

        yield 'just under 1 minute' => [
            Duration::fromSecondsAndNanoseconds(59, 999_000_000),
            '60s',
        ];

        yield 'exactly 1 minute' => [
            Duration::fromSecondsAndNanoseconds(60, 0),
            '1min',
        ];

        yield '3.1 minutes' => [
            Duration::fromSecondsAndNanoseconds(186, 0),
            '3.1min',
        ];

        yield 'multiple minutes' => [
            Duration::fromSecondsAndNanoseconds(450, 500_000_000),
            '7.5min',
        ];

        yield 'large duration' => [
            Duration::fromSecondsAndNanoseconds(7200, 0),
            '120min',
        ];

        yield 'fractional nanoseconds in minutes' => [
            Duration::fromSecondsAndNanoseconds(125, 750_000_000),
            '2.1min',
        ];

        yield 'exactly 1.05ms boundary' => [
            Duration::fromSecondsAndNanoseconds(0, 1_050_000),
            '1ms',
        ];

        yield 'seconds with precision boundary' => [
            Duration::fromSecondsAndNanoseconds(59, 999_500_000),
            '60s',
        ];

        yield 'seconds rounding boundary down' => [
            Duration::fromSecondsAndNanoseconds(1, 400_000_000),
            '1.4s',
        ];

        yield 'seconds rounding boundary up' => [
            Duration::fromSecondsAndNanoseconds(1, 600_000_000),
            '1.6s',
        ];

        yield 'seconds that round down but ceil up' => [
            Duration::fromSecondsAndNanoseconds(2, 300_000_000),
            '2.3s',
        ];
    }
}
