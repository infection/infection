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

use Infection\Environment\BuildContextResolver;
use Infection\Environment\StrykerApiKeyResolver;
use Infection\Http\StrykerDashboardClient;
use Infection\Logger\BadgeLogger;
use Infection\Metrics\MetricsCalculator;
use Infection\Tests\Double\OndraM\CiDetector\ConfigurableEnv;
use Infection\Tests\EnvVariableManipulation\BacksUpEnvironmentVariables;
use OndraM\CiDetector\CiDetector;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use function Safe\putenv;

final class BadgeLoggerTest extends TestCase
{
    use BacksUpEnvironmentVariables;

    /**
     * @var StrykerDashboardClient|MockObject
     */
    private $badgeApiClientMock;

    /**
     * @var MetricsCalculator|MockObject
     */
    private $metricsCalculatorMock;

    /**
     * @var ConfigurableEnv
     */
    private $ciDetectorEnv;

    /**
     * @var DummyLogger
     */
    private $logger;

    /**
     * @var BadgeLogger
     */
    private $badgeLogger;

    protected function setUp(): void
    {
        $this->backupEnvironmentVariables();

        $this->badgeApiClientMock = $this->createMock(StrykerDashboardClient::class);
        $this->metricsCalculatorMock = $this->createMock(MetricsCalculator::class);
        $this->ciDetectorEnv = new ConfigurableEnv();
        $this->logger = new DummyLogger();

        $this->badgeLogger = new BadgeLogger(
            new BuildContextResolver(CiDetector::fromEnvironment($this->ciDetectorEnv)),
            new StrykerApiKeyResolver(),
            $this->badgeApiClientMock,
            $this->metricsCalculatorMock,
            'master',
            $this->logger
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

        $this->badgeApiClientMock
            ->expects($this->never())
            ->method('sendReport')
        ;

        $this->badgeLogger->log();

        $this->assertSame(
            [
                [
                    LogLevel::WARNING,
                    'Dashboard report has not been sent: The current process is not executed in a CI build',
                    [],
                ],
            ],
            $this->logger->getLogs()
        );
    }

    public function test_it_skips_logging_when_it_is_pull_request(): void
    {
        $this->ciDetectorEnv->setVariables([
            'TRAVIS' => 'true',
            'TRAVIS_PULL_REQUEST' => '123',
        ]);

        $this->badgeApiClientMock
            ->expects($this->never())
            ->method('sendReport')
        ;

        $this->badgeLogger->log();

        $this->assertSame(
            [
                [
                    LogLevel::WARNING,
                    'Dashboard report has not been sent: The current process is a pull request build',
                    [],
                ],
            ],
            $this->logger->getLogs()
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

        $this->badgeApiClientMock
            ->expects($this->never())
            ->method('sendReport');

        $this->badgeLogger->log();

        $this->assertSame(
            [
                [
                    LogLevel::WARNING,
                    'Dashboard report has not been sent: The branch name could not be determined for the current process',
                    [],
                ],
            ],
            $this->logger->getLogs()
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

        $this->badgeApiClientMock
            ->expects($this->never())
            ->method('sendReport')
        ;

        $this->badgeLogger->log();

        $this->assertSame(
            [
                [
                    LogLevel::WARNING,
                    'Dashboard report has not been sent: The repository name could not be determined for the current process',
                    [],
                ],
            ],
            $this->logger->getLogs()
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

        $this->badgeApiClientMock
            ->expects($this->never())
            ->method('sendReport')
        ;

        $this->badgeLogger->log();

        $this->assertSame(
            [
                [
                    LogLevel::WARNING,
                    'Dashboard report has not been sent: Expected branch "master", found "foo"',
                    [],
                ],
            ],
            $this->logger->getLogs()
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

        putenv('INFECTION_BADGE_API_KEY');
        putenv('STRYKER_DASHBOARD_API_KEY');

        $this->badgeApiClientMock
            ->expects($this->never())
            ->method('sendReport')
        ;

        $this->badgeLogger->log();

        $this->assertSame(
            [
                [
                    LogLevel::WARNING,
                    'Dashboard report has not been sent: The Stryker API key needs to be configured using one of the environment variables "INFECTION_BADGE_API_KEY" or "STRYKER_DASHBOARD_API_KEY", but could not find any of these.',
                    [],
                ],
            ],
            $this->logger->getLogs()
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

        $this->badgeApiClientMock
            ->expects($this->once())
            ->method('sendReport')
            ->with('github.com/a/b', 'master', 'abc', 33.3)
        ;

        $this->metricsCalculatorMock
            ->method('getMutationScoreIndicator')
            ->willReturn(33.3)
        ;

        $this->badgeLogger->log();

        $this->assertSame(
            [
                [
                    LogLevel::WARNING,
                    'Sending dashboard report...',
                    [],
                ],
            ],
            $this->logger->getLogs()
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

        putenv('INFECTION_BADGE_API_KEY=abc');

        $this->badgeApiClientMock
            ->expects($this->once())
            ->method('sendReport')
            ->with('github.com/a/b', 'master', 'abc', 33.3)
        ;

        $this->metricsCalculatorMock
            ->method('getMutationScoreIndicator')
            ->willReturn(33.3)
        ;

        $this->badgeLogger->log();

        $this->assertSame(
            [
                [
                    LogLevel::WARNING,
                    'Sending dashboard report...',
                    [],
                ],
            ],
            $this->logger->getLogs()
        );
    }
}
