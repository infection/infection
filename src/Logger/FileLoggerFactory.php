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
use Infection\Logger\Html\HtmlFileLogger;
use Infection\Logger\Html\StrykerHtmlReportBuilder;
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
    public function __construct(private readonly MetricsCalculator $metricsCalculator, private readonly ResultsCollector $resultsCollector, private readonly Filesystem $filesystem, private readonly string $logVerbosity, private readonly bool $debugMode, private readonly bool $onlyCoveredCode, private readonly LoggerInterface $logger, private readonly StrykerHtmlReportBuilder $strykerHtmlReportBuilder, private readonly ?string $loggerProjectRootDirectory)
    {
    }

    public function createFromLogEntries(Logs $logConfig): MutationTestingResultsLogger
    {
        $loggers = [];

        foreach ($this->createLineLoggers($logConfig) as $filePath => $lineLogger) {
            $loggers[] = $this->wrapWithFileLogger($filePath, $lineLogger);
        }

        return new FederatedLogger(...$loggers);
    }

    /**
     * @return iterable<string, LineMutationTestingResultsLogger>
     */
    private function createLineLoggers(Logs $logConfig): iterable
    {
        if ($this->logVerbosity === LogVerbosity::NONE) {
            return;
        }

        if ($logConfig->getTextLogFilePath() !== null) {
            yield $logConfig->getTextLogFilePath() => $this->createTextLogger();
        }

        if ($logConfig->getHtmlLogFilePath() !== null) {
            yield $logConfig->getHtmlLogFilePath() => $this->createHtmlLogger();
        }

        if ($logConfig->getSummaryLogFilePath() !== null) {
            yield $logConfig->getSummaryLogFilePath() => $this->createSummaryLogger();
        }

        if ($logConfig->getJsonLogFilePath() !== null) {
            yield $logConfig->getJsonLogFilePath() => $this->createJsonLogger();
        }

        if ($logConfig->getGitlabLogFilePath() !== null) {
            yield $logConfig->getGitlabLogFilePath() => $this->createGitlabLogger();
        }

        if ($logConfig->getDebugLogFilePath() !== null) {
            yield $logConfig->getDebugLogFilePath() => $this->createDebugLogger();
        }

        if ($logConfig->getPerMutatorFilePath() !== null) {
            yield $logConfig->getPerMutatorFilePath() => $this->createPerMutatorLogger();
        }

        if ($logConfig->getSummaryJsonLogFilePath() !== null) {
            yield $logConfig->getSummaryJsonLogFilePath() => $this->createSummaryJsonLogger();
        }

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
            $this->logger,
        );
    }

    private function createTextLogger(): LineMutationTestingResultsLogger
    {
        return new TextFileLogger(
            $this->resultsCollector,
            $this->logVerbosity === LogVerbosity::DEBUG,
            $this->onlyCoveredCode,
            $this->debugMode,
        );
    }

    private function createHtmlLogger(): LineMutationTestingResultsLogger
    {
        return new HtmlFileLogger(
            $this->strykerHtmlReportBuilder,
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
            $this->onlyCoveredCode,
        );
    }

    private function createGitlabLogger(): LineMutationTestingResultsLogger
    {
        return new GitLabCodeQualityLogger($this->resultsCollector, $this->loggerProjectRootDirectory);
    }

    private function createGitHubAnnotationsLogger(): LineMutationTestingResultsLogger
    {
        return new GitHubAnnotationsLogger($this->resultsCollector, $this->loggerProjectRootDirectory);
    }

    private function createDebugLogger(): LineMutationTestingResultsLogger
    {
        return new DebugFileLogger(
            $this->metricsCalculator,
            $this->resultsCollector,
            $this->onlyCoveredCode,
        );
    }

    private function createPerMutatorLogger(): LineMutationTestingResultsLogger
    {
        return new PerMutatorLogger(
            $this->metricsCalculator,
            $this->resultsCollector,
        );
    }

    private function createSummaryJsonLogger(): LineMutationTestingResultsLogger
    {
        return new SummaryJsonLogger($this->metricsCalculator);
    }
}
