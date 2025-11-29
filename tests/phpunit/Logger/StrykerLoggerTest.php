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

use Infection\Configuration\Entry\StrykerConfig;
use Infection\Environment\BuildContextResolver;
use Infection\Environment\StrykerApiKeyResolver;
use Infection\Logger\Html\StrykerHtmlReportBuilder;
use Infection\Logger\Http\StrykerDashboardClient;
use Infection\Logger\StrykerLogger;
use Infection\Metrics\MetricsCalculator;
use Infection\Metrics\ResultsCollector;
use Infection\Tests\CI\ConfigurableEnv;
use Infection\Tests\EnvVariableManipulation\BacksUpEnvironmentVariables;
use OndraM\CiDetector\CiDetector;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use function Safe\putenv;

#[CoversClass(StrykerLogger::class)]
final class StrykerLoggerTest extends TestCase
{
    use BacksUpEnvironmentVariables;

    private MockObject&StrykerDashboardClient $strykerDashboardClient;

    private MockObject&MetricsCalculator $metricsCalculatorMock;

    private ConfigurableEnv $ciDetectorEnv;

    private DummyLogger $logger;

    private StrykerLogger $strykerLogger;

    protected function setUp(): void
    {
        $this->backupEnvironmentVariables();

        $this->strykerDashboardClient = $this->createMock(StrykerDashboardClient::class);
        $this->metricsCalculatorMock = $this->createMock(MetricsCalculator::class);
        $this->ciDetectorEnv = new ConfigurableEnv();
        $this->logger = new DummyLogger();

        $this->strykerLogger = new StrykerLogger(
            new BuildContextResolver(CiDetector::fromEnvironment($this->ciDetectorEnv)),
            new StrykerApiKeyResolver(),
            $this->strykerDashboardClient,
            $this->metricsCalculatorMock,
            new StrykerHtmlReportBuilder($this->metricsCalculatorMock, new ResultsCollector()),
            StrykerConfig::forBadge('master'),
            $this->logger,
        );
    }

    protected function tearDown(): void
    {
        $this->restoreEnvironmentVariables();
    }

    public function test_it_skips_logging_when_it_is_not_travis(): void
    {
        $this->ciDetectorEnv->setVariables([
            'TRAVIS' => false,
        ]);

        $this->strykerDashboardClient
            ->expects($this->never())
            ->method('sendReport')
        ;

        $this->strykerLogger->log();

        $this->assertSame(
            [
                [
                    LogLevel::WARNING,
                    'Dashboard report has not been sent: The current process is not executed in a CI build',
                    [],
                ],
            ],
            $this->logger->getLogs(),
        );
    }

    public function test_it_skips_logging_when_it_is_pull_request(): void
    {
        $this->ciDetectorEnv->setVariables([
            'TRAVIS' => 'true',
            'TRAVIS_PULL_REQUEST' => '123',
        ]);

        $this->strykerDashboardClient
            ->expects($this->never())
            ->method('sendReport')
        ;

        $this->strykerLogger->log();

        $this->assertSame(
            [
                [
                    LogLevel::WARNING,
                    'Dashboard report has not been sent: The current process is a pull request build',
                    [],
                ],
            ],
            $this->logger->getLogs(),
        );
    }

    public function test_it_skips_logging_when_branch_not_found(): void
    {
        $this->ciDetectorEnv->setVariables([
            'TRAVIS' => 'true',
            'TRAVIS_PULL_REQUEST' => 'false',
            'TRAVIS_REPO_SLUG' => 'a/b',
            'TRAVIS_BRANCH' => false,
        ]);

        $this->strykerDashboardClient
            ->expects($this->never())
            ->method('sendReport');

        $this->strykerLogger->log();

        $this->assertSame(
            [
                [
                    LogLevel::WARNING,
                    'Dashboard report has not been sent: The branch name could not be determined for the current process',
                    [],
                ],
            ],
            $this->logger->getLogs(),
        );
    }

