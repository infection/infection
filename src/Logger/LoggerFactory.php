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

use function array_filter;
use Infection\Configuration\Entry\Badge;
use Infection\Configuration\Entry\Logs;
use Infection\Console\LogVerbosity;
use Infection\Environment\BuildContextResolver;
use Infection\Environment\StrykerApiKeyResolver;
use Infection\Logger\Http\StrykerCurlClient;
use Infection\Logger\Http\StrykerDashboardClient;
use Infection\Metrics\MetricsCalculator;
use Infection\Metrics\ResultsCollector;
use OndraM\CiDetector\CiDetector;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @internal
 * @final
 */
class LoggerFactory
{
    private MetricsCalculator $metricsCalculator;
    private ResultsCollector $resultsCollector;

    private Filesystem $filesystem;
    private string $logVerbosity;
    private bool $debugMode;
    private bool $onlyCoveredCode;
    private CiDetector $ciDetector;
    private LoggerInterface $logger;

    public function __construct(
        MetricsCalculator $metricsCalculator,
        ResultsCollector $resultsCollector,
        Filesystem $filesystem,
        string $logVerbosity,
        bool $debugMode,
        bool $onlyCoveredCode,
        CiDetector $ciDetector,
        LoggerInterface $logger
    ) {
        $this->metricsCalculator = $metricsCalculator;
        $this->resultsCollector = $resultsCollector;
        $this->filesystem = $filesystem;
        $this->logVerbosity = $logVerbosity;
        $this->debugMode = $debugMode;
        $this->onlyCoveredCode = $onlyCoveredCode;
        $this->ciDetector = $ciDetector;
        $this->logger = $logger;
    }

    public function createFromLogEntries(Logs $logConfig): MutationTestingResultsLogger
    {
        return new FederatedLogger(
            ...array_filter(
                [
                    $this->createTextLogger($logConfig->getTextLogFilePath()),
                    $this->createSummaryLogger($logConfig->getSummaryLogFilePath()),
                    $this->createJsonLogger($logConfig->getJsonLogFilePath()),
                    $this->createDebugLogger($logConfig->getDebugLogFilePath()),
                    $this->createPerMutatorLogger($logConfig->getPerMutatorFilePath()),
                    $this->createGitHubAnnotationsLogger($logConfig->getUseGitHubAnnotationsLogger()),
                    $this->createBadgeLogger($logConfig->getBadge()),
                ],
                function (?MutationTestingResultsLogger $logger): bool {
                    return $logger !== null && $this->isAllowedToLog($logger);
                }
            )
        );
    }

    private function createTextLogger(?string $filePath): ?FileLogger
    {
        if ($filePath === null) {
            return null;
        }

        $textLogger = new TextFileLogger(
            $this->resultsCollector,
            $this->logVerbosity === LogVerbosity::DEBUG,
            $this->onlyCoveredCode,
            $this->debugMode
        );

        return new FileLogger(
            $filePath,
            $this->filesystem,
            $textLogger,
            $this->logger
        );
    }

    private function createSummaryLogger(?string $filePath): ?FileLogger
    {
        if ($filePath === null) {
            return null;
        }

        $summaryFileLogger = new SummaryFileLogger($this->metricsCalculator);

        return new FileLogger(
            $filePath,
            $this->filesystem,
            $summaryFileLogger,
            $this->logger
        );
    }

    private function createJsonLogger(?string $filePath): ?FileLogger
    {
        if ($filePath === null) {
            return null;
        }

        $jsonLogger = new JsonLogger(
            $this->metricsCalculator,
            $this->resultsCollector,
            $this->onlyCoveredCode
        );

        return new FileLogger(
            $filePath,
            $this->filesystem,
            $jsonLogger,
            $this->logger
        );
    }

    private function createGitHubAnnotationsLogger(bool $useGitHubAnnotationsLogger): ?FileLogger
    {
        if ($useGitHubAnnotationsLogger === false) {
            return null;
        }

        $annotationsLogger = new GitHubAnnotationsLogger($this->resultsCollector);

        return new FileLogger(
            'php://stdout',
            $this->filesystem,
            $annotationsLogger,
            $this->logger
        );
    }

    private function createDebugLogger(?string $filePath): ?FileLogger
    {
        if ($filePath === null) {
            return null;
        }

        $debugLogger = new DebugFileLogger(
            $this->metricsCalculator,
            $this->resultsCollector,
            $this->onlyCoveredCode
        );

        return new FileLogger(
            $filePath,
            $this->filesystem,
            $debugLogger,
            $this->logger
        );
    }

    private function createPerMutatorLogger(?string $filePath): ?FileLogger
    {
        if ($filePath === null) {
            return null;
        }

        $perMutatorLogger = new PerMutatorLogger(
            $this->metricsCalculator,
            $this->resultsCollector
        );

        return new FileLogger(
            $filePath,
            $this->filesystem,
            $perMutatorLogger,
            $this->logger
        );
    }

    private function createBadgeLogger(?Badge $badge): ?BadgeLogger
    {
        if ($badge === null) {
            return null;
        }

        return new BadgeLogger(
            new BuildContextResolver($this->ciDetector),
            new StrykerApiKeyResolver(),
            new StrykerDashboardClient(
                new StrykerCurlClient(),
                $this->logger
            ),
            $this->metricsCalculator,
            $badge->getBranch(),
            $this->logger
        );
    }

    private function isAllowedToLog(MutationTestingResultsLogger $logger): bool
    {
        return $this->logVerbosity !== LogVerbosity::NONE || $logger instanceof BadgeLogger;
    }
}
