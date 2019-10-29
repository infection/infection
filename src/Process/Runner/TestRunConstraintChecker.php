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

namespace Infection\Process\Runner;

use Infection\Mutant\MetricsCalculator;

/**
 * @internal
 */
final class TestRunConstraintChecker
{
    public const MSI_FAILURE = 'min-msi';

    public const COVERED_MSI_FAILURE = 'min-covered-msi';

    /**
     * @var MetricsCalculator
     */
    private $metricsCalculator;

    /**
     * @var bool
     */
    private $ignoreMsiWithNoMutations;

    /**
     * @var float
     */
    private $minMsi;

    /**
     * @var float
     */
    private $minCoveredMsi;

    /**
     * @var string
     */
    private $failureType = '';

    public function __construct(
        MetricsCalculator $metricsCalculator,
        bool $ignoreMsiWithNoMutations,
        float $minMsi,
        float $minCoveredMsi
    ) {
        $this->metricsCalculator = $metricsCalculator;
        $this->ignoreMsiWithNoMutations = $ignoreMsiWithNoMutations;
        $this->minMsi = $minMsi;
        $this->minCoveredMsi = $minCoveredMsi;
    }

    public function hasTestRunPassedConstraints(): bool
    {
        if ($this->ignoreMsiWithNoMutations && $this->metricsCalculator->getTotalMutantsCount() === 0) {
            return true;
        }

        if ($this->hasBadMsi()) {
            $this->failureType = self::MSI_FAILURE;

            return false;
        }

        if ($this->hasBadCoveredMsi()) {
            $this->failureType = self::COVERED_MSI_FAILURE;

            return false;
        }

        return true;
    }

    public function isMsiOverMinimumMsi(string $type): bool
    {
        $msi = ($type === TestRunConstraintChecker::MSI_FAILURE)
            ? $this->metricsCalculator->getMutationScoreIndicator()
            : $this->metricsCalculator->getCoveredCodeMutationScoreIndicator();

        $min = ($type === TestRunConstraintChecker::MSI_FAILURE) ? $this->minMsi : $this->minCoveredMsi;

        return $min && ($msi > $min);
    }

    public function getErrorType(): string
    {
        return $this->failureType;
    }

    public function getMinRequiredValue(): float
    {
        return $this->failureType === self::MSI_FAILURE ? $this->minMsi : $this->minCoveredMsi;
    }

    private function hasBadMsi(): bool
    {
        return $this->minMsi && ($this->metricsCalculator->getMutationScoreIndicator() < $this->minMsi);
    }

    private function hasBadCoveredMsi(): bool
    {
        return $this->minCoveredMsi && ($this->metricsCalculator->getCoveredCodeMutationScoreIndicator() < $this->minCoveredMsi);
    }
}
