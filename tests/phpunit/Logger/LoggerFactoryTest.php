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

namespace Infection\Tests\Logger;

use Generator;
use Infection\Configuration\Entry\Badge;
use Infection\Configuration\Entry\Logs;
use Infection\Console\LogVerbosity;
use Infection\Logger\BadgeLogger;
use Infection\Logger\DebugFileLogger;
use Infection\Logger\LoggerFactory;
use Infection\Logger\PerMutatorLogger;
use Infection\Logger\SummaryFileLogger;
use Infection\Logger\TextFileLogger;
use Infection\Mutant\MetricsCalculator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

final class LoggerFactoryTest extends TestCase
{
    public function test_it_does_not_create_any_logger_for_no_verbosity_level(): void
    {
        $factory = new LoggerFactory(
            new MetricsCalculator(),
            new Filesystem(),
            LogVerbosity::NONE,
            true,
            true
        );

        $loggers = $factory->createFromLogEntries(
            new Logs(
                '/a/file',
                '/a/file',
                '/a/file',
                '/a/file',
                null
            ),
            $this->createMock(OutputInterface::class)
        );

        $this->assertCount(0, $loggers);
    }

    public function test_it_creates_a_bade_logger_on_low_verbosity(): void
    {
        $factory = new LoggerFactory(
            new MetricsCalculator(),
            new Filesystem(),
            LogVerbosity::NONE,
            true,
            true
        );

        $loggers = $factory->createFromLogEntries(
            new Logs(null, null, null, null, new Badge('branch_name')),
            $this->createMock(OutputInterface::class)
        );

        $this->assertCount(1, $loggers);
        $this->assertInstanceOf(BadgeLogger::class, current($loggers));
    }

    /**
     * @dataProvider provideLogTypesAndClasses
     */
    public function test_it_creates_a_logger_for_log_type(Logs $logs, string $expectedLoggerClass): void
    {
        $factory = new LoggerFactory(
            new MetricsCalculator(),
            new Filesystem(),
            LogVerbosity::NORMAL,
            true,
            true
        );

        $loggers = $factory->createFromLogEntries(
            $logs,
            $this->createMock(OutputInterface::class)
        );

        $this->assertCount(1, $loggers);
        $this->assertInstanceOf($expectedLoggerClass, current($loggers));
    }

    /**
     * @dataProvider provideLogsAndCount
     */
    public function test_it_creates_multiple_loggers(Logs $logs, int $expectedLoggerCount): void
    {
        $factory = new LoggerFactory(
            new MetricsCalculator(),
            new Filesystem(),
            LogVerbosity::NORMAL,
            true,
            true
        );

        $loggers = $factory->createFromLogEntries(
            $logs,
            $this->createMock(OutputInterface::class)
        );

        $this->assertCount($expectedLoggerCount, $loggers);
    }

    public function provideLogTypesAndClasses(): Generator
    {
        yield 'text logger' => [
            new Logs(
                'text',
                null,
                null,
                null,
                null
            ),
            TextFileLogger::class,
        ];

        yield 'summary logger' => [
            new Logs(
                null,
                'summary_file',
                null,
                null,
                null
            ),
            SummaryFileLogger::class,
        ];

        yield 'debug logger' => [
            new Logs(
                null,
                null,
                'debug_file',
                null,
                null
            ),
            DebugFileLogger::class,
        ];

        yield 'per mutator logger' => [
            new Logs(
                null,
                null,
                null,
                'per_muator',
                null
            ),
            PerMutatorLogger::class,
        ];

        yield 'badge logger' => [
            new Logs(
                null,
                null,
                null,
                null,
                new Badge('foo')
            ),
            BadgeLogger::class,
        ];
    }

    public function provideLogsAndCount(): Generator
    {
        yield 'no logger' => [
            new Logs(
                null,
                null,
                null,
                null,
                null
            ),
            0,
        ];

        yield 'nominal' => [
            new Logs(
                'text',
                'summary',
                'debug',
                'per_mutator',
                new Badge('branch')
            ),
            5,
        ];
    }
}
