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

use Infection\Environment\BuildContextResolver;
use Infection\Environment\CouldNotResolveBuildContext;
use Infection\Environment\CouldNotResolveStrykerApiKey;
use Infection\Environment\StrykerApiKeyResolver;
use Infection\Http\BadgeApiClient;
use Infection\Mutant\MetricsCalculator;
use function Safe\sprintf;
use stdClass;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal
 */
final class BadgeLogger implements MutationTestingResultsLogger
{
    private $output;
    private $buildContextResolver;
    private $strykerApiKeyResolver;
    private $badgeApiClient;
    private $metricsCalculator;
    private $config;

    public function __construct(
        OutputInterface $output,
        BuildContextResolver $platformResolver,
        StrykerApiKeyResolver $strykerApiKeyResolver,
        BadgeApiClient $badgeApiClient,
        MetricsCalculator $metricsCalculator,
        stdClass $config
    ) {
        $this->output = $output;
        $this->buildContextResolver = $platformResolver;
        $this->strykerApiKeyResolver = $strykerApiKeyResolver;
        $this->badgeApiClient = $badgeApiClient;
        $this->metricsCalculator = $metricsCalculator;
        $this->config = $config;
    }

    public function log(): void
    {
        try {
            $buildContext = $this->buildContextResolver->resolve(getenv());
        } catch (CouldNotResolveBuildContext $exception) {
            $this->showInfo($exception->getMessage());

            return;
        }

        if ($buildContext->branch() !== $this->config->branch) {
            $this->showInfo(sprintf(
                'expected branch "%s", found "%s"',
                $this->config->branch,
                $buildContext->branch()
            ));

            return;
        }

        try {
            $apiKey = $this->strykerApiKeyResolver->resolve(getenv());
        } catch (CouldNotResolveStrykerApiKey $exception) {
            $this->showInfo($exception->getMessage());

            return;
        }

        /*
         * All clear!
         */
        $this->output->writeln('Sending dashboard report...');

        $this->badgeApiClient->sendReport(
            $apiKey,
            'github.com/' . $buildContext->repositorySlug(),
            $buildContext->branch(),
            $this->metricsCalculator->getMutationScoreIndicator()
        );
    }

    private function showInfo(string $message): void
    {
        $this->output->writeln(sprintf('Dashboard report has not been sent: %s', $message));
    }
}
