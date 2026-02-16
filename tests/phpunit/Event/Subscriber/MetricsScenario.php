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

namespace Infection\Tests\Event\Subscriber;

final class MetricsScenario
{
    public function __construct(
        public bool $withUncovered,
        public bool $timeoutsAsEscaped,
        public int $killedByTestsCount,
        public int $killedByStaticAnalysisCount,
        public int $errorCount,
        public int $syntaxErrorCount,
        public int $skippedCount,
        public int $ignoredCount,
        public int $escapedCount,
        public int $timedOutCount,
        public int $notTestedCount,
        public int $totalMutantsCount,
        public float $mutationScoreIndicator,
        public float $coverageRate,
        public float $coveredCodeMutationScoreIndicator,
        public string $expected,
    ) {
    }

    public function withUncovered(bool $withUncovered): self
    {
        $clone = clone $this;
        $clone->withUncovered = $withUncovered;

        return $clone;
    }

    public function withTimeoutsAsEscaped(bool $timeoutsAsEscaped): self
    {
        $clone = clone $this;
        $clone->timeoutsAsEscaped = $timeoutsAsEscaped;

        return $clone;
    }

    public function withKilledByTestsCount(int $killedByTestsCount): self
    {
        $clone = clone $this;
        $clone->killedByTestsCount = $killedByTestsCount;

        return $clone;
    }

    public function withKilledByIgnoredCount(int $killedByIgnoredCount): self
    {
        $clone = clone $this;
        $clone->ignoredCount = $killedByIgnoredCount;

        return $clone;
    }

    public function withTotalMutantsCount(int $totalMutantsCount): self
    {
        $clone = clone $this;
        $clone->totalMutantsCount = $totalMutantsCount;

        return $clone;
    }

    public function withMutationScoreIndicator(float $mutationScoreIndicator): self
    {
        $clone = clone $this;
        $clone->mutationScoreIndicator = $mutationScoreIndicator;

        return $clone;
    }

    public function withCoverageRate(float $coverageRate): self
    {
        $clone = clone $this;
        $clone->coverageRate = $coverageRate;

        return $clone;
    }

    public function withCoveredCodeMutationScoreIndicator(float $coveredCodeMutationScoreIndicator): self
    {
        $clone = clone $this;
        $clone->coveredCodeMutationScoreIndicator = $coveredCodeMutationScoreIndicator;

        return $clone;
    }

    public function withExpected(string $expected): self
    {
        $clone = clone $this;
        $clone->expected = $expected;

        return $clone;
    }

    /**
     * @return array{self}
     */
    public function build(): array
    {
        return [$this];
    }
}
