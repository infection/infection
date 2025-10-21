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

namespace Infection\Configuration\Options;

/**
 * Applies command-line argument overrides to InfectionOptions.
 *
 * @internal
 */
final class CliOptionsApplier
{
    public function apply(
        InfectionOptions $options,
        ?string $initialTestsPhpOptions = null,
        ?bool $ignoreMsiWithNoMutations = null,
        ?float $minMsi = null,
        ?float $minCoveredMsi = null,
        ?string $testFramework = null,
        ?string $testFrameworkExtraOptions = null,
        ?string $staticAnalysisToolOptions = null,
        ?int $threadCount = null,
        ?string $staticAnalysisTool = null,
        ?bool $dryRun = null,
        ?int $msiPrecision = null,
    ): void {
        if ($initialTestsPhpOptions !== null) {
            $options->initialTestsPhpOptions = $initialTestsPhpOptions;
        }

        if ($ignoreMsiWithNoMutations !== null) {
            $options->ignoreMsiWithNoMutations = $ignoreMsiWithNoMutations;
        }

        if ($minMsi !== null) {
            $options->minMsi = $minMsi;
        }

        if ($minCoveredMsi !== null) {
            $options->minCoveredMsi = $minCoveredMsi;
        }

        if ($testFramework !== null) {
            $options->testFramework = $testFramework;
        }

        if ($testFrameworkExtraOptions !== null) {
            $options->testFrameworkOptions = $testFrameworkExtraOptions;
        }

        if ($staticAnalysisToolOptions !== null) {
            $options->staticAnalysisToolOptions = $staticAnalysisToolOptions;
        }

        if ($threadCount !== null) {
            $options->threads = $threadCount;
        }

        if ($staticAnalysisTool !== null) {
            $options->staticAnalysisTool = $staticAnalysisTool;
        }

        if ($dryRun !== null) {
            $options->dryRun = $dryRun;
        }

        if ($msiPrecision !== null) {
            $options->msiPrecision = $msiPrecision;
        }
    }
}
