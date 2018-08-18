<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Logger;

use Infection\Http\BadgeApiClient;
use Infection\Logger\BadgeLogger;
use Infection\Mutant\MetricsCalculator;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal
 */
final class BadgeLoggerTest extends MockeryTestCase
{
    /**
     * @var OutputInterface|Mockery\MockInterface
     */
    private $output;

    /**
     * @var BadgeApiClient|Mockery\MockInterface
     */
    private $badgeApiClient;

    /**
     * @var MetricsCalculator|Mockery\MockInterface
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
        $this->output = Mockery::mock(OutputInterface::class);
        $this->badgeApiClient = Mockery::mock(BadgeApiClient::class);
        $this->metricsCalculator = Mockery::mock(MetricsCalculator::class);
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
            ->shouldReceive('writeln')
            ->withArgs(['Dashboard report has not been sent: it is not a Travis CI']);
        $this->badgeApiClient->shouldReceive('sendReport')->never();

        $this->badgeLogger->log();
    }

    public function test_it_skips_logging_when_it_is_pull_request(): void
    {
        putenv('TRAVIS=true');
        putenv('TRAVIS_PULL_REQUEST=123');
        $this->output
            ->shouldReceive('writeln')
            ->withArgs(['Dashboard report has not been sent: build is for a pull request (TRAVIS_PULL_REQUEST=123)']);
        $this->badgeApiClient->shouldReceive('sendReport')->never();

        $this->badgeLogger->log();
    }

    public function test_it_skips_logging_when_branch_not_found(): void
    {
        putenv('TRAVIS=true');
        putenv('TRAVIS_PULL_REQUEST=false');
        putenv('TRAVIS_REPO_SLUG=a/b');
        putenv('TRAVIS_BRANCH');

        $this->output
            ->shouldReceive('writeln')
            ->withArgs(['Dashboard report has not been sent: repository slug nor current branch were found; not a Travis build?']);
        $this->badgeApiClient->shouldReceive('sendReport')->never();

        $this->badgeLogger->log();
    }

    public function test_it_skips_logging_when_repo_slug_not_found(): void
    {
        putenv('TRAVIS=true');
        putenv('TRAVIS_PULL_REQUEST=false');
        putenv('TRAVIS_REPO_SLUG');
        putenv('TRAVIS_BRANCH=foo');

        $this->output
            ->shouldReceive('writeln')
            ->withArgs(['Dashboard report has not been sent: repository slug nor current branch were found; not a Travis build?']);
        $this->badgeApiClient->shouldReceive('sendReport')->never();

        $this->badgeLogger->log();
    }

    public function test_it_skips_logging_when_it_is_branch_not_from_config(): void
    {
        putenv('TRAVIS=true');
        putenv('TRAVIS_PULL_REQUEST=false');
        putenv('TRAVIS_REPO_SLUG=a/b');
        putenv('TRAVIS_BRANCH=foo');
        $this->output
            ->shouldReceive('writeln')
            ->withArgs(['Dashboard report has not been sent: expected branch "master", found "foo"']);
        $this->badgeApiClient->shouldReceive('sendReport')->never();

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
        ->shouldReceive('writeln')
        ->withArgs(['Dashboard report has not been sent: neither INFECTION_BADGE_API_KEY nor STRYKER_DASHBOARD_API_KEY were found in the environment']);
        $this->badgeApiClient->shouldReceive('sendReport')->never();

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
            ->shouldReceive('writeln')
            ->withArgs(['Sending dashboard report...']);
        $this->badgeApiClient
            ->shouldReceive('sendReport')
            ->once()
            ->withArgs(['abc', 'github.com/a/b', 'master', 33.3]);
        $this->metricsCalculator
            ->shouldReceive('getMutationScoreIndicator')
            ->andReturn(33.3);

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
            ->shouldReceive('writeln')
            ->withArgs(['Sending dashboard report...']);

        $this->badgeApiClient
            ->shouldReceive('sendReport')
            ->once()
            ->withArgs(['abc', 'github.com/a/b', 'master', 33.3]);
        $this->metricsCalculator
            ->shouldReceive('getMutationScoreIndicator')
            ->andReturn(33.3);

        $this->badgeLogger->log();
    }
}
