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
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @internal
 */
final class LoggerFactory
{
    private $metricsCalculator;
    private $filesystem;
    private $logVerbosity;
    private $debugMode;
    private $onlyCoveredCode;

    public function __construct(
        MetricsCalculator $metricsCalculator,
        Filesystem $filesystem,
        string $logVerbosity,
        bool $debugMode,
        bool $onlyCoveredCode
    ) {
        $this->metricsCalculator = $metricsCalculator;
        $this->filesystem = $filesystem;
        $this->logVerbosity = $logVerbosity;
        $this->debugMode = $debugMode;
        $this->onlyCoveredCode = $onlyCoveredCode;
    }

    public function createFromLogEntries(Logs $logConfig, OutputInterface $output): MutationTestingResultsLogger
    {
        $logger = new ConsoleLogger($output);

        return new LoggerRegistry(
            ...array_filter(
                [
                    $this->createTextLogger($logger, $logConfig->getTextLogFilePath()),
                    $this->createSummaryLogger($logger, $logConfig->getSummaryLogFilePath()),
                    $this->createDebugLogger($logger, $logConfig->getDebugLogFilePath()),
                    $this->createPerMutatorLogger($logger, $logConfig->getPerMutatorFilePath()),
                    $this->createBadgeLogger($logger, $logConfig->getBadge()),
                ],
                function (?MutationTestingResultsLogger $logger): bool {
                    return $logger !== null && $this->isAllowedToLog($logger);
                }
            )
        );
    }

    private function createTextLogger(
        LoggerInterface $logger,
        ?string $filePath
    ): ?FileLogger {
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
                $logger
            )
        ;
    }

    private function createSummaryLogger(
        LoggerInterface $logger,
        ?string $filePath
    ): ?FileLogger {
        return $filePath === null
            ? null
            : new FileLogger(
                $filePath,
                $this->filesystem,
                new SummaryFileLogger($this->metricsCalculator),
                $logger
            )
        ;
    }

    private function createDebugLogger(
        LoggerInterface $logger,
        ?string $filePath
    ): ?FileLogger {
        return $filePath === null
            ? null
            : new FileLogger(
                $filePath,
                $this->filesystem,
                new DebugFileLogger($this->metricsCalculator, $this->onlyCoveredCode),
                $logger
            )
        ;
    }

    private function createPerMutatorLogger(
        LoggerInterface $logger,
        ?string $filePath
    ): ?FileLogger {
        return $filePath === null
            ? null
            : new FileLogger(
                $filePath,
                $this->filesystem,
                new PerMutatorLogger($this->metricsCalculator),
                $logger
            )
        ;
    }

    private function createBadgeLogger(LoggerInterface $logger, ?Badge $badge): ?BadgeLogger
    {
        return $badge === null
            ? null
            : new BadgeLogger(
                new BuildContextResolver(new CiDetector()),
                new StrykerApiKeyResolver(),
                new StrykerDashboardClient(
                    new StrykerCurlClient(),
                    $logger
                ),
                $this->metricsCalculator,
                $badge->getBranch(),
                $logger
            )
        ;
    }

    private function isAllowedToLog(MutationTestingResultsLogger $logger): bool
    {
        return $this->logVerbosity !== LogVerbosity::NONE || $logger instanceof BadgeLogger;
    }
}
