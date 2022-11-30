<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Logger;

use _HumbugBox9658796bb9f0\Infection\Configuration\Entry\Logs;
use _HumbugBox9658796bb9f0\Infection\Console\LogVerbosity;
use _HumbugBox9658796bb9f0\Infection\Logger\Html\HtmlFileLogger;
use _HumbugBox9658796bb9f0\Infection\Logger\Html\StrykerHtmlReportBuilder;
use _HumbugBox9658796bb9f0\Infection\Metrics\MetricsCalculator;
use _HumbugBox9658796bb9f0\Infection\Metrics\ResultsCollector;
use _HumbugBox9658796bb9f0\Psr\Log\LoggerInterface;
use _HumbugBox9658796bb9f0\Symfony\Component\Filesystem\Filesystem;
class FileLoggerFactory
{
    public function __construct(private MetricsCalculator $metricsCalculator, private ResultsCollector $resultsCollector, private Filesystem $filesystem, private string $logVerbosity, private bool $debugMode, private bool $onlyCoveredCode, private LoggerInterface $logger, private StrykerHtmlReportBuilder $strykerHtmlReportBuilder)
    {
    }
    public function createFromLogEntries(Logs $logConfig) : MutationTestingResultsLogger
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
    private function createLineLoggers(Logs $logConfig) : iterable
    {
        if ($this->logVerbosity === LogVerbosity::NONE) {
            return;
        }
        (yield $logConfig->getTextLogFilePath() => $this->createTextLogger());
        (yield $logConfig->getHtmlLogFilePath() => $this->createHtmlLogger());
        (yield $logConfig->getSummaryLogFilePath() => $this->createSummaryLogger());
        (yield $logConfig->getJsonLogFilePath() => $this->createJsonLogger());
        (yield $logConfig->getDebugLogFilePath() => $this->createDebugLogger());
        (yield $logConfig->getPerMutatorFilePath() => $this->createPerMutatorLogger());
        if ($logConfig->getUseGitHubAnnotationsLogger()) {
            (yield GitHubAnnotationsLogger::DEFAULT_OUTPUT => $this->createGitHubAnnotationsLogger());
        }
    }
    private function wrapWithFileLogger(string $filePath, LineMutationTestingResultsLogger $lineLogger) : MutationTestingResultsLogger
    {
        return new FileLogger($filePath, $this->filesystem, $lineLogger, $this->logger);
    }
    private function createTextLogger() : LineMutationTestingResultsLogger
    {
        return new TextFileLogger($this->resultsCollector, $this->logVerbosity === LogVerbosity::DEBUG, $this->onlyCoveredCode, $this->debugMode);
    }
    private function createHtmlLogger() : LineMutationTestingResultsLogger
    {
        return new HtmlFileLogger($this->strykerHtmlReportBuilder);
    }
    private function createSummaryLogger() : LineMutationTestingResultsLogger
    {
        return new SummaryFileLogger($this->metricsCalculator);
    }
    private function createJsonLogger() : LineMutationTestingResultsLogger
    {
        return new JsonLogger($this->metricsCalculator, $this->resultsCollector, $this->onlyCoveredCode);
    }
    private function createGitHubAnnotationsLogger() : LineMutationTestingResultsLogger
    {
        return new GitHubAnnotationsLogger($this->resultsCollector);
    }
    private function createDebugLogger() : LineMutationTestingResultsLogger
    {
        return new DebugFileLogger($this->metricsCalculator, $this->resultsCollector, $this->onlyCoveredCode);
    }
    private function createPerMutatorLogger() : LineMutationTestingResultsLogger
    {
        return new PerMutatorLogger($this->metricsCalculator, $this->resultsCollector);
    }
}
