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

namespace Infection\Tests\Resource\Time;

use Generator;
use Infection\Resource\Time\TimeFormatter;
use PHPUnit\Framework\TestCase;

final class TimeFormatterTest extends TestCase
{
    /**
     * @var TimeFormatter
     */
    private $timeFormatter;

    protected function setUp(): void
    {
        $this->timeFormatter = new TimeFormatter();
    }

    /**
     * @dataProvider timeProvider
     */
    public function test_it_converts_time_to_human_readable_time(float $seconds, string $expectedString): void
    {
        $timeString = $this->timeFormatter->toHumanReadableString($seconds);

        $this->assertSame($expectedString, $timeString);
    }

    public function timeProvider(): Generator
    {
        foreach (self::secondsProvider() as $i => $set) {
            yield 'seconds#' . $i => $set;
        }

        foreach (self::minutesProvider() as $i => $set) {
            yield 'minutes#' . $i => $set;
        }

        foreach (self::hoursProvider() as $i => $set) {
            yield 'hours#' . $i => $set;
        }
    }

    private static function secondsProvider(): Generator
    {
        yield [0, '0s'];

        yield [0.3, '0s'];

        yield [1, '1s'];

        yield [1.19, '1s'];

        yield [3, '3s'];

        yield [31, '31s'];

        yield [31.01, '31s'];
    }

    private static function minutesProvider(): Generator
    {
        yield [60, '1m'];

        yield [60.1, '1m'];

        yield [61, '1m 1s'];

        yield [122, '2m 2s'];

        yield [122.9, '2m 2s'];
    }

    private static function hoursProvider(): Generator
    {
        yield [3600, '1h'];

        yield [3600.99, '1h'];

        yield [3601, '1h 1s'];

        yield [7302, '2h 1m 42s'];

        yield [7302.88, '2h 1m 42s'];
    }
}
