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
use InvalidArgumentException;
use function Safe\sprintf;

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
    private $killedExecutionResults;
    private $errorExecutionResults;
    private $escapedExecutionResults;
    private $timedOutExecutionResults;
    private $notCoveredExecutionResults;
    private $allExecutionResults;

    /**
     * @var Calculator|null
     */
    private $calculator;

    /**
     * @var bool
     */
    private $treatTimeoutsAsEscapes = false;

    public function __construct(bool $treatTimeoutsAsEscapes = false)
    {
        $this->killedExecutionResults = new SortableMutantExecutionResults();
        $this->errorExecutionResults = new SortableMutantExecutionResults();
        $this->escapedExecutionResults = new SortableMutantExecutionResults();
        $this->timedOutExecutionResults = new SortableMutantExecutionResults();
        $this->notCoveredExecutionResults = new SortableMutantExecutionResults();
        $this->allExecutionResults = new SortableMutantExecutionResults();
        $this->treatTimeoutsAsEscapes = $treatTimeoutsAsEscapes;
    }

    public function collect(MutantExecutionResult ...$executionResults): void
    {
        foreach ($executionResults as $executionResult) {
            ++$this->totalMutantsCount;
            $this->allExecutionResults->add($executionResult);

            switch ($executionResult->getProcessResultCode()) {
                case MutantProcess::CODE_KILLED:
                    $this->killedCount++;
                    $this->killedExecutionResults->add($executionResult);

                    break;

                case MutantProcess::CODE_NOT_COVERED:
                    $this->notCoveredByTestsCount++;
                    $this->notCoveredExecutionResults->add($executionResult);

                    break;

                case MutantProcess::CODE_ESCAPED:
                    $this->escapedCount++;
                    $this->escapedExecutionResults->add($executionResult);

                    break;

                case MutantProcess::CODE_TIMED_OUT:
                    $this->timedOutCount++;
                    $this->timedOutExecutionResults->add($executionResult);

                    break;

                case MutantProcess::CODE_ERROR:
                    $this->errorCount++;
                    $this->errorExecutionResults->add($executionResult);

                    break;

                default:
                    throw new InvalidArgumentException(sprintf(
                        'Unknown execution result process result code "%s"',
                        $executionResult->getProcessResultCode()
                    ));
            }
        }
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

    /**
     * Are mutation timeouts treated as escapes?
     */
    public function getTreatTimeoutsAsEscapes(): bool
    {
        return $this->treatTimeoutsAsEscapes;
    }

    private function getCalculator(): Calculator
    {
        return $this->calculator ?? Calculator::fromMetrics($this);
    }
}
