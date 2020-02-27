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

namespace Infection\Mutant;

use Infection\Process\MutantProcess;

/**
 * @internal
 */
class MetricsCalculator
{
    /**
     * @var int
     */
    private $killedCount = 0;

    /**
     * @var int
     */
    private $errorCount = 0;

    /**
     * @var int
     */
    private $escapedCount = 0;

    /**
     * @var int
     */
    private $timedOutCount = 0;

    /**
     * @var int
     */
    private $notCoveredByTestsCount = 0;

    /**
     * @var int
     */
    private $totalMutantsCount = 0;

    /**
     * @var MutantExecutionResult[]
     */
    private $killedMutantExecutionResults = [];

    /**
     * @var MutantExecutionResult[]
     */
    private $errorMutantExecutionResults = [];

    /**
     * @var MutantExecutionResult[]
     */
    private $escapedMutantExecutionResults = [];

    /**
     * @var MutantExecutionResult[]
     */
    private $timedOutMutantExecutionResults = [];

    /**
     * @var MutantExecutionResult[]
     */
    private $notCoveredMutantExecutionResults = [];

    /**
     * Build a metric calculator with a sub-set of mutators
     *
     * @param MutantExecutionResult[] $executionResults
     *
     * @return MetricsCalculator
     */
    public static function createFromArray(array $executionResults): self
    {
        $self = new self();

        foreach ($executionResults as $process) {
            $self->collect($process);
        }

        return $self;
    }

    public function collect(MutantExecutionResult $executionResult): void
    {
        ++$this->totalMutantsCount;

        switch ($executionResult->getProcessResultCode()) {
            case MutantProcess::CODE_KILLED:
                $this->killedCount++;
                $this->killedMutantExecutionResults[] = $executionResult;

                break;
            case MutantProcess::CODE_NOT_COVERED:
                $this->notCoveredByTestsCount++;
                $this->notCoveredMutantExecutionResults[] = $executionResult;

                break;
            case MutantProcess::CODE_ESCAPED:
                $this->escapedCount++;
                $this->escapedMutantExecutionResults[] = $executionResult;

                break;
            case MutantProcess::CODE_TIMED_OUT:
                $this->timedOutCount++;
                $this->timedOutMutantExecutionResults[] = $executionResult;

                break;
            case MutantProcess::CODE_ERROR:
                $this->errorCount++;
                $this->errorMutantExecutionResults[] = $executionResult;

                break;
        }
    }

    /**
     * Mutation Score Indicator (MSI)
     */
    public function getMutationScoreIndicator(): float
    {
        $detectionRateAll = 0;
        $defeatedTotal = $this->killedCount + $this->timedOutCount + $this->errorCount;

        if ($this->totalMutantsCount) {
            $detectionRateAll = 100 * $defeatedTotal / $this->totalMutantsCount;
        }

        return $detectionRateAll;
    }

    /**
     * Mutation coverage percentage
     */
    public function getCoverageRate(): float
    {
        $coveredRate = 0;
        $coveredByTestsTotal = $this->totalMutantsCount - $this->notCoveredByTestsCount;

        if ($this->totalMutantsCount) {
            $coveredRate = 100 * $coveredByTestsTotal / $this->totalMutantsCount;
        }

        return $coveredRate;
    }

    public function getCoveredCodeMutationScoreIndicator(): float
    {
        $detectionRateTested = 0;
        $coveredByTestsTotal = $this->totalMutantsCount - $this->notCoveredByTestsCount;
        $defeatedTotal = $this->killedCount + $this->timedOutCount + $this->errorCount;

        if ($coveredByTestsTotal) {
            $detectionRateTested = 100 * $defeatedTotal / $coveredByTestsTotal;
        }

        return $detectionRateTested;
    }

    public function getKilledCount(): int
    {
        return $this->killedCount;
    }

    public function getEscapedCount(): int
    {
        return $this->escapedCount;
    }

    public function getTimedOutCount(): int
    {
        return $this->timedOutCount;
    }

    public function getNotCoveredByTestsCount(): int
    {
        return $this->notCoveredByTestsCount;
    }

    public function getTotalMutantsCount(): int
    {
        return $this->totalMutantsCount;
    }

    /**
     * @return MutantExecutionResult[]
     */
    public function getEscapedMutantExecutionResults(): array
    {
        return $this->escapedMutantExecutionResults;
    }

    /**
     * @return MutantExecutionResult[]
     */
    public function getKilledMutantExecutionResults(): array
    {
        return $this->killedMutantExecutionResults;
    }

    /**
     * @return MutantExecutionResult[]
     */
    public function getTimedOutMutantExecutionResults(): array
    {
        return $this->timedOutMutantExecutionResults;
    }

    /**
     * @return MutantExecutionResult[]
     */
    public function getNotCoveredMutantExecutionResults(): array
    {
        return $this->notCoveredMutantExecutionResults;
    }

    /**
     * @return MutantExecutionResult[]
     */
    public function getAllMutantExecutionResults(): array
    {
        return array_merge(
            $this->escapedMutantExecutionResults,
            $this->killedMutantExecutionResults,
            $this->timedOutMutantExecutionResults,
            $this->notCoveredMutantExecutionResults
        );
    }

    public function getErrorCount(): int
    {
        return $this->errorCount;
    }

    /**
     * @return MutantExecutionResult[]
     */
    public function getErrorMutantExecutionResults(): array
    {
        return $this->errorMutantExecutionResults;
    }
}
