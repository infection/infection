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

    public static function setUpBeforeClass()
    {
        // Save current env state
        $names = [
            'TRAVIS',
            'TRAVIS_BRANCH',
            'TRAVIS_REPO_SLUG',
            'TRAVIS_PULL_REQUEST',
            BadgeLogger::ENV_INFECTION_BADGE_API_KEY,
        ];

        foreach ($names as $name) {
            self::$env[$name] = getenv($name);
        }
    }

    public static function tearDownAfterClass()
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

    protected function setUp()
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

    public function test_it_skips_logging_when_it_is_not_travis()
    {
        putenv('TRAVIS');
        $this->output
            ->shouldReceive('writeln')
            ->withArgs(['Dashboard report has not been sent: it is not a Travis CI']);
        $this->badgeApiClient->shouldReceive('sendReport')->never();

        $this->badgeLogger->log();
    }

    public function test_it_skips_logging_when_it_is_pull_request()
    {
        putenv('TRAVIS=true');
        putenv('TRAVIS_PULL_REQUEST=123');
        $this->output
            ->shouldReceive('writeln')
            ->withArgs(['Dashboard report has not been sent: build is for a pull request (TRAVIS_PULL_REQUEST=123)']);
        $this->badgeApiClient->shouldReceive('sendReport')->never();

        $this->badgeLogger->log();
    }

    public function test_it_skips_logging_when_it_is_branch_not_from_config()
    {
        putenv('TRAVIS=true');
        putenv('TRAVIS_PULL_REQUEST=false');
        putenv('TRAVIS_REPO_SLUG=a/b');
        putenv('TRAVIS_BRANCH=foo');
        $this->output
            ->shouldReceive('writeln')
            ->withArgs(['Dashboard report has not been sent: Repository Slug=a/b, Branch=foo']);
        $this->badgeApiClient->shouldReceive('sendReport')->never();

        $this->badgeLogger->log();
    }

    public function test_it_sends_report_when_everything_is_ok()
    {
        putenv(BadgeLogger::ENV_INFECTION_BADGE_API_KEY . '=abc');
        putenv('TRAVIS=true');
        putenv('TRAVIS_PULL_REQUEST=false');
        putenv('TRAVIS_REPO_SLUG=a/b');
        putenv('TRAVIS_BRANCH=master');
        $this->output->shouldReceive('writeln')->never();
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
