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
use Infection\Http\StrykerCurlClient;
use Infection\Http\StrykerDashboardClient;
use Infection\Mutant\MetricsCalculator;
use OndraM\CiDetector\CiDetector;
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
        return new LoggerRegistry(
            ...array_filter(
                [
                    $this->createTextLogger($output, $logConfig->getTextLogFilePath()),
                    $this->createSummaryLogger($output, $logConfig->getSummaryLogFilePath()),
                    $this->createDebugLogger($output, $logConfig->getDebugLogFilePath()),
                    $this->createPerMutatorLogger($output, $logConfig->getPerMutatorFilePath()),
                    $this->createBadgeLogger($output, $logConfig->getBadge()),
                ],
                function (?MutationTestingResultsLogger $logger): bool {
                    return $logger !== null && $this->isAllowedToLog($logger);
                }
            )
        );
    }

    private function createTextLogger(
        OutputInterface $output,
        ?string $filePath
    ): ?FileLogger {
        return $filePath === null
            ? null
            : new FileLogger(
                $output,
                $filePath,
                $this->filesystem,
                new TextFileLogger(
                    $this->metricsCalculator,
                    $this->logVerbosity === LogVerbosity::DEBUG,
                    $this->onlyCoveredCode,
                    $this->debugMode
                )
            )
        ;
    }

    private function createSummaryLogger(
        OutputInterface $output,
        ?string $filePath
    ): ?FileLogger {
        return $filePath === null
            ? null
            : new FileLogger(
                $output,
                $filePath,
                $this->filesystem,
                new SummaryFileLogger($this->metricsCalculator)
            )
        ;
    }

    private function createDebugLogger(
        OutputInterface $output,
        ?string $filePath
    ): ?FileLogger {
        return $filePath === null
            ? null
            : new FileLogger(
                $output,
                $filePath,
                $this->filesystem,
                new DebugFileLogger($this->metricsCalculator, $this->onlyCoveredCode)
            )
        ;
    }

    private function createPerMutatorLogger(
        OutputInterface $output,
        ?string $filePath
    ): ?FileLogger {
        return $filePath === null
            ? null
            : new FileLogger(
                $output,
                $filePath,
                $this->filesystem,
                new PerMutatorLogger($this->metricsCalculator)
            )
        ;
    }

    private function createBadgeLogger(OutputInterface $output, ?Badge $badge): ?BadgeLogger
    {
        return $badge === null
            ? null
            : new BadgeLogger(
                $output,
                new BuildContextResolver(new CiDetector()),
                new StrykerApiKeyResolver(),
                new StrykerDashboardClient(
                    new StrykerCurlClient(),
                    new ConsoleLogger($output)
                ),
                $this->metricsCalculator,
                $badge->getBranch()
            )
        ;
    }

    private function isAllowedToLog(MutationTestingResultsLogger $logger): bool
    {
        return $this->logVerbosity !== LogVerbosity::NONE || $logger instanceof BadgeLogger;
    }
}
