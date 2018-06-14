<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Performance\Time;

use Infection\Performance\Time\TimeFormatter;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class TimeFormatterTest extends TestCase
{
    /**
     * @var TimeFormatter
     */
    private $timeFormatter;

    protected function setUp()
    {
        $this->timeFormatter = new TimeFormatter();
    }

    /**
     * @dataProvider secondsProvider
     *
     * @param float $seconds
     * @param string $expectedString
     */
    public function test_it_converts_seconds_to_seconds_string(float $seconds, string $expectedString)
    {
        $timeString = $this->timeFormatter->toHumanReadableString($seconds);

        $this->assertSame($expectedString, $timeString);
    }

    public function secondsProvider()
    {
        yield [0, '0s'];
        yield [0.3, '0s'];
        yield [1, '1s'];
        yield [1.19, '1s'];
        yield [3, '3s'];
        yield [31, '31s'];
        yield [31.01, '31s'];
    }

    /**
     * @dataProvider minutesProvider
     *
     * @param float $seconds
     * @param string $expectedString
     */
    public function test_it_converts_seconds_to_minutes_string(float $seconds, string $expectedString)
    {
        $timeString = $this->timeFormatter->toHumanReadableString($seconds);

        $this->assertSame($expectedString, $timeString);
    }

    public function minutesProvider()
    {
        yield [60, '1m'];
        yield [60.1, '1m'];
        yield [61, '1m 1s'];
        yield [122, '2m 2s'];
        yield [122.9, '2m 2s'];
    }

    /**
     * @dataProvider minutesProvider
     *
     * @param float $seconds
     * @param string $expectedString
     */
    public function test_it_converts_seconds_to_hours_string(float $seconds, string $expectedString)
    {
        $timeString = $this->timeFormatter->toHumanReadableString($seconds);

        $this->assertSame($expectedString, $timeString);
    }

    public function hoursProvider()
    {
        yield [3600, '1h'];
        yield [3600.99, '1h'];
        yield [3601, '1h 0m 1s'];
        yield [7302, '2h 1m 42s'];
        yield [7302.88, '2h 1m 42s'];
    }
}
