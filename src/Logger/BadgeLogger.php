<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Logger;

use Infection\Http\BadgeApiClient;
use Infection\Mutant\MetricsCalculator;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal
 */
final class BadgeLogger implements MutationTestingResultsLogger
{
    const ENV_INFECTION_BADGE_API_KEY = 'INFECTION_BADGE_API_KEY';
    const ENV_STRYKER_DASHBOARD_API_KEY = 'STRYKER_DASHBOARD_API_KEY';

    /**
     * @var BadgeApiClient
     */
    private $badgeApiClient;

    /**
     * @var MetricsCalculator
     */
    private $metricsCalculator;

    /**
     * @var \stdClass
     */
    private $config;

    /**
     * @var OutputInterface
     */
    private $output;

    public function __construct(OutputInterface $output, BadgeApiClient $badgeApiClient, MetricsCalculator $metricsCalculator, \stdClass $config)
    {
        $this->output = $output;
        $this->badgeApiClient = $badgeApiClient;
        $this->metricsCalculator = $metricsCalculator;
        $this->config = $config;
    }

    public function log()
    {
        $travisBuild = getenv('TRAVIS');

        if ($travisBuild !== 'true') {
            $this->showInfo('it is not a Travis CI');

            return;
        }

        $pullRequest = getenv('TRAVIS_PULL_REQUEST');

        if ($pullRequest !== 'false') {
            $this->showInfo("build is for a pull request (TRAVIS_PULL_REQUEST={$pullRequest})");

            return;
        }

        $repositorySlug = getenv('TRAVIS_REPO_SLUG');
        $currentBranch = getenv('TRAVIS_BRANCH');

        if ($repositorySlug === false || $currentBranch === false) {
            $this->showInfo('repository slug nor current branch were found; not a Travis build?');

            return;
        }

        if ($currentBranch !== $this->config->branch) {
            $this->showInfo(
                sprintf(
                    'expected branch "%s", found "%s"',
                    $this->config->branch,
                    $currentBranch
                )
            );

            return;
        }

        $apiKey = getenv(self::ENV_INFECTION_BADGE_API_KEY);

        /*
         * Original Stryker Dashboard manual warrants a different API key:
         * fall back to theirs if our isn't present
         */
        if ($apiKey === false) {
            $apiKey = getenv(self::ENV_STRYKER_DASHBOARD_API_KEY);
        }

        if ($apiKey === false) {
            $this->showInfo(
                sprintf(
                    'neither %s nor %s were found in the environment',
                    self::ENV_INFECTION_BADGE_API_KEY,
                    self::ENV_STRYKER_DASHBOARD_API_KEY
                )
            );

            return;
        }

        /*
         * All clear!
         */
        $this->output->writeln('Sending dashboard report...');

        $this->badgeApiClient->sendReport(
            $apiKey,
            'github.com/' . $repositorySlug,
            $currentBranch,
            $this->metricsCalculator->getMutationScoreIndicator()
        );
    }

    private function showInfo(string $message)
    {
        $this->output->writeln(sprintf('Dashboard report has not been sent: %s', $message));
    }
}
