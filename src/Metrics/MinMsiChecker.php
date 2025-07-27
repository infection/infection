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

use Infection\Console\ConsoleOutput;
use function var_dump;

/**
 * @internal
 * @final
 */
class MinMsiChecker
{
    private const VALUE_OVER_REQUIRED_TOLERANCE = 2;

    public function __construct(
        private readonly bool $ignoreMsiWithNoMutations,
        private readonly float $minUncoveredMsi,
        private readonly float $minCoveredCodeMsi,
    ) {
    }

    /**
     * @throws MinMsiCheckFailed
     */
    public function checkMetrics(
        int $totalMutantCount,
        float $msi,
        float $coveredCodeMsi,
        ConsoleOutput $consoleOutput,
    ): void {
        $this->checkMinMsi($totalMutantCount, $msi, $coveredCodeMsi);
        $this->checkIfMinMsiCanBeIncreased($msi, $coveredCodeMsi, $consoleOutput);
    }

    private function checkMinMsi(int $totalMutantCount, float $msi, float $coveredCodeMsi): void
    {
        if ($this->ignoreMsiWithNoMutations
            && $totalMutantCount === 0
        ) {
            return;
        }

        if ($this->isUncoveredMsiInsufficient($msi)) {
            throw MinMsiCheckFailed::createUncoveredMsi(
                $this->minUncoveredMsi,
                $msi,
            );
        }

        if ($this->isCoveredCodeMsiInsufficient($coveredCodeMsi)) {
            throw MinMsiCheckFailed::createCoveredMsi(
                $this->minCoveredCodeMsi,
                $coveredCodeMsi,
            );
        }
    }

    private function checkIfMinMsiCanBeIncreased(float $msi, float $coveredCodeMsi, ConsoleOutput $output): void
    {
        if ($this->canIncreaseUncoveredMsi($msi)) {
            $output->logMinUncoveredMsiCanGetIncreasedNotice(
                $this->minUncoveredMsi,
                $msi,
            );
        }

        if ($this->canIncreaseCoveredCodeMsi($coveredCodeMsi)) {
            $output->logMinCoveredCodeMsiCanGetIncreasedNotice(
                $this->minCoveredCodeMsi,
                $coveredCodeMsi,
            );
        }
    }

    private function isUncoveredMsiInsufficient(float $uncoveredMsi): bool
    {
        return $this->minUncoveredMsi > 0 && $uncoveredMsi < $this->minUncoveredMsi;
    }

    private function isCoveredCodeMsiInsufficient(float $coveredCodeMsi): bool
    {
        return $this->minCoveredCodeMsi > 0 && $coveredCodeMsi < $this->minCoveredCodeMsi;
    }

    private function canIncreaseUncoveredMsi(float $msi): bool
    {
        if ($this->minUncoveredMsi === 0.0) {
            return false;
        }

        return $msi > $this->minUncoveredMsi + self::VALUE_OVER_REQUIRED_TOLERANCE;
    }

    private function canIncreaseCoveredCodeMsi(float $coveredCodeMsi): bool
    {
        if ($this->minCoveredCodeMsi === 0.0) {
            return false;
        }

        return $coveredCodeMsi > $this->minCoveredCodeMsi + self::VALUE_OVER_REQUIRED_TOLERANCE;
    }
}
