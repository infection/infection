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
use Infection\Metrics\MetricsCalculator;
use Infection\Metrics\ResultsCollector;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @internal
 * @final
 */
class FileLoggerFactory
{
    private MetricsCalculator $metricsCalculator;
    private ResultsCollector $resultsCollector;

    private Filesystem $filesystem;
    private string $logVerbosity;
    private bool $debugMode;
    private bool $onlyCoveredCode;
    private LoggerInterface $logger;

    public function __construct(
        MetricsCalculator $metricsCalculator,
        ResultsCollector $resultsCollector,
        Filesystem $filesystem,
        string $logVerbosity,
        bool $debugMode,
        bool $onlyCoveredCode,
        LoggerInterface $logger
    ) {
        $this->metricsCalculator = $metricsCalculator;
        $this->resultsCollector = $resultsCollector;
        $this->filesystem = $filesystem;
        $this->logVerbosity = $logVerbosity;
        $this->debugMode = $debugMode;
        $this->onlyCoveredCode = $onlyCoveredCode;
        $this->logger = $logger;
    }

    public function createFromLogEntries(Logs $logConfig): MutationTestingResultsLogger
    {
        $loggers = [];

        foreach ($this->createLineLoggers($logConfig) as $filePath => $lineLogger) {
            if ($filePath === null) {
                continue;
            }

            $loggers[] = $this->wrapWithFileLogger($filePath, $lineLogger);
        }

        return new FederatedLogger(...$loggers);
    }

    /**
     * @return iterable<?string, LineMutationTestingResultsLogger>
     */
    private function createLineLoggers(Logs $logConfig): iterable
    {
        if ($this->logVerbosity === LogVerbosity::NONE) {
            return;
        }

        yield $logConfig->getTextLogFilePath() => $this->createTextLogger();

        yield $logConfig->getSummaryLogFilePath() => $this->createSummaryLogger();

        yield $logConfig->getJsonLogFilePath() => $this->createJsonLogger();

        yield $logConfig->getDebugLogFilePath() => $this->createDebugLogger();

        yield $logConfig->getPerMutatorFilePath() => $this->createPerMutatorLogger();

        if ($logConfig->getUseGitHubAnnotationsLogger()) {
            yield GitHubAnnotationsLogger::DEFAULT_OUTPUT => $this->createGitHubAnnotationsLogger();
        }
    }

    private function wrapWithFileLogger(string $filePath, LineMutationTestingResultsLogger $lineLogger): MutationTestingResultsLogger
    {
        return new FileLogger(
            $filePath,
            $this->filesystem,
            $lineLogger,
            $this->logger
        );
    }

    private function createTextLogger(): LineMutationTestingResultsLogger
    {
        return new TextFileLogger(
            $this->resultsCollector,
            $this->logVerbosity === LogVerbosity::DEBUG,
            $this->onlyCoveredCode,
            $this->debugMode
        );
    }

    private function createSummaryLogger(): LineMutationTestingResultsLogger
    {
        return new SummaryFileLogger($this->metricsCalculator);
    }

    private function createJsonLogger(): LineMutationTestingResultsLogger
    {
        return new JsonLogger(
            $this->metricsCalculator,
            $this->resultsCollector,
            $this->onlyCoveredCode
        );
    }

    private function createGitHubAnnotationsLogger(): LineMutationTestingResultsLogger
    {
        return new GitHubAnnotationsLogger($this->resultsCollector);
    }

    private function createDebugLogger(): LineMutationTestingResultsLogger
    {
        return new DebugFileLogger(
            $this->metricsCalculator,
            $this->resultsCollector,
            $this->onlyCoveredCode
        );
    }

    private function createPerMutatorLogger(): LineMutationTestingResultsLogger
    {
        return new PerMutatorLogger(
            $this->metricsCalculator,
            $this->resultsCollector
        );
    }
}
