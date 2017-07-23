<?php
/**
 * Copyright © 2017 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types = 1);

namespace Infection\Mutant;

use Infection\Process\MutantProcess;
use Infection\TestFramework\AbstractTestFrameworkAdapter;

class MetricsCalculator
{
    /**
     * @var int
     */
    private $killedCount = 0;

    /**
     * @var MutantProcess[]
     */
    private $killedMutantProcesses = [];

    /**
     * @var int
     */
    private $escapedCount = 0;

    /**
     * @var MutantProcess[]
     */
    private $escapedMutantProcesses = [];

    /**
     * @var int
     */
    private $timedOutCount = 0;


    /**
     * @var MutantProcess[]
     */
    private $timedOutProcesses = [];

    /**
     * @var int
     */
    private $notCoveredByTestsCount = 0;

    /**
     * @var int
     */
    private $totalMutantsCount = 0;

    /**
     * @var AbstractTestFrameworkAdapter
     */
    private $testFrameworkAdapter;

    /**
     * MetricsCalculator constructor.
     * @param AbstractTestFrameworkAdapter $testFrameworkAdapter
     */
    public function __construct(AbstractTestFrameworkAdapter $testFrameworkAdapter)
    {
        $this->testFrameworkAdapter = $testFrameworkAdapter;
    }

    public function collect(MutantProcess $mutantProcess)
    {
        $this->totalMutantsCount++;

        switch ($mutantProcess->getResultCode()) {
            case MutantProcess::CODE_KILLED:
                $this->killedCount++;
                $this->killedMutantProcesses[] = $mutantProcess;
                break;
            case MutantProcess::CODE_NOT_COVERED:
                $this->notCoveredByTestsCount++;
                break;
            case MutantProcess::CODE_ESCAPED:
                $this->escapedCount++;
                $this->escapedMutantProcesses[] = $mutantProcess;
                break;
            case MutantProcess::CODE_TIMED_OUT:
                $this->timedOutCount++;
                $this->timedOutProcesses[] = $mutantProcess;
                break;
        }
    }

    /**
     * Mutation Score Indicator (MSI)
     * @return float
     */
    public function getMutationScoreIndicator(): float
    {
        $detectionRateAll = 0;
        $defeatedTotal = $this->killedCount + $this->timedOutCount/* + $errorCount*/;

        if ($this->totalMutantsCount) {
            $detectionRateAll = round(100 * ($defeatedTotal / $this->totalMutantsCount));
        }

        return $detectionRateAll;
    }

    /**
     * Mutation coverage percentage
     *
     * @return float
     */
    public function getCoverageRate(): float
    {
        $coveredRate = 0;
        $coveredByTestsTotal = $this->totalMutantsCount - $this->notCoveredByTestsCount;

        if ($this->totalMutantsCount) {
            $coveredRate = round(100 * ($coveredByTestsTotal / $this->totalMutantsCount));
        }

        return $coveredRate;
    }

    public function getCoveredCodeMutationScoreIndicator(): float
    {
        $detectionRateTested = 0;
        $coveredByTestsTotal = $this->totalMutantsCount - $this->notCoveredByTestsCount;
        $defeatedTotal = $this->killedCount + $this->timedOutCount/* + $errorCount*/;

        if ($coveredByTestsTotal) {
            $detectionRateTested = round(100 * ($defeatedTotal / $coveredByTestsTotal));
        }

        return $detectionRateTested;
    }

    /**
     * @return int
     */
    public function getKilledCount(): int
    {
        return $this->killedCount;
    }

    /**
     * @return int
     */
    public function getEscapedCount(): int
    {
        return $this->escapedCount;
    }

    /**
     * @return int
     */
    public function getTimedOutCount(): int
    {
        return $this->timedOutCount;
    }

    /**
     * @return int
     */
    public function getNotCoveredByTestsCount(): int
    {
        return $this->notCoveredByTestsCount;
    }

    /**
     * @return int
     */
    public function getTotalMutantsCount(): int
    {
        return $this->totalMutantsCount;
    }

    /**
     * @return MutantProcess[]
     */
    public function getEscapedMutantProcesses(): array
    {
        return $this->escapedMutantProcesses;
    }

    /**
     * @return MutantProcess[]
     */
    public function getKilledMutantProcesses(): array
    {
        return $this->killedMutantProcesses;
    }

    /**
     * @return MutantProcess[]
     */
    public function getTimedOutProcesses(): array
    {
        return $this->timedOutProcesses;
    }
}