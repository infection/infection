<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Console;

use Infection\Console\LogVerbosity;
use PHPUnit\Framework\TestCase;

class LogVerbosityTest extends TestCase
{
    /**
     * @dataProvider providesLogVerbosity
     *
     * @param string|int $input
     * @param string $output
     */
    public function test_covert_log_verbosity($input, string $output)
    {
        $this->assertSame($output, LogVerbosity::convertVerbosityLevel($input));
    }

    public function providesLogVerbosity(): \Generator
    {
        yield 'It keeps the same input if it is correct' => [
            LogVerbosity::NORMAL,
            LogVerbosity::NORMAL,
        ];

        yield 'It converts integer versions to its correct version' => [
            LogVerbosity::NONE_INTEGER,
            LogVerbosity::NONE,
        ];

        yield 'It converts integer versions to its correct version, even if it is a string' => [
            (string) LogVerbosity::DEBUG_INTEGER,
            LogVerbosity::DEBUG,
        ];
    }

    public function test_conversion_throws_an_exception_if_level_is_not_found()
    {
        $this->expectException(\Exception::class);
        LogVerbosity::convertVerbosityLevel(5);
    }
}
