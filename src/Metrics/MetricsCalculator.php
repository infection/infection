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
use function is_nan;
use Pipeline\Helper\RunningVariance;
use function sprintf;

/**
 * @internal
 */
class MetricsCalculator implements Collector
{
    /**
     * @var array<string, int>
     */
    private array $countByStatus = [];

    private int $totalMutantsCount = 0;

    private ?Calculator $calculator = null;

    private readonly RunningVariance $testRuntimesVariance;

    private readonly RunningVariance $staticAnalysisRuntimesVariance;

    public function __construct(
        private readonly int $roundingPrecision,
    ) {
        foreach (DetectionStatus::ALL as $status) {
            $this->countByStatus[$status] = 0;
        }

        $this->testRuntimesVariance = new RunningVariance();
        $this->staticAnalysisRuntimesVariance = new RunningVariance();
    }

    public function collect(MutantExecutionResult ...$executionResults): void
    {
        if ($this->calculator !== null && $executionResults !== []) {
            // Reset the calculator if any result is added
            $this->calculator = null;
        }

        foreach ($executionResults as $executionResult) {
            $detectionStatus = $executionResult->getDetectionStatus();

            if (!array_key_exists($detectionStatus, $this->countByStatus)) {
                throw new InvalidArgumentException(sprintf(
                    'Unknown execution result process result code "%s"',
                    $executionResult->getDetectionStatus(),
                ));
            }

            ++$this->totalMutantsCount;
            ++$this->countByStatus[$detectionStatus];

            if ($detectionStatus === DetectionStatus::KILLED_BY_TESTS) {
                $this->testRuntimesVariance->observe($executionResult->getProcessRuntime());
            }

            if ($detectionStatus === DetectionStatus::KILLED_BY_STATIC_ANALYSIS) {
                $this->staticAnalysisRuntimesVariance->observe($executionResult->getProcessRuntime());
            }
        }
    }

    public function getRoundingPrecision(): int
    {
        return $this->roundingPrecision;
    }

    public function getKilledByTestsCount(): int
    {
        return $this->countByStatus[DetectionStatus::KILLED_BY_TESTS];
    }

    public function getKilledByStaticAnalysisCount(): int
    {
        return $this->countByStatus[DetectionStatus::KILLED_BY_STATIC_ANALYSIS];
    }

    public function getErrorCount(): int
    {
        return $this->countByStatus[DetectionStatus::ERROR];
    }

    public function getSyntaxErrorCount(): int
    {
        return $this->countByStatus[DetectionStatus::SYNTAX_ERROR];
    }

    public function getSkippedCount(): int
    {
        return $this->countByStatus[DetectionStatus::SKIPPED];
    }

    public function getIgnoredCount(): int
    {
        return $this->countByStatus[DetectionStatus::IGNORED];
    }

    public function getEscapedCount(): int
    {
        return $this->countByStatus[DetectionStatus::ESCAPED];
    }

    public function getTimedOutCount(): int
    {
        return $this->countByStatus[DetectionStatus::TIMED_OUT];
    }

    public function getNotTestedCount(): int
    {
        return $this->countByStatus[DetectionStatus::NOT_COVERED];
    }

    public function getTotalMutantsCount(): int
    {
        return $this->totalMutantsCount;
    }

    public function getTestedMutantsCount(): int
    {
        return $this->getTotalMutantsCount() - $this->getSkippedCount() - $this->getIgnoredCount();
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

    public function getTestsMinimumRuntime(): float
    {
        return self::foldToZero($this->testRuntimesVariance->getMin());
    }

    public function getTestsAverageRuntime(): float
    {
        return self::foldToZero($this->testRuntimesVariance->getMean());
    }

    public function getTestsMaximumRuntime(): float
    {
        return self::foldToZero($this->testRuntimesVariance->getMax());
    }

    public function getStaticAnalysisMinimumRuntime(): float
    {
        return self::foldToZero($this->staticAnalysisRuntimesVariance->getMin());
    }

    public function getStaticAnalysisAverageRuntime(): float
    {
        return self::foldToZero($this->staticAnalysisRuntimesVariance->getMean());
    }

    public function getStaticAnalysisMaximumRuntime(): float
    {
        return self::foldToZero($this->staticAnalysisRuntimesVariance->getMax());
    }

    private function getCalculator(): Calculator
    {
        return $this->calculator ??= Calculator::fromMetrics($this);
    }

    private static function foldToZero(float $value): float
    {
        return is_nan($value) ? 0.0 : $value;
    }
}