    public function test_it_skips_logging_when_repo_slug_not_found(): void
    {
        $this->ciDetectorEnv->setVariables([
            'TRAVIS' => 'true',
            'TRAVIS_PULL_REQUEST' => 'false',
            'TRAVIS_REPO_SLUG' => false,
            'TRAVIS_BRANCH' => 'foo',
        ]);

        $this->strykerDashboardClient
            ->expects($this->never())
            ->method('sendReport')
        ;

        $this->strykerLogger->log();

        $this->assertSame(
            [
                [
                    LogLevel::WARNING,
                    'Dashboard report has not been sent: The repository name could not be determined for the current process',
                    [],
                ],
            ],
            $this->logger->getLogs(),
        );
    }

    public function test_it_skips_logging_when_it_is_branch_not_from_config(): void
    {
        $this->ciDetectorEnv->setVariables([
            'TRAVIS' => 'true',
            'TRAVIS_PULL_REQUEST' => 'false',
            'TRAVIS_REPO_SLUG' => 'a/b',
            'TRAVIS_BRANCH' => 'foo',
        ]);

        $this->strykerDashboardClient
            ->expects($this->never())
            ->method('sendReport')
        ;

        $this->strykerLogger->log();

        $this->assertSame(
            [
                [
                    LogLevel::WARNING,
                    'Dashboard report has not been sent: Branch "foo" does not match expected Stryker configuration',
                    [],
                ],
            ],
            $this->logger->getLogs(),
        );
    }

    public function test_it_skips_logging_when_it_is_branch_not_from_config_regex(): void
    {
        $this->ciDetectorEnv->setVariables([
            'TRAVIS' => 'true',
            'TRAVIS_PULL_REQUEST' => 'false',
            'TRAVIS_REPO_SLUG' => 'a/b',
            'TRAVIS_BRANCH' => '1.x-mismatch',
        ]);

        $this->strykerDashboardClient
            ->expects($this->never())
            ->method('sendReport')
        ;

        $strykerLogger = new StrykerLogger(
            new BuildContextResolver(CiDetector::fromEnvironment($this->ciDetectorEnv)),
            new StrykerApiKeyResolver(),
            $this->strykerDashboardClient,
            $this->metricsCalculatorMock,
            new StrykerHtmlReportBuilder($this->metricsCalculatorMock, new ResultsCollector()),
            StrykerConfig::forBadge('/^\d+\\.x$/'),
            $this->logger,
        );

        $strykerLogger->log();

        $this->assertSame(
            [
                [
                    LogLevel::WARNING,
                    'Dashboard report has not been sent: Branch "1.x-mismatch" does not match expected Stryker configuration',
                    [],
                ],
            ],
            $this->logger->getLogs(),
        );
    }

    public function test_it_sends_report_missing_our_api_key(): void
    {
        $this->ciDetectorEnv->setVariables([
            'TRAVIS' => 'true',
            'TRAVIS_PULL_REQUEST' => 'false',
            'TRAVIS_REPO_SLUG' => 'a/b',
            'TRAVIS_BRANCH' => 'master',
        ]);

        putenv('INFECTION_DASHBOARD_API_KEY');
        putenv('STRYKER_DASHBOARD_API_KEY');

        $this->strykerDashboardClient
            ->expects($this->never())
            ->method('sendReport')
        ;

        $this->strykerLogger->log();

        $this->assertSame(
            [
                [
                    LogLevel::WARNING,
                    'Dashboard report has not been sent: The Stryker API key needs to be configured using one of the environment variables "INFECTION_DASHBOARD_API_KEY" or "STRYKER_DASHBOARD_API_KEY", but could not find any of these.',
                    [],
                ],
            ],
            $this->logger->getLogs(),
        );
    }

    public function test_it_sends_report_when_everything_is_ok_with_stryker_key(): void
    {
        $this->ciDetectorEnv->setVariables([
            'TRAVIS' => 'true',
            'TRAVIS_PULL_REQUEST' => 'false',
            'TRAVIS_REPO_SLUG' => 'a/b',
            'TRAVIS_BRANCH' => 'master',
        ]);

        putenv('STRYKER_DASHBOARD_API_KEY=abc');

        $this->strykerDashboardClient
            ->expects($this->once())
            ->method('sendReport')
            ->with('github.com/a/b', 'master', 'abc', '{"mutationScore":33.3}')
        ;

        $this->metricsCalculatorMock
            ->method('getMutationScoreIndicator')
            ->willReturn(33.3)
        ;

        $this->strykerLogger->log();

        $this->assertSame(
            [
                [
                    LogLevel::WARNING,
                    'Sending dashboard report...',
                    [],
                ],
            ],
            $this->logger->getLogs(),
        );
    }

