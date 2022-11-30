<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Metrics;

use function array_key_exists;
use _HumbugBox9658796bb9f0\Infection\Mutant\DetectionStatus;
use _HumbugBox9658796bb9f0\Infection\Mutant\MutantExecutionResult;
use InvalidArgumentException;
use function _HumbugBox9658796bb9f0\Safe\sprintf;
class MetricsCalculator implements Collector
{
    private array $countByStatus = [];
    private int $totalMutantsCount = 0;
    private ?Calculator $calculator = null;
    public function __construct(private int $roundingPrecision)
    {
        foreach (DetectionStatus::ALL as $status) {
            $this->countByStatus[$status] = 0;
        }
    }
    public function collect(MutantExecutionResult ...$executionResults) : void
    {
        if ($this->calculator !== null && $executionResults !== []) {
            $this->calculator = null;
        }
        foreach ($executionResults as $executionResult) {
            $detectionStatus = $executionResult->getDetectionStatus();
            if (!array_key_exists($detectionStatus, $this->countByStatus)) {
                throw new InvalidArgumentException(sprintf('Unknown execution result process result code "%s"', $executionResult->getDetectionStatus()));
            }
            ++$this->totalMutantsCount;
            ++$this->countByStatus[$detectionStatus];
        }
    }
    public function getRoundingPrecision() : int
    {
        return $this->roundingPrecision;
    }
    public function getKilledCount() : int
    {
        return $this->countByStatus[DetectionStatus::KILLED];
    }
    public function getErrorCount() : int
    {
        return $this->countByStatus[DetectionStatus::ERROR];
    }
    public function getSyntaxErrorCount() : int
    {
        return $this->countByStatus[DetectionStatus::SYNTAX_ERROR];
    }
    public function getSkippedCount() : int
    {
        return $this->countByStatus[DetectionStatus::SKIPPED];
    }
    public function getIgnoredCount() : int
    {
        return $this->countByStatus[DetectionStatus::IGNORED];
    }
    public function getEscapedCount() : int
    {
        return $this->countByStatus[DetectionStatus::ESCAPED];
    }
    public function getTimedOutCount() : int
    {
        return $this->countByStatus[DetectionStatus::TIMED_OUT];
    }
    public function getNotTestedCount() : int
    {
        return $this->countByStatus[DetectionStatus::NOT_COVERED];
    }
    public function getTotalMutantsCount() : int
    {
        return $this->totalMutantsCount;
    }
    public function getTestedMutantsCount() : int
    {
        return $this->getTotalMutantsCount() - $this->getSkippedCount() - $this->getIgnoredCount();
    }
    public function getMutationScoreIndicator() : float
    {
        return $this->getCalculator()->getMutationScoreIndicator();
    }
    public function getCoverageRate() : float
    {
        return $this->getCalculator()->getCoverageRate();
    }
    public function getCoveredCodeMutationScoreIndicator() : float
    {
        return $this->getCalculator()->getCoveredCodeMutationScoreIndicator();
    }
    private function getCalculator() : Calculator
    {
        return $this->calculator ?? ($this->calculator = Calculator::fromMetrics($this));
    }
}
