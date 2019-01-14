<?php
/**
 * This code is licensed under the BSD 3-Clause License.
 *
 * Copyright (c) 2017-2019, Maks Rafalko
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

use Infection\Http\BadgeApiClient;
use Infection\Logger\BadgeLogger;
use Infection\Mutant\MetricsCalculator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal
 */
final class BadgeLoggerTest extends TestCase
{
    /**
     * @var OutputInterface|MockObject
     */
    private $output;

    /**
     * @var BadgeApiClient|MockObject
     */
    private $badgeApiClient;

    /**
     * @var MetricsCalculator|MockObject
     */
    private $metricsCalculator;

    /**
     * @var BadgeLogger
     */
    private $badgeLogger;

    private static $env = [];

    public static function setUpBeforeClass(): void
    {
        // Save current env state
        $names = [
            'TRAVIS',
            'TRAVIS_BRANCH',
            'TRAVIS_REPO_SLUG',
            'TRAVIS_PULL_REQUEST',
            BadgeLogger::ENV_INFECTION_BADGE_API_KEY,
            BadgeLogger::ENV_STRYKER_DASHBOARD_API_KEY,
        ];

        foreach ($names as $name) {
            self::$env[$name] = getenv($name);
        }
    }

    public static function tearDownAfterClass(): void
    {
        // Restore original env state
        foreach (self::$env as $name => $value) {
            if (false !== $value) {
                putenv($name . '=' . $value);
            } else {
                putenv($name);
            }
        }
    }

    protected function setUp(): void
    {
        $this->output = $this->createMock(OutputInterface::class);
        $this->badgeApiClient = $this->createMock(BadgeApiClient::class);
        $this->metricsCalculator = $this->createMock(MetricsCalculator::class);
        $config = new \stdClass();
        $config->branch = 'master';

        $this->badgeLogger = new BadgeLogger(
            $this->output,
            $this->badgeApiClient,
            $this->metricsCalculator,
            $config
        );
    }

    public function test_it_skips_logging_when_it_is_not_travis(): void
    {
        putenv('TRAVIS');
        $this->output
            ->method('writeln')
            ->with('Dashboard report has not been sent: it is not a Travis CI');

        $this->badgeApiClient
            ->expects($this->never())
            ->method('sendReport');

        $this->badgeLogger->log();
    }

    public function test_it_skips_logging_when_it_is_pull_request(): void
    {
        putenv('TRAVIS=true');
        putenv('TRAVIS_PULL_REQUEST=123');
        $this->output
            ->method('writeln')
            ->with('Dashboard report has not been sent: build is for a pull request (TRAVIS_PULL_REQUEST=123)');

        $this->badgeApiClient
            ->expects($this->never())
            ->method('sendReport');

        $this->badgeLogger->log();
    }

    public function test_it_skips_logging_when_branch_not_found(): void
    {
        putenv('TRAVIS=true');
        putenv('TRAVIS_PULL_REQUEST=false');
        putenv('TRAVIS_REPO_SLUG=a/b');
        putenv('TRAVIS_BRANCH');

        $this->output
            ->method('writeln')
            ->with('Dashboard report has not been sent: repository slug nor current branch were found; not a Travis build?');

        $this->badgeApiClient
            ->expects($this->never())
            ->method('sendReport');

        $this->badgeLogger->log();
    }

    public function test_it_skips_logging_when_repo_slug_not_found(): void
    {
        putenv('TRAVIS=true');
        putenv('TRAVIS_PULL_REQUEST=false');
        putenv('TRAVIS_REPO_SLUG');
        putenv('TRAVIS_BRANCH=foo');

        $this->output
            ->method('writeln')
            ->with('Dashboard report has not been sent: repository slug nor current branch were found; not a Travis build?');

        $this->badgeApiClient
            ->expects($this->never())
            ->method('sendReport');

        $this->badgeLogger->log();
    }

    public function test_it_skips_logging_when_it_is_branch_not_from_config(): void
    {
        putenv('TRAVIS=true');
        putenv('TRAVIS_PULL_REQUEST=false');
        putenv('TRAVIS_REPO_SLUG=a/b');
        putenv('TRAVIS_BRANCH=foo');
        $this->output
            ->method('writeln')
            ->with('Dashboard report has not been sent: expected branch "master", found "foo"');

        $this->badgeApiClient
            ->expects($this->never())
            ->method('sendReport');

        $this->badgeLogger->log();
    }

    public function test_it_sends_report_missing_our_api_key(): void
    {
        putenv('TRAVIS=true');
        putenv('TRAVIS_PULL_REQUEST=false');
        putenv('TRAVIS_REPO_SLUG=a/b');
        putenv('TRAVIS_BRANCH=master');

        putenv(BadgeLogger::ENV_INFECTION_BADGE_API_KEY);
        putenv(BadgeLogger::ENV_STRYKER_DASHBOARD_API_KEY);

        $this->output
            ->method('writeln')
            ->with('Dashboard report has not been sent: neither INFECTION_BADGE_API_KEY nor STRYKER_DASHBOARD_API_KEY were found in the environment');

        $this->badgeApiClient
            ->expects($this->never())
            ->method('sendReport');

        $this->badgeLogger->log();
    }

    public function test_it_sends_report_when_everything_is_ok_with_stryker_key(): void
    {
        putenv(BadgeLogger::ENV_STRYKER_DASHBOARD_API_KEY . '=abc');
        putenv('TRAVIS=true');
        putenv('TRAVIS_PULL_REQUEST=false');
        putenv('TRAVIS_REPO_SLUG=a/b');
        putenv('TRAVIS_BRANCH=master');

        $this->output
            ->method('writeln')
            ->with('Sending dashboard report...');

        $this->badgeApiClient
            ->expects($this->once())
            ->method('sendReport')
            ->with('abc', 'github.com/a/b', 'master', 33.3);

        $this->metricsCalculator
            ->method('getMutationScoreIndicator')
            ->willReturn(33.3);

        $this->badgeLogger->log();
    }

    public function test_it_sends_report_when_everything_is_ok_with_our_key(): void
    {
        putenv(BadgeLogger::ENV_INFECTION_BADGE_API_KEY . '=abc');
        putenv('TRAVIS=true');
        putenv('TRAVIS_PULL_REQUEST=false');
        putenv('TRAVIS_REPO_SLUG=a/b');
        putenv('TRAVIS_BRANCH=master');

        $this->output
            ->method('writeln')
            ->with('Sending dashboard report...');

        $this->badgeApiClient
            ->expects($this->once())
            ->method('sendReport')
            ->with('abc', 'github.com/a/b', 'master', 33.3);

        $this->metricsCalculator
            ->method('getMutationScoreIndicator')
            ->willReturn(33.3);

        $this->badgeLogger->log();
    }
}