    public function test_it_sends_report_when_everything_is_ok_with_stryker_key_and_matching_branch_regex(): void
    {
        $this->ciDetectorEnv->setVariables([
            'TRAVIS' => 'true',
            'TRAVIS_PULL_REQUEST' => 'false',
            'TRAVIS_REPO_SLUG' => 'a/b',
            'TRAVIS_BRANCH' => '7.x',
        ]);

        putenv('STRYKER_DASHBOARD_API_KEY=abc');

        $this->strykerDashboardClient
            ->expects($this->once())
            ->method('sendReport')
            ->with('github.com/a/b', '7.x', 'abc', '{"mutationScore":33.3}')
        ;

        $this->metricsCalculatorMock
            ->method('getMutationScoreIndicator')
            ->willReturn(33.3)
        ;

        $strykerLogger = new StrykerLogger(
            new BuildContextResolver(CiDetector::fromEnvironment($this->ciDetectorEnv)),
            new StrykerApiKeyResolver(),
            $this->strykerDashboardClient,
            $this->metricsCalculatorMock,
            new StrykerHtmlReportBuilder($this->metricsCalculatorMock, new ResultsCollector()),
            StrykerConfig::forBadge('/^\d+\\.x$/'),
            $this->logger,
        );

        $strykerLogger->log();

        $this->assertSame(
            [
                [
                    LogLevel::WARNING,
                    'Sending dashboard report...',
                    [],
                ],
            ],
            $this->logger->getLogs(),
        );
    }

    public function test_it_sends_report_when_everything_is_ok_with_our_key(): void
    {
        $this->ciDetectorEnv->setVariables([
            'TRAVIS' => 'true',
            'TRAVIS_PULL_REQUEST' => 'false',
            'TRAVIS_REPO_SLUG' => 'a/b',
            'TRAVIS_BRANCH' => 'master',
        ]);

        putenv('INFECTION_DASHBOARD_API_KEY=abc');

        $this->strykerDashboardClient
            ->expects($this->once())
            ->method('sendReport')
            ->with('github.com/a/b', 'master', 'abc', '{"mutationScore":33.3}')
        ;

        $this->metricsCalculatorMock
            ->method('getMutationScoreIndicator')
            ->willReturn(33.3)
        ;

        $this->strykerLogger->log();

        $this->assertSame(
            [
                [
                    LogLevel::WARNING,
                    'Sending dashboard report...',
                    [],
                ],
            ],
            $this->logger->getLogs(),
        );
    }

    public function test_it_sends_report_when_everything_is_ok_with_our_key_for_full_report(): void
    {
        $this->strykerLogger = new StrykerLogger(
            new BuildContextResolver(CiDetector::fromEnvironment($this->ciDetectorEnv)),
            new StrykerApiKeyResolver(),
            $this->strykerDashboardClient,
            $this->metricsCalculatorMock,
            new StrykerHtmlReportBuilder($this->metricsCalculatorMock, new ResultsCollector()),
            StrykerConfig::forFullReport('master'),
            $this->logger,
        );

        $this->ciDetectorEnv->setVariables([
            'TRAVIS' => 'true',
            'TRAVIS_PULL_REQUEST' => 'false',
            'TRAVIS_REPO_SLUG' => 'a/b',
            'TRAVIS_BRANCH' => 'master',
        ]);

        putenv('INFECTION_DASHBOARD_API_KEY=abc');

        $this->strykerDashboardClient
            ->expects($this->once())
            ->method('sendReport')
            ->with('github.com/a/b', 'master', 'abc', '{"schemaVersion":"1","thresholds":{"high":90,"low":50},"files":{},"testFiles":{},"framework":{"name":"Infection","branding":{"homepageUrl":"https:\/\/infection.github.io\/","imageUrl":"https:\/\/infection.github.io\/images\/logo.png"}}}')
        ;

        $this->metricsCalculatorMock
            ->method('getMutationScoreIndicator')
            ->willReturn(33.3)
        ;

        $this->strykerLogger->log();

        $this->assertSame(
            [
                [
                    LogLevel::WARNING,
                    'Sending dashboard report...',
                    [],
                ],
            ],
            $this->logger->getLogs(),
        );
    }
}
