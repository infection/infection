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

class BadgeLogger implements MutationTestingResultsLogger
{
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

    public function __construct(BadgeApiClient $badgeApiClient, MetricsCalculator $metricsCalculator, \stdClass $config)
    {
        $this->badgeApiClient = $badgeApiClient;
        $this->metricsCalculator = $metricsCalculator;
        $this->config = $config;
    }

    public function log()
    {
        $travisBuild = getenv('TRAVIS');

        if ($travisBuild !== 'true') {
            return;
        }

        $pullRequest = getenv('TRAVIS_PULL_REQUEST');

        if ($pullRequest !== 'false') {
            return;
        }

        $apiKey = getenv('INFECTION_BADGE_API_KEY');
        $repositorySlug = getenv('TRAVIS_REPO_SLUG');
        $currentBranch = getenv('TRAVIS_BRANCH');

        if ($apiKey && $repositorySlug && $currentBranch === $this->config->branch) {
            $this->badgeApiClient->sendReport(
                $apiKey,
                'github.com/' . $repositorySlug,
                $currentBranch,
                $this->metricsCalculator->getMutationScoreIndicator()
            );
        }
    }
}
