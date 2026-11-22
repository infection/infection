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

use function array_map;
use function array_reduce;
use function array_slice;
use function array_values;
use function implode;
use Infection\Mutant\Mutant;
use Infection\Process\MutantProcessContainer;
use Infection\TestFramework\Contracts\InitialRunResults;
use Infection\TestFramework\Contracts\TestFramework;

/**
 * Composes test frameworks into one ordered workflow.
 *
 * @internal
 */
final readonly class CombinedTestFramework implements TestFramework
{
    /**
     * @var non-empty-list<TestFramework>
     */
    private array $testFrameworks;

    public function __construct(
        TestFramework $firstTestFramework,
        TestFramework ...$followUpTestFrameworks,
    ) {
        $this->testFrameworks = [
            $firstTestFramework,
            ...array_values($followUpTestFrameworks),
        ];
    }

    public function getName(): string
    {
        return implode(
            ', ',
            array_map(
                static fn (TestFramework $framework): string => $framework->getName(),
                $this->testFrameworks,
            ),
        );
    }

    public function getVersion(): string
    {
        return implode(
            ', ',
            array_map(
                static fn (TestFramework $framework): string => $framework->getVersion(),
                $this->testFrameworks,
            ),
        );
    }

    public function checkRequirements(): void
    {
        foreach ($this->testFrameworks as $framework) {
            $framework->checkRequirements();
        }
    }

    public function executeInitialRun(): InitialRunResults
    {
        foreach ($this->testFrameworks as $framework) {
            $framework->executeInitialRun();
        }

        // Individual results need an explicit collection if callers need them; no single
        // framework's output or memory usage represents the combined run.
        return new InitialRunResults('', null);
    }

    public function test(Mutant $mutant): MutantProcessContainer
    {
        // Defer each follow-up framework setup until the preceding evaluation has escaped.
        return array_reduce(
            array_slice($this->testFrameworks, 1),
            static fn (
                MutantProcessContainer $evaluation,
                TestFramework $framework,
            ): MutantProcessContainer => $evaluation->append(
                static fn (): MutantProcessContainer => $framework->test($mutant),
            ),
            $this->testFrameworks[0]->test($mutant),
        );
    }

    public function hasJUnitReport(): bool
    {
        foreach ($this->testFrameworks as $framework) {
            // Preserve the legacy capability until it is removed from the contract.
            // @mago-expect analysis:deprecated-method
            if ($framework->hasJUnitReport()) {
                return true;
            }
        }

        return false;
    }
}
