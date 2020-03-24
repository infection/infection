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

namespace Infection\Metrics;

use function count;
use Infection\Mutant\DetectionStatus;
use Infection\Mutant\MutantExecutionResult;
use InvalidArgumentException;
use function Safe\sprintf;

/**
 * @internal
 */
class MetricsCalculator
{
    private $roundingPrecision;
    private $killedExecutionResults;
    private $errorExecutionResults;
    private $escapedExecutionResults;
    private $timedOutExecutionResults;
    private $notCoveredExecutionResults;
    private $allExecutionResults;

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
     * @var Calculator|null
     */
    private $calculator;

    public function __construct(int $roundingPrecision)
    {
        $this->roundingPrecision = $roundingPrecision;
        $this->killedExecutionResults = new SortableMutantExecutionResults();
        $this->errorExecutionResults = new SortableMutantExecutionResults();
        $this->escapedExecutionResults = new SortableMutantExecutionResults();
        $this->timedOutExecutionResults = new SortableMutantExecutionResults();
        $this->notCoveredExecutionResults = new SortableMutantExecutionResults();
        $this->allExecutionResults = new SortableMutantExecutionResults();
    }

    public function collect(MutantExecutionResult ...$executionResults): void
    {
        if (count($executionResults) > 0) {
            // Reset the calculator if any result is added
            $this->calculator = null;
        }

        foreach ($executionResults as $executionResult) {
            ++$this->totalMutantsCount;
            $this->allExecutionResults->add($executionResult);

            switch ($executionResult->getDetectionStatus()) {
                case DetectionStatus::KILLED:
                    $this->killedCount++;
                    $this->killedExecutionResults->add($executionResult);

                    break;

                case DetectionStatus::NOT_COVERED:
                    $this->notCoveredByTestsCount++;
                    $this->notCoveredExecutionResults->add($executionResult);

                    break;

                case DetectionStatus::ESCAPED:
                    $this->escapedCount++;
                    $this->escapedExecutionResults->add($executionResult);

                    break;

                case DetectionStatus::TIMED_OUT:
                    $this->timedOutCount++;
                    $this->timedOutExecutionResults->add($executionResult);

                    break;

                case DetectionStatus::ERROR:
                    $this->errorCount++;
                    $this->errorExecutionResults->add($executionResult);

                    break;

                default:
                    throw new InvalidArgumentException(sprintf(
                        'Unknown execution result process result code "%s"',
                        $executionResult->getDetectionStatus()
                    ));
            }
        }
    }

    public function getRoundingPrecision(): int
    {
        return $this->roundingPrecision;
    }

    /**
     * @return MutantExecutionResult[]
     */
    public function getKilledExecutionResults(): array
    {
        return $this->killedExecutionResults->getSortedExecutionResults();
    }

    /**
     * @return MutantExecutionResult[]
     */
    public function getErrorExecutionResults(): array
    {
        return $this->errorExecutionResults->getSortedExecutionResults();
    }

    /**
     * @return MutantExecutionResult[]
     */
    public function getEscapedExecutionResults(): array
    {
        return $this->escapedExecutionResults->getSortedExecutionResults();
    }

    /**
     * @return MutantExecutionResult[]
     */
    public function getTimedOutExecutionResults(): array
    {
        return $this->timedOutExecutionResults->getSortedExecutionResults();
    }

    /**
     * @return MutantExecutionResult[]
     */
    public function getNotCoveredExecutionResults(): array
    {
        return $this->notCoveredExecutionResults->getSortedExecutionResults();
    }

    /**
     * @return MutantExecutionResult[]
     */
    public function getAllExecutionResults(): array
    {
        return $this->allExecutionResults->getSortedExecutionResults();
    }

    public function getKilledCount(): int
    {
        return $this->killedCount;
    }

    public function getErrorCount(): int
    {
        return $this->errorCount;
    }

    public function getEscapedCount(): int
    {
        return $this->escapedCount;
    }

    public function getTimedOutCount(): int
    {
        return $this->timedOutCount;
    }

    public function getNotTestedCount(): int
    {
        return $this->notCoveredByTestsCount;
    }

    public function getTotalMutantsCount(): int
    {
        return $this->totalMutantsCount;
    }

    /**
     * Mutation Score Indicator (MSI)
     */
    public function getMutationScoreIndicator(): float
    {
        return $this->getCalculator()->getMutationScoreIndicator();
    }

    /**
     * Mutation coverage percentage
     */
    public function getCoverageRate(): float
    {
        return $this->getCalculator()->getCoverageRate();
    }

    /**
     * Mutation Score Indicator relative to the covered mutants
     */
    public function getCoveredCodeMutationScoreIndicator(): float
    {
        return $this->getCalculator()->getCoveredCodeMutationScoreIndicator();
    }

    private function getCalculator(): Calculator
    {
        return $this->calculator ?? $this->calculator = Calculator::fromMetrics($this);
    }
}
