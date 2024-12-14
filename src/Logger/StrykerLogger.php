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
use Infection\Configuration\Entry\StrykerConfig;
use Infection\Environment\BuildContextResolver;
use Infection\Environment\CouldNotResolveBuildContext;
use Infection\Environment\CouldNotResolveStrykerApiKey;
use Infection\Environment\StrykerApiKeyResolver;
use Infection\Logger\Html\StrykerHtmlReportBuilder;
use Infection\Logger\Http\StrykerDashboardClient;
use Infection\Metrics\MetricsCalculator;
use Psr\Log\LoggerInterface;
use function Safe\json_encode;
use function sprintf;

/**
 * @internal
 */
final readonly class StrykerLogger implements MutationTestingResultsLogger
{
    public function __construct(private BuildContextResolver $buildContextResolver, private StrykerApiKeyResolver $strykerApiKeyResolver, private StrykerDashboardClient $strykerDashboardClient, private MetricsCalculator $metricsCalculator, private StrykerHtmlReportBuilder $strykerHtmlReportBuilder, private StrykerConfig $strykerConfig, private LoggerInterface $logger)
    {
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

        if (!$this->strykerConfig->applicableForBranch($branch)) {
            $this->logReportWasNotSent(sprintf(
                'Branch "%s" does not match expected Stryker configuration',
                $branch,
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

        // note: full html report updates Badge value as well
        $report = $this->strykerConfig->isForFullReport()
            ? $this->strykerHtmlReportBuilder->build()
            : ['mutationScore' => $this->metricsCalculator->getMutationScoreIndicator()];

        $this->strykerDashboardClient->sendReport(
            'github.com/' . $buildContext->repositorySlug(),
            $branch,
            $apiKey,
            json_encode($report),
        );
    }

    private function logReportWasNotSent(string $message): void
    {
        $this->logger->warning(sprintf('Dashboard report has not been sent: %s', $message));
    }
}
