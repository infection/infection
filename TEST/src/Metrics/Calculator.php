<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Metrics;

use const PHP_ROUND_HALF_UP;
use function round;
final class Calculator
{
    private ?float $mutationScoreIndicator = null;
    private ?float $coverageRate = null;
    private ?float $coveredMutationScoreIndicator = null;
    public function __construct(private int $roundingPrecision, private int $killedCount, private int $errorCount, private int $timedOutCount, private int $notTestedCount, private int $totalCount)
    {
    }
    public static function fromMetrics(MetricsCalculator $calculator) : self
    {
        return new self($calculator->getRoundingPrecision(), $calculator->getKilledCount(), $calculator->getErrorCount() + $calculator->getSyntaxErrorCount(), $calculator->getTimedOutCount(), $calculator->getNotTestedCount(), $calculator->getTestedMutantsCount());
    }
    public function getMutationScoreIndicator() : float
    {
        if ($this->mutationScoreIndicator !== null) {
            return $this->mutationScoreIndicator;
        }
        $score = 0.0;
        $coveredTotal = $this->killedCount + $this->timedOutCount + $this->errorCount;
        $totalCount = $this->totalCount;
        if ($totalCount !== 0) {
            $score = 100 * $coveredTotal / $totalCount;
        }
        return $this->mutationScoreIndicator = $this->round($score);
    }
    public function getCoverageRate() : float
    {
        if ($this->coverageRate !== null) {
            return $this->coverageRate;
        }
        $coveredRate = 0.0;
        $totalCount = $this->totalCount;
        $testedTotal = $totalCount - $this->notTestedCount;
        if ($totalCount !== 0) {
            $coveredRate = 100 * $testedTotal / $totalCount;
        }
        return $this->coverageRate = $this->round($coveredRate);
    }
    public function getCoveredCodeMutationScoreIndicator() : float
    {
        if ($this->coveredMutationScoreIndicator !== null) {
            return $this->coveredMutationScoreIndicator;
        }
        $score = 0.0;
        $testedTotal = $this->totalCount - $this->notTestedCount;
        $coveredTotal = $this->killedCount + $this->timedOutCount + $this->errorCount;
        if ($testedTotal !== 0) {
            $score = 100 * $coveredTotal / $testedTotal;
        }
        return $this->coveredMutationScoreIndicator = $this->round($score);
    }
    private function round(float $value) : float
    {
        return round($value, $this->roundingPrecision, PHP_ROUND_HALF_UP);
    }
}
