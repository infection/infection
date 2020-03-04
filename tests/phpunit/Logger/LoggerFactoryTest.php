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

use function array_map;
use Generator;
use function get_class;
use Infection\Configuration\Entry\Badge;
use Infection\Configuration\Entry\Logs;
use Infection\Console\LogVerbosity;
use Infection\Logger\BadgeLogger;
use Infection\Logger\DebugFileLogger;
use Infection\Logger\FileLogger;
use Infection\Logger\LoggerFactory;
use Infection\Logger\LoggerRegistry;
use Infection\Logger\MutationTestingResultsLogger;
use Infection\Logger\PerMutatorLogger;
use Infection\Logger\SarbLogger;
use Infection\Logger\SummaryFileLogger;
use Infection\Logger\TextFileLogger;
use Infection\Mutant\MetricsCalculator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @group integration
 */
final class LoggerFactoryTest extends TestCase
{
    /**
     * @var MetricsCalculator
     */
    private $metricsCalculator;

    /**
     * @var Filesystem|MockObject
     */
    private $fileSystemMock;

    /**
     * @var OutputInterface|MockObject
     */
    private $outputMock;

    protected function setUp(): void
    {
        $this->metricsCalculator = new MetricsCalculator();
        $this->fileSystemMock = $this->createMock(Filesystem::class);
        $this->outputMock = $this->createMock(OutputInterface::class);
    }

    public function test_it_does_not_create_any_logger_for_no_verbosity_level_and_no_badge(): void
    {
        $factory = $this->createLoggerFactory(
            LogVerbosity::NONE,
            true,
            true
        );

        $logger = $factory->createFromLogEntries(
            new Logs(
                '/a/file',
                '/a/file',
                '/a/file',
                '/a/file',
                '/a/file',
                null
            ),
            $this->outputMock
        );

        $this->assertRegisteredLoggersAre([], $logger);
    }

    public function test_it_creates_a_bade_logger_on_no_verbosity(): void
    {
        $factory = $this->createLoggerFactory(
            LogVerbosity::NONE,
            true,
            true
        );

        $logger = $factory->createFromLogEntries(
            new Logs(
                null,
                null,
                null,
                null,
                null,
                new Badge('master')
            ),
            $this->outputMock
        );

        $this->assertRegisteredLoggersAre([BadgeLogger::class], $logger);
    }

    /**
     * @dataProvider logsProvider
     */
    public function test_it_creates_a_logger_for_log_type_on_normal_verbosity(
        Logs $logs,
        array $expectedLoggerClasses
    ): void {
        $factory = $this->createLoggerFactory(
            LogVerbosity::NORMAL,
            true,
            true
        );

        $logger = $factory->createFromLogEntries(
            $logs,
            $this->outputMock
        );

        $this->assertRegisteredLoggersAre($expectedLoggerClasses, $logger);
    }

    public function logsProvider(): Generator
    {
        yield 'no logger' => [
            new Logs(
                null,
                null,
                null,
                null,
                null,
                null
            ),
            [],
        ];

        yield 'text logger' => [
            new Logs(
                'text',
                null,
                null,
                null,
                null,
                null
            ),
            [TextFileLogger::class],
        ];

        yield 'summary logger' => [
            new Logs(
                null,
                'summary_file',
                null,
                null,
                null,
                null
            ),
            [SummaryFileLogger::class],
        ];

        yield 'debug logger' => [
            new Logs(
                null,
                null,
                'debug_file',
                null,
                null,
                null
            ),
            [DebugFileLogger::class],
        ];

        yield 'per mutator logger' => [
            new Logs(
                null,
                null,
                null,
                'per_muator',
                null,
                null
            ),
            [PerMutatorLogger::class],
        ];

        yield 'sarb logger' => [
            new Logs(
                null,
                null,
                null,
                null,
                'sarb.json',
                null
            ),
            [SarbLogger::class],
        ];

        yield 'badge logger' => [
            new Logs(
                null,
                null,
                null,
                null,
                null,
                new Badge('foo')
            ),
            [BadgeLogger::class],
        ];

        yield 'all loggers' => [
            new Logs(
                'text',
                'summary',
                'debug',
                'per_mutator',
                'sarb.json',
                new Badge('branch')
            ),
            [
                TextFileLogger::class,
                SummaryFileLogger::class,
                DebugFileLogger::class,
                PerMutatorLogger::class,
                SarbLogger::class,
                BadgeLogger::class,
            ],
        ];
    }

    private function createLoggerFactory(
        string $logVerbosity,
        bool $debugMode,
        bool $onlyCoveredCode
    ): LoggerFactory {
        return new LoggerFactory(
            $this->metricsCalculator,
            $this->fileSystemMock,
            $logVerbosity,
            $debugMode,
            $onlyCoveredCode
        );
    }

    private function assertRegisteredLoggersAre(
        array $expectedLoggerClasses,
        MutationTestingResultsLogger $logger
    ): void {
        $this->assertInstanceOf(LoggerRegistry::class, $logger);

        $loggersReflection = (new ReflectionClass(LoggerRegistry::class))->getProperty('loggers');
        $loggersReflection->setAccessible(true);

        $loggers = $loggersReflection->getValue($logger);

        $fileLoggerDecoratedLogger = (new ReflectionClass(FileLogger::class))->getProperty('lineLogger');
        $fileLoggerDecoratedLogger->setAccessible(true);

        $actualLoggerClasses = array_map(
            static function ($logger) use ($fileLoggerDecoratedLogger): string {
                if ($logger instanceof FileLogger) {
                    $logger = $fileLoggerDecoratedLogger->getValue($logger);
                }

                return get_class($logger);
            },
            $loggers
        );

        $this->assertSame($expectedLoggerClasses, $actualLoggerClasses);
    }
}
