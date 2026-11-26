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

use BadMethodCallException;
use Infection\Mutant\Mutant;
use Infection\Process\MutantProcessContainer;
use Infection\TestFramework\Contracts\InitialRunResults;
use Infection\TestFramework\Contracts\TestFramework;

/**
 * Composes test frameworks into a single mutant-evaluation workflow.
 *
 * @internal
 */
final readonly class CombinedTestFramework implements TestFramework
{
    private const string UNSUPPORTED_OPERATION_MESSAGE = 'CombinedTestFramework only supports mutant evaluation.';

    public function __construct(
        private TestFramework $firstTestFramework,
        private ?TestFramework $followUpTestFramework,
    ) {
    }

    public function getName(): string
    {
        throw new BadMethodCallException(self::UNSUPPORTED_OPERATION_MESSAGE);
    }

    public function getVersion(): string
    {
        throw new BadMethodCallException(self::UNSUPPORTED_OPERATION_MESSAGE);
    }

    public function checkRequirements(): void
    {
        throw new BadMethodCallException(self::UNSUPPORTED_OPERATION_MESSAGE);
    }

    public function executeInitialRun(): InitialRunResults
    {
        throw new BadMethodCallException(self::UNSUPPORTED_OPERATION_MESSAGE);
    }

    public function test(Mutant $mutant): MutantProcessContainer
    {
        $evaluation = MutantProcessContainer::from(
            $mutant,
            fn (): MutantProcessContainer => $this->firstTestFramework->test($mutant),
        );
        $followUpTestFramework = $this->followUpTestFramework;

        if ($followUpTestFramework !== null) {
            $evaluation->append(
                static fn (): MutantProcessContainer => $followUpTestFramework->test($mutant),
            );
        }

        return $evaluation;
    }

    public function hasJUnitReport(): bool
    {
        throw new BadMethodCallException(self::UNSUPPORTED_OPERATION_MESSAGE);
    }
}
