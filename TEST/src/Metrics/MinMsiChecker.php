<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Metrics;

use _HumbugBox9658796bb9f0\Infection\Console\ConsoleOutput;
class MinMsiChecker
{
    private const VALUE_OVER_REQUIRED_TOLERANCE = 2;
    public function __construct(private bool $ignoreMsiWithNoMutations, private float $minMsi, private float $minCoveredCodeMsi)
    {
    }
    public function checkMetrics(int $totalMutantCount, float $msi, float $coveredCodeMsi, ConsoleOutput $consoleOutput) : void
    {
        $this->checkMinMsi($totalMutantCount, $msi, $coveredCodeMsi);
        $this->checkIfMinMsiCanBeIncreased($msi, $coveredCodeMsi, $consoleOutput);
    }
    private function checkMinMsi(int $totalMutantCount, float $msi, float $coveredCodeMsi) : void
    {
        if ($this->ignoreMsiWithNoMutations && $totalMutantCount === 0) {
            return;
        }
        if ($this->isMsiInsufficient($msi)) {
            throw MinMsiCheckFailed::createForMsi($this->minMsi, $msi);
        }
        if ($this->isCoveredCodeMsiInsufficient($coveredCodeMsi)) {
            throw MinMsiCheckFailed::createCoveredMsi($this->minCoveredCodeMsi, $coveredCodeMsi);
        }
    }
    private function checkIfMinMsiCanBeIncreased(float $msi, float $coveredCodeMsi, ConsoleOutput $output) : void
    {
        if ($this->canIncreaseMsi($msi)) {
            $output->logMinMsiCanGetIncreasedNotice($this->minMsi, $msi);
        }
        if ($this->canIncreaseCoveredCodeMsi($coveredCodeMsi)) {
            $output->logMinCoveredCodeMsiCanGetIncreasedNotice($this->minCoveredCodeMsi, $coveredCodeMsi);
        }
    }
    private function isMsiInsufficient(float $msi) : bool
    {
        return $this->minMsi > 0 && $msi < $this->minMsi;
    }
    private function isCoveredCodeMsiInsufficient(float $coveredCodeMsi) : bool
    {
        return $this->minCoveredCodeMsi > 0 && $coveredCodeMsi < $this->minCoveredCodeMsi;
    }
    private function canIncreaseMsi(float $msi) : bool
    {
        if ($this->minMsi === 0.0) {
            return \false;
        }
        return $msi > $this->minMsi + self::VALUE_OVER_REQUIRED_TOLERANCE;
    }
    private function canIncreaseCoveredCodeMsi(float $coveredCodeMsi) : bool
    {
        if ($this->minCoveredCodeMsi === 0.0) {
            return \false;
        }
        return $coveredCodeMsi > $this->minCoveredCodeMsi + self::VALUE_OVER_REQUIRED_TOLERANCE;
    }
}
