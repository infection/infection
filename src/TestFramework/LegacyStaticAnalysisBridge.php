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

namespace Infection\TestFramework;

use Infection\Mutant\Mutant;
use Infection\Process\MutantProcessContainer;
use Infection\Process\Runner\InitialStaticAnalysisRunFailed;
use Infection\Process\Runner\InitialStaticAnalysisRunner;
use Infection\StaticAnalysis\StaticAnalysisToolAdapter;
use Infection\TestFramework\Contracts\InitialRunResults;
use Infection\TestFramework\Contracts\StaticAnalysisTestFramework;
use Symfony\Component\Process\Exception\ProcessSignaledException;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Exception\RuntimeException;

/**
 * Compatibility layer for the built-in static-analysis adapters. No third-party adapter
 * packages depend on this bridge.
 *
 * @internal
 *
 * @deprecated Remove once the built-in static-analysis adapters implement TestFramework directly.
 */
final readonly class LegacyStaticAnalysisBridge implements StaticAnalysisTestFramework
{
    public function __construct(
        private InitialStaticAnalysisRunner $initialStaticAnalysisRunner,
        private StaticAnalysisToolAdapter $staticAnalysisToolAdapter,
    ) {
    }

    public function getName(): string
    {
        return $this->staticAnalysisToolAdapter->getName();
    }

    public function getVersion(): string
    {
        return $this->staticAnalysisToolAdapter->getVersion();
    }

    public function checkRequirements(): void
    {
        $this->staticAnalysisToolAdapter->assertMinimumVersionSatisfied();
    }

    /**
     * @throws InitialStaticAnalysisRunFailed
     * @throws RuntimeException
     * @throws ProcessTimedOutException
     * @throws ProcessSignaledException
     */
    public function executeInitialRun(): InitialRunResults
    {
        $this->initialStaticAnalysisRunner->run();

        return new InitialRunResults('', null);
    }

    public function test(Mutant $mutant): MutantProcessContainer
    {
        return new MutantProcessContainer(
            $this->staticAnalysisToolAdapter->createMutantProcessFactory()->create($mutant),
            [],
        );
    }

    public function hasJUnitReport(): bool
    {
        return false;
    }
}
