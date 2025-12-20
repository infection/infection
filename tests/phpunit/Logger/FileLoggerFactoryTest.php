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
use Infection\Configuration\Entry\Logs;
use Infection\Configuration\Entry\StrykerConfig;
use Infection\Console\LogVerbosity;
use Infection\Logger\DebugFileLogger;
use Infection\Logger\FederatedLogger;
use Infection\Logger\FileLogger;
use Infection\Logger\FileLoggerFactory;
use Infection\Logger\GitHubActionsLogTextFileLogger;
use Infection\Logger\GitHubAnnotationsLogger;
use Infection\Logger\GitLabCodeQualityLogger;
use Infection\Logger\Html\HtmlFileLogger;
use Infection\Logger\Html\StrykerHtmlReportBuilder;
use Infection\Logger\JsonLogger;
use Infection\Logger\MutationTestingResultsLogger;
use Infection\Logger\PerMutatorLogger;
use Infection\Logger\SummaryFileLogger;
use Infection\Logger\SummaryJsonLogger;
use Infection\Logger\TextFileLogger;
use Infection\Metrics\MetricsCalculator;
use Infection\Metrics\ResultsCollector;
use Infection\Tests\Fixtures\Logger\FakeLogger;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\Filesystem\Filesystem;

#[Group('integration')]
#[CoversClass(FileLoggerFactory::class)]
final class FileLoggerFactoryTest extends TestCase
{
    private MetricsCalculator $metricsCalculator;

    private ResultsCollector $resultsCollector;

    private MockObject&Filesystem $fileSystemMock;

    protected function setUp(): void
    {
        $this->metricsCalculator = new MetricsCalculator(2);
        $this->resultsCollector = new ResultsCollector();

        $this->fileSystemMock = $this->createMock(Filesystem::class);
    }

    public function test_it_does_not_create_any_logger_for_no_verbosity_level_and_no_badge(): void
    {
        $factory = $this->createLoggerFactory(
            LogVerbosity::NONE,
            true,
            true,
        );

        $logger = $factory->createFromLogEntries(
            new Logs(
                '/a/file',
                '/a/file',
                '/a/file',
                '/a/file',
                '/a/file',
                '/a/file',
                '/a/file',
                true,
                null,
                '/a/file',
            ),
        );

        $this->assertRegisteredLoggersAre([], $logger);
    }

    #[DataProvider('logsProvider')]
    public function test_it_creates_a_logger_for_log_type_on_normal_verbosity(
        Logs $logs,
        array $expectedLoggerClasses,
    ): void {
        $factory = $this->createLoggerFactory(
            LogVerbosity::NORMAL,
            true,
            true,
        );

        $logger = $factory->createFromLogEntries($logs);

        $this->assertRegisteredLoggersAre($expectedLoggerClasses, $logger);
    }

    public static function logsProvider(): iterable
    {
        yield 'no logger' => [
            Logs::createEmpty(),
            [],
        ];

        yield 'text logger' => [
            new Logs(
                'text',
                null,
                null,
                null,
                null,
                null,
                null,
                false,
                null,
                null,
            ),
            [TextFileLogger::class],
        ];

        yield 'text logger outside of github actions' => [
            new Logs(
                'php://stdout',
                null,
                null,
                null,
                null,
                null,
                null,
                false,
                null,
                null,
            ),
            [TextFileLogger::class],
        ];

        yield 'text logger in github actions' => [
            new Logs(
                'php://stdout',
                null,
                null,
                null,
                null,
                null,
                null,
                true,
                null,
                null,
            ),
            [GitHubActionsLogTextFileLogger::class, GitHubAnnotationsLogger::class],
        ];

        yield 'html logger' => [
            new Logs(
                null,
                'html',
                null,
                null,
                null,
                null,
                null,
                false,
                null,
                null,
            ),
            [HtmlFileLogger::class],
        ];

        yield 'summary logger' => [
            new Logs(
                null,
                null,
                'summary_file',
                null,
                null,
                null,
                null,
                false,
                null,
                null,
            ),
            [SummaryFileLogger::class],
        ];

        yield 'debug logger' => [
            new Logs(
                null,
                null,
                null,
                null,
                null,
                'debug_file',
                null,
                false,
                null,
                null,
            ),
            [DebugFileLogger::class],
        ];

        yield 'json logger' => [
            new Logs(
                null,
                null,
                null,
                'json_file',
                null,
                null,
                null,
                false,
                null,
                null,
            ),
            [JsonLogger::class],
        ];

        yield 'GitLab logger' => [
            new Logs(
                null,
                null,
                null,
                null,
                'gitlab.log',
                null,
                null,
                false,
                null,
                null,
            ),
            [GitLabCodeQualityLogger::class],
        ];

        yield 'per mutator logger' => [
            new Logs(
                null,
                null,
                null,
                null,
                null,
                null,
                'per_muator',
                false,
                null,
                null,
            ),
            [PerMutatorLogger::class],
        ];

        yield 'github logger' => [
            new Logs(
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                true,
                null,
                null,
            ),
            [GitHubAnnotationsLogger::class],
        ];

        yield 'summary-json logger' => [
            new Logs(
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                false,
                null,
                'summary-json',
            ),
            [SummaryJsonLogger::class],
        ];

        yield 'all loggers' => [
            new Logs(
                'text',
                'html',
                'summary',
                'json',
                'gitlab',
                'debug',
                'per_mutator',
                true,
                StrykerConfig::forBadge('branch'),
                'summary-json',
            ),
            [
                TextFileLogger::class,
                HtmlFileLogger::class,
                SummaryFileLogger::class,
                JsonLogger::class,
                GitLabCodeQualityLogger::class,
                DebugFileLogger::class,
                PerMutatorLogger::class,
                SummaryJsonLogger::class,
                GitHubAnnotationsLogger::class,
            ],
        ];
    }

    private function createLoggerFactory(
        string $logVerbosity,
        bool $debugMode,
        bool $onlyCoveredCode,
    ): FileLoggerFactory {
        return new FileLoggerFactory(
            $this->metricsCalculator,
            $this->resultsCollector,
            $this->fileSystemMock,
            $logVerbosity,
            $debugMode,
            $onlyCoveredCode,
            new FakeLogger(),
            new StrykerHtmlReportBuilder($this->metricsCalculator, $this->resultsCollector),
            null,
            20,
        );
    }

    private function assertRegisteredLoggersAre(
        array $expectedLoggerClasses,
        MutationTestingResultsLogger $logger,
    ): void {
        $this->assertInstanceOf(FederatedLogger::class, $logger);

        $loggersReflection = (new ReflectionClass(FederatedLogger::class))->getProperty('loggers');
        $loggers = $loggersReflection->getValue($logger);

        $fileLoggerDecoratedLogger = (new ReflectionClass(FileLogger::class))->getProperty('lineLogger');

        $actualLoggerClasses = array_map(
            static function ($logger) use ($fileLoggerDecoratedLogger): string {
                if ($logger instanceof FileLogger) {
                    $logger = $fileLoggerDecoratedLogger->getValue($logger);
                }

                return $logger::class;
            },
            $loggers,
        );

        $this->assertSame($expectedLoggerClasses, $actualLoggerClasses);
    }
}
