<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Metrics;

use function array_key_exists;
use _HumbugBox9658796bb9f0\Infection\Mutant\DetectionStatus;
use _HumbugBox9658796bb9f0\Infection\Mutant\MutantExecutionResult;
use InvalidArgumentException;
use function _HumbugBox9658796bb9f0\Safe\sprintf;
class ResultsCollector implements Collector
{
    private array $resultsByStatus = [];
    private SortableMutantExecutionResults $allExecutionResults;
    public function __construct()
    {
        foreach (DetectionStatus::ALL as $status) {
            $this->resultsByStatus[$status] = new SortableMutantExecutionResults();
        }
        $this->allExecutionResults = new SortableMutantExecutionResults();
    }
    public function collect(MutantExecutionResult ...$executionResults) : void
    {
        foreach ($executionResults as $executionResult) {
            $this->allExecutionResults->add($executionResult);
            $detectionStatus = $executionResult->getDetectionStatus();
            if (!array_key_exists($detectionStatus, $this->resultsByStatus)) {
                throw new InvalidArgumentException(sprintf('Unknown execution result process result code "%s"', $detectionStatus));
            }
            $this->resultsByStatus[$detectionStatus]->add($executionResult);
        }
    }
    public function getAllExecutionResults() : array
    {
        return $this->allExecutionResults->getSortedExecutionResults();
    }
    public function getKilledExecutionResults() : array
    {
        return $this->getResultListForStatus(DetectionStatus::KILLED)->getSortedExecutionResults();
    }
    public function getErrorExecutionResults() : array
    {
        return $this->getResultListForStatus(DetectionStatus::ERROR)->getSortedExecutionResults();
    }
    public function getSyntaxErrorExecutionResults() : array
    {
        return $this->getResultListForStatus(DetectionStatus::SYNTAX_ERROR)->getSortedExecutionResults();
    }
    public function getSkippedExecutionResults() : array
    {
        return $this->getResultListForStatus(DetectionStatus::SKIPPED)->getSortedExecutionResults();
    }
    public function getEscapedExecutionResults() : array
    {
        return $this->getResultListForStatus(DetectionStatus::ESCAPED)->getSortedExecutionResults();
    }
    public function getTimedOutExecutionResults() : array
    {
        return $this->getResultListForStatus(DetectionStatus::TIMED_OUT)->getSortedExecutionResults();
    }
    public function getNotCoveredExecutionResults() : array
    {
        return $this->getResultListForStatus(DetectionStatus::NOT_COVERED)->getSortedExecutionResults();
    }
    public function getIgnoredExecutionResults() : array
    {
        return $this->getResultListForStatus(DetectionStatus::IGNORED)->getSortedExecutionResults();
    }
    private function getResultListForStatus(string $detectionStatus) : SortableMutantExecutionResults
    {
        return $this->resultsByStatus[$detectionStatus];
    }
}
