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

use function array_key_exists;
use Infection\Mutant\DetectionStatus;
use Infection\Mutant\MutantExecutionResult;
use InvalidArgumentException;
use function sprintf;

/**
 * @internal
 * @final
 */
class ResultsCollector implements Collector
{
    /**
     * @var array<string, SortableMutantExecutionResults>
     */
    private array $resultsByStatus = [];

    private readonly SortableMutantExecutionResults $allExecutionResults;

    public function __construct()
    {
        foreach (DetectionStatus::ALL as $status) {
            $this->resultsByStatus[$status] = new SortableMutantExecutionResults();
        }

        $this->allExecutionResults = new SortableMutantExecutionResults();
    }

    public function collect(MutantExecutionResult ...$executionResults): void
    {
        foreach ($executionResults as $executionResult) {
            $this->allExecutionResults->add($executionResult);

            $detectionStatus = $executionResult->getDetectionStatus();

            if (!array_key_exists($detectionStatus, $this->resultsByStatus)) {
                throw new InvalidArgumentException(sprintf(
                    'Unknown execution result process result code "%s"',
                    $detectionStatus,
                ));
            }

            $this->resultsByStatus[$detectionStatus]->add($executionResult);
        }
    }

    /**
     * @return MutantExecutionResult[]
     */
    public function getAllExecutionResults(): array
    {
        return $this->allExecutionResults->getSortedExecutionResults();
    }

    /**
     * @return MutantExecutionResult[]
     */
    public function getKilledExecutionResults(): array
    {
        return $this->getResultListForStatus(DetectionStatus::KILLED)->getSortedExecutionResults();
    }

    /**
     * @return MutantExecutionResult[]
     */
    public function getErrorExecutionResults(): array
    {
        return $this->getResultListForStatus(DetectionStatus::ERROR)->getSortedExecutionResults();
    }

    /**
     * @return MutantExecutionResult[]
     */
    public function getSyntaxErrorExecutionResults(): array
    {
        return $this->getResultListForStatus(DetectionStatus::SYNTAX_ERROR)->getSortedExecutionResults();
    }

    /**
     * @return MutantExecutionResult[]
     */
    public function getSkippedExecutionResults(): array
    {
        return $this->getResultListForStatus(DetectionStatus::SKIPPED)->getSortedExecutionResults();
    }

    /**
     * @return MutantExecutionResult[]
     */
    public function getEscapedExecutionResults(): array
    {
        return $this->getResultListForStatus(DetectionStatus::ESCAPED)->getSortedExecutionResults();
    }

    /**
     * @return MutantExecutionResult[]
     */
    public function getTimedOutExecutionResults(): array
    {
        return $this->getResultListForStatus(DetectionStatus::TIMED_OUT)->getSortedExecutionResults();
    }

    /**
     * @return MutantExecutionResult[]
     */
    public function getNotCoveredExecutionResults(): array
    {
        return $this->getResultListForStatus(DetectionStatus::NOT_COVERED)->getSortedExecutionResults();
    }

    /**
     * @return MutantExecutionResult[]
     */
    public function getIgnoredExecutionResults(): array
    {
        return $this->getResultListForStatus(DetectionStatus::IGNORED)->getSortedExecutionResults();
    }

    private function getResultListForStatus(string $detectionStatus): SortableMutantExecutionResults
    {
        return $this->resultsByStatus[$detectionStatus];
    }
}
