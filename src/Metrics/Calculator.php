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

use const PHP_ROUND_HALF_UP;
use function round;

/**
 * @internal
 */
final class Calculator
{
    private ?float $mutationScoreIndicator = null;

    private ?float $coverageRate = null;

    private ?float $coveredMutationScoreIndicator = null;

    public function __construct(private readonly int $roundingPrecision, private readonly int $killedCount, private readonly int $errorCount, private readonly int $timedOutCount, private readonly int $notTestedCount, private readonly int $totalCount)
    {
    }

    public static function fromMetrics(MetricsCalculator $calculator): self
    {
        return new self(
            $calculator->getRoundingPrecision(),
            $calculator->getKilledCount(),
            $calculator->getErrorCount() + $calculator->getSyntaxErrorCount(),
            $calculator->getTimedOutCount(),
            $calculator->getNotTestedCount(),
            $calculator->getTestedMutantsCount(),
        );
    }

    /**
     * Mutation Score Indicator (MSI)
     */
    public function getMutationScoreIndicator(): float
    {
        if ($this->mutationScoreIndicator !== null) {
            return $this->mutationScoreIndicator;
        }

        $score = 0.;
        $coveredTotal = $this->killedCount + $this->timedOutCount + $this->errorCount;
        $totalCount = $this->totalCount;

        if ($totalCount !== 0) {
            $score = 100 * $coveredTotal / $totalCount;
        }

        return $this->mutationScoreIndicator = $this->round($score);
    }

    /**
     * Mutation coverage percentage
     */
    public function getCoverageRate(): float
    {
        if ($this->coverageRate !== null) {
            return $this->coverageRate;
        }

        $coveredRate = 0.;
        $totalCount = $this->totalCount;
        $testedTotal = $totalCount - $this->notTestedCount;

        if ($totalCount !== 0) {
            $coveredRate = 100 * $testedTotal / $totalCount;
        }

        return $this->coverageRate = $this->round($coveredRate);
    }

    /**
     * Mutation Score Indicator relative to the covered mutants
     */
    public function getCoveredCodeMutationScoreIndicator(): float
    {
        if ($this->coveredMutationScoreIndicator !== null) {
            return $this->coveredMutationScoreIndicator;
        }

        $score = 0.;
        $testedTotal = $this->totalCount - $this->notTestedCount;
        $coveredTotal = $this->killedCount + $this->timedOutCount + $this->errorCount;

        if ($testedTotal !== 0) {
            $score = 100 * $coveredTotal / $testedTotal;
        }

        return $this->coveredMutationScoreIndicator = $this->round($score);
    }

    private function round(float $value): float
    {
        return round($value, $this->roundingPrecision, PHP_ROUND_HALF_UP);
    }
}
