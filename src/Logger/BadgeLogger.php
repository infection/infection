<?php
/**
 * This code is licensed under the BSD 3-Clause License.
 *
 * Copyright (c) 2017-2018, Maks Rafalko
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

namespace Infection\Logger;

use Infection\Http\BadgeApiClient;
use Infection\Mutant\MetricsCalculator;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal
 */
final class BadgeLogger implements MutationTestingResultsLogger
{
    public const ENV_INFECTION_BADGE_API_KEY = 'INFECTION_BADGE_API_KEY';
    public const ENV_STRYKER_DASHBOARD_API_KEY = 'STRYKER_DASHBOARD_API_KEY';

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

    public function log(): void
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

    private function showInfo(string $message): void
    {
        $this->output->writeln(sprintf('Dashboard report has not been sent: %s', $message));
    }
}
