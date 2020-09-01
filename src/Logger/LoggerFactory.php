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
use OndraM\CiDetector\CiDetector;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @internal
 * @final
 */
class LoggerFactory
{
    private $metricsCalculator;
    private $filesystem;
    private $logVerbosity;
    private $debugMode;
    private $onlyCoveredCode;
    private $ciDetector;
    private $logger;

    public function __construct(
        MetricsCalculator $metricsCalculator,
        Filesystem $filesystem,
        string $logVerbosity,
        bool $debugMode,
        bool $onlyCoveredCode,
        CiDetector $ciDetector,
        LoggerInterface $logger
    ) {
        $this->metricsCalculator = $metricsCalculator;
        $this->filesystem = $filesystem;
        $this->logVerbosity = $logVerbosity;
        $this->debugMode = $debugMode;
        $this->onlyCoveredCode = $onlyCoveredCode;
        $this->ciDetector = $ciDetector;
        $this->logger = $logger;
    }

    public function createFromLogEntries(Logs $logConfig): MutationTestingResultsLogger
    {
        return new LoggerRegistry(
            ...array_filter(
                [
                    $this->createTextLogger($logConfig->getTextLogFilePath()),
                    $this->createSummaryLogger($logConfig->getSummaryLogFilePath()),
                    $this->createJsonLogger($logConfig->getJsonLogFilePath()),
                    $this->createDebugLogger($logConfig->getDebugLogFilePath()),
                    $this->createPerMutatorLogger($logConfig->getPerMutatorFilePath()),
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
        return $filePath === null
            ? null
            : new FileLogger(
                $filePath,
                $this->filesystem,
                new TextFileLogger(
                    $this->metricsCalculator,
                    $this->logVerbosity === LogVerbosity::DEBUG,
                    $this->onlyCoveredCode,
                    $this->debugMode
                ),
                $this->logger
            )
        ;
    }

    private function createSummaryLogger(?string $filePath): ?FileLogger
    {
        return $filePath === null
            ? null
            : new FileLogger(
                $filePath,
                $this->filesystem,
                new SummaryFileLogger($this->metricsCalculator),
                $this->logger
            )
        ;
    }

    private function createJsonLogger(?string $filePath): ?FileLogger
    {
        return $filePath === null
            ? null
            : new FileLogger(
                $filePath,
                $this->filesystem,
                new JsonLogger($this->metricsCalculator, $this->onlyCoveredCode),
                $this->logger
            )
        ;
    }

    private function createDebugLogger(?string $filePath): ?FileLogger
    {
        return $filePath === null
            ? null
            : new FileLogger(
                $filePath,
                $this->filesystem,
                new DebugFileLogger($this->metricsCalculator, $this->onlyCoveredCode),
                $this->logger
            )
        ;
    }

    private function createPerMutatorLogger(?string $filePath): ?FileLogger
    {
        return $filePath === null
            ? null
            : new FileLogger(
                $filePath,
                $this->filesystem,
                new PerMutatorLogger($this->metricsCalculator),
                $this->logger
            )
        ;
    }

    private function createBadgeLogger(?Badge $badge): ?BadgeLogger
    {
        return $badge === null
            ? null
            : new BadgeLogger(
                new BuildContextResolver($this->ciDetector),
                new StrykerApiKeyResolver(),
                new StrykerDashboardClient(
                    new StrykerCurlClient(),
                    $this->logger
                ),
                $this->metricsCalculator,
                $badge->getBranch(),
                $this->logger
            )
        ;
    }

    private function isAllowedToLog(MutationTestingResultsLogger $logger): bool
    {
        return $this->logVerbosity !== LogVerbosity::NONE || $logger instanceof BadgeLogger;
    }
}
