<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Logger;

use _HumbugBox9658796bb9f0\Infection\Metrics\MetricsCalculator;
use _HumbugBox9658796bb9f0\Infection\Metrics\ResultsCollector;
use _HumbugBox9658796bb9f0\Infection\Mutant\MutantExecutionResult;
use _HumbugBox9658796bb9f0\Infection\Str;
use function json_encode;
use const JSON_THROW_ON_ERROR;
final class JsonLogger implements LineMutationTestingResultsLogger
{
    public function __construct(private MetricsCalculator $metricsCalculator, private ResultsCollector $resultsCollector, private bool $onlyCoveredMode)
    {
    }
    public function getLogLines() : array
    {
        $data = ['stats' => ['totalMutantsCount' => $this->metricsCalculator->getTotalMutantsCount(), 'killedCount' => $this->metricsCalculator->getKilledCount(), 'notCoveredCount' => $this->metricsCalculator->getNotTestedCount(), 'escapedCount' => $this->metricsCalculator->getEscapedCount(), 'errorCount' => $this->metricsCalculator->getErrorCount(), 'syntaxErrorCount' => $this->metricsCalculator->getSyntaxErrorCount(), 'skippedCount' => $this->metricsCalculator->getSkippedCount(), 'ignoredCount' => $this->metricsCalculator->getIgnoredCount(), 'timeOutCount' => $this->metricsCalculator->getTimedOutCount(), 'msi' => $this->metricsCalculator->getMutationScoreIndicator(), 'mutationCodeCoverage' => $this->metricsCalculator->getCoverageRate(), 'coveredCodeMsi' => $this->metricsCalculator->getCoveredCodeMutationScoreIndicator()], 'escaped' => $this->getResultsLine($this->resultsCollector->getEscapedExecutionResults()), 'timeouted' => $this->getResultsLine($this->resultsCollector->getTimedOutExecutionResults()), 'killed' => $this->getResultsLine($this->resultsCollector->getKilledExecutionResults()), 'errored' => $this->getResultsLine($this->resultsCollector->getErrorExecutionResults()), 'syntaxErrors' => $this->getResultsLine($this->resultsCollector->getSyntaxErrorExecutionResults()), 'uncovered' => $this->onlyCoveredMode ? [] : $this->getResultsLine($this->resultsCollector->getNotCoveredExecutionResults()), 'ignored' => $this->getResultsLine($this->resultsCollector->getIgnoredExecutionResults())];
        return [json_encode($data, JSON_THROW_ON_ERROR)];
    }
    private function getResultsLine(array $executionResults) : array
    {
        $mutatorRows = [];
        foreach ($executionResults as $mutantProcess) {
            $mutatorRows[] = ['mutator' => ['mutatorName' => $mutantProcess->getMutatorName(), 'originalSourceCode' => $mutantProcess->getOriginalCode(), 'mutatedSourceCode' => $mutantProcess->getMutatedCode(), 'originalFilePath' => $mutantProcess->getOriginalFilePath(), 'originalStartLine' => $mutantProcess->getOriginalStartingLine()], 'diff' => Str::convertToUtf8(Str::trimLineReturns($mutantProcess->getMutantDiff())), 'processOutput' => Str::convertToUtf8(Str::trimLineReturns($mutantProcess->getProcessOutput()))];
        }
        return $mutatorRows;
    }
}
