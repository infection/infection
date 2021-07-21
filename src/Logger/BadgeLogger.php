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

namespace Infection\Logger;

use function getenv;
use Infection\Configuration\Entry\Badge;
use Infection\Environment\BuildContextResolver;
use Infection\Environment\CouldNotResolveBuildContext;
use Infection\Environment\CouldNotResolveStrykerApiKey;
use Infection\Environment\StrykerApiKeyResolver;
use Infection\Logger\Http\StrykerDashboardClient;
use Infection\Metrics\MetricsCalculator;
use Psr\Log\LoggerInterface;
use function Safe\sprintf;

/**
 * @internal
 */
final class BadgeLogger implements MutationTestingResultsLogger
{
    private BuildContextResolver $buildContextResolver;
    private StrykerApiKeyResolver $strykerApiKeyResolver;
    private StrykerDashboardClient $strykerDashboardClient;
    private MetricsCalculator $metricsCalculator;
    private Badge $badge;
    private LoggerInterface $logger;

    public function __construct(
        BuildContextResolver $buildContextResolver,
        StrykerApiKeyResolver $strykerApiKeyResolver,
        StrykerDashboardClient $strykerDashboardClient,
        MetricsCalculator $metricsCalculator,
        Badge $badge,
        LoggerInterface $logger
    ) {
        $this->buildContextResolver = $buildContextResolver;
        $this->strykerApiKeyResolver = $strykerApiKeyResolver;
        $this->strykerDashboardClient = $strykerDashboardClient;
        $this->metricsCalculator = $metricsCalculator;
        $this->badge = $badge;
        $this->logger = $logger;
    }

    public function log(): void
    {
        try {
            $buildContext = $this->buildContextResolver->resolve();
        } catch (CouldNotResolveBuildContext $exception) {
            $this->logReportWasNotSent($exception->getMessage());

            return;
        }

        $branch = $buildContext->branch();

        if (!$this->badge->applicableForBranch($branch)) {
            $this->logReportWasNotSent(sprintf(
                'Branch "%s" does not match expected badge configuration',
                $branch
            ));

            return;
        }

        try {
            $apiKey = $this->strykerApiKeyResolver->resolve(getenv());
        } catch (CouldNotResolveStrykerApiKey $exception) {
            $this->logReportWasNotSent($exception->getMessage());

            return;
        }

        // All clear!
        $this->logger->warning('Sending dashboard report...');

        $this->strykerDashboardClient->sendReport(
            'github.com/' . $buildContext->repositorySlug(),
            $branch,
            $apiKey,
            $this->metricsCalculator->getMutationScoreIndicator()
        );
    }

    private function logReportWasNotSent(string $message): void
    {
        $this->logger->warning(sprintf('Dashboard report has not been sent: %s', $message));
    }
}
