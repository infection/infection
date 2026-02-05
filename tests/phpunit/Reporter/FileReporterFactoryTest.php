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

namespace Infection\Tests\Reporter;

use function array_map;
use Infection\Configuration\Entry\Logs;
use Infection\Configuration\Entry\StrykerConfig;
use Infection\Console\LogVerbosity;
use Infection\Metrics\MetricsCalculator;
use Infection\Metrics\ResultsCollector;
use Infection\Reporter\DebugFileReporter;
use Infection\Reporter\FederatedReporter;
use Infection\Reporter\FileReporter;
use Infection\Reporter\FileReporterFactory;
use Infection\Reporter\GitHubActionsLogTextFileReporter;
use Infection\Reporter\GitHubAnnotationsReporter;
use Infection\Reporter\GitLabCodeQualityReporter;
use Infection\Reporter\Html\HtmlFileReporter;
use Infection\Reporter\Html\StrykerHtmlReportBuilder;
use Infection\Reporter\JsonReporter;
use Infection\Reporter\PerMutatorReporter;
use Infection\Reporter\Reporter;
use Infection\Reporter\SummaryFileReporter;
use Infection\Reporter\SummaryJsonReporter;
use Infection\Reporter\TextFileReporter;
use Infection\Tests\Fixtures\Logger\FakeLogger;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\Filesystem\Filesystem;

#[Group('integration')]
#[CoversClass(FileReporterFactory::class)]
final class FileReporterFactoryTest extends TestCase
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

    public function test_it_does_not_create_any_reporter_for_no_verbosity_level_and_no_badge(): void
    {
        $factory = $this->createReporterFactory(
            LogVerbosity::NONE,
            true,
            true,
        );

        $reporter = $factory->createFromConfiguration(
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

        $this->assertRegisteredReportersAre([], $reporter);
    }

    #[DataProvider('configProvider')]
    public function test_it_creates_a_reporter_for_log_type_on_normal_verbosity(
        Logs $logs,
        array $expectedReporterClassNames,
    ): void {
        $factory = $this->createReporterFactory(
            LogVerbosity::NORMAL,
            true,
            true,
        );

        $reporter = $factory->createFromConfiguration($logs);

        $this->assertRegisteredReportersAre($expectedReporterClassNames, $reporter);
    }

    public static function configProvider(): iterable
    {
        yield 'no reporter' => [
            Logs::createEmpty(),
            [],
        ];

        yield 'text reporter' => [
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
            [TextFileReporter::class],
        ];

        yield 'text reporter outside of github actions' => [
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
            [TextFileReporter::class],
        ];

        yield 'text reporter in github actions' => [
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
            [GitHubActionsLogTextFileReporter::class, GitHubAnnotationsReporter::class],
        ];

        yield 'html reporter' => [
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
            [HtmlFileReporter::class],
        ];

        yield 'summary reporter' => [
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
            [SummaryFileReporter::class],
        ];

        yield 'debug reporter' => [
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
            [DebugFileReporter::class],
        ];

        yield 'json reporter' => [
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
            [JsonReporter::class],
        ];

        yield 'GitLab reporter' => [
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
            [GitLabCodeQualityReporter::class],
        ];

        yield 'per mutator reporter' => [
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
            [PerMutatorReporter::class],
        ];

        yield 'github reporter' => [
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
            [GitHubAnnotationsReporter::class],
        ];

        yield 'summary-json reporter' => [
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
            [SummaryJsonReporter::class],
        ];

        yield 'all reporters' => [
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
                TextFileReporter::class,
                HtmlFileReporter::class,
                SummaryFileReporter::class,
                JsonReporter::class,
                GitLabCodeQualityReporter::class,
                DebugFileReporter::class,
                PerMutatorReporter::class,
                SummaryJsonReporter::class,
                GitHubAnnotationsReporter::class,
            ],
        ];
    }

    private function createReporterFactory(
        string $logVerbosity,
        bool $debugMode,
        bool $onlyCoveredCode,
    ): FileReporterFactory {
        return new FileReporterFactory(
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

    private function assertRegisteredReportersAre(
        array $expectedReporterClassNames,
        Reporter $reporter,
    ): void {
        $this->assertInstanceOf(FederatedReporter::class, $reporter);

        $reportersReflection = (new ReflectionClass(FederatedReporter::class))->getProperty('reporters');
        $reporters = $reportersReflection->getValue($reporter);

        $fileReporterDecoratedReporter = (new ReflectionClass(FileReporter::class))->getProperty('lineReporter');

        $actualReporterClassNames = array_map(
            static function ($reporter) use ($fileReporterDecoratedReporter): string {
                if ($reporter instanceof FileReporter) {
                    $reporter = $fileReporterDecoratedReporter->getValue($reporter);
                }

                return $reporter::class;
            },
            $reporters,
        );

        $this->assertSame($expectedReporterClassNames, $actualReporterClassNames);
    }
}
