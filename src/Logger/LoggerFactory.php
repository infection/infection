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

use Infection\Configuration\Entry\Logs;
use Infection\Console\LogVerbosity;
use Infection\Environment\ChainBuildContextResolver;
use Infection\Environment\StrykerApiKeyResolver;
use Infection\Environment\TravisCiResolver;
use Infection\Http\JsonClient;
use Infection\Http\StrykerDashboardClient;
use Infection\Mutant\MetricsCalculator;
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

    /**
     * @return MutationTestingResultsLogger[]
     */
    public function createFromLogEntries(Logs $logs, OutputInterface $output): array
    {
        $isDebugVerbosity = $this->logVerbosity === LogVerbosity::DEBUG;
        $badge = $logs->getBadge();
        $debug = $logs->getDebugLogFilePath();
        $perMutator = $logs->getPerMutatorFilePath();
        $summary = $logs->getSummaryLogFilePath();
        $text = $logs->getTextLogFilePath();

        /** @var MutationTestingResultsLogger[] $loggers */
        $loggers = array_filter([
            ResultsLoggerTypes::BADGE => $badge === null
                ? null
                : $this->createBadgeLogger($output, $badge->getBranch()),
            ResultsLoggerTypes::DEBUG_FILE => $debug === null
                ? null
                : $this->createDebugLogger($output, $debug, $isDebugVerbosity),
            ResultsLoggerTypes::PER_MUTATOR => $perMutator === null
                ? null
                : $this->createPerMutatorLogger($output, $perMutator, $isDebugVerbosity),
            ResultsLoggerTypes::SUMMARY_FILE => $summary === null
                ? null
                : $this->createSummaryLogger($output, $summary, $isDebugVerbosity),
            ResultsLoggerTypes::TEXT_FILE => $text === null
                ? null
                : $this->createTextLogger($output, $text, $isDebugVerbosity),
        ]);

        return array_filter($loggers, [$this, 'isAllowedToLog'], ARRAY_FILTER_USE_KEY);
    }

    private function createTextLogger(
        OutputInterface $output,
        string $location,
        bool $isDebugVerbosity
    ): TextFileLogger {
        return new TextFileLogger(
            $output,
            $location,
            $this->metricsCalculator,
            $this->filesystem,
            $isDebugVerbosity,
            $this->debugMode,
            $this->onlyCoveredCode
        );
    }

    private function createSummaryLogger(
        OutputInterface $output,
        string $location,
        bool $isDebugVerbosity
    ): SummaryFileLogger {
        return new SummaryFileLogger(
            $output,
            $location,
            $this->metricsCalculator,
            $this->filesystem,
            $isDebugVerbosity,
            $this->debugMode
        );
    }

    private function createDebugLogger(
        OutputInterface $output,
        string $location,
        bool $isDebugVerbosity
    ): DebugFileLogger {
        return new DebugFileLogger(
            $output,
            $location,
            $this->metricsCalculator,
            $this->filesystem,
            $isDebugVerbosity,
            $this->debugMode,
            $this->onlyCoveredCode
        );
    }

    private function createPerMutatorLogger(
        OutputInterface $output,
        string $location,
        bool $isDebugVerbosity
    ): PerMutatorLogger {
        return new PerMutatorLogger(
            $output,
            $location,
            $this->metricsCalculator,
            $this->filesystem,
            $isDebugVerbosity,
            $this->debugMode
        );
    }

    private function createBadgeLogger(OutputInterface $output, string $branch): BadgeLogger
    {
        return new BadgeLogger(
            $output,
            new ChainBuildContextResolver(new TravisCiResolver()),
            new StrykerApiKeyResolver(),
            new StrykerDashboardClient(
                new JsonClient(),
                new ConsoleLogger($output)
            ),
            $this->metricsCalculator,
            (object) ['branch' => $branch]
        );
    }

    private function isAllowedToLog(string $logType): bool
    {
        return $this->logVerbosity !== LogVerbosity::NONE
            || in_array($logType, ResultsLoggerTypes::ALLOWED_WITHOUT_LOGGING, true)
        ;
    }
}
