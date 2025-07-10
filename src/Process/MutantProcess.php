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

namespace Infection\Process;

use Infection\Mutant\Mutant;
use Infection\Mutant\MutantExecutionResult;
use Infection\Mutant\MutantExecutionResultFactory;
use function microtime;
use Symfony\Component\Process\Process;

/**
 * @internal
 * @final
 */
class MutantProcess
{
    private bool $timedOut = false;

    private float $finishedAt = 0.0;

    public function __construct(
        private readonly Process $process,
        private readonly Mutant $mutant,
        private readonly MutantExecutionResultFactory $mutantExecutionResultFactory,
        private readonly ?TestTokenHandler $testTokenHandler = null,
    ) {
    }

    public function getProcess(): Process
    {
        return $this->process;
    }

    public function getMutant(): Mutant
    {
        return $this->mutant;
    }

    /**
     * @return int a test token for backward compatibility with the old test token handler; should be removed together IndexedMutantProcessContainer
     */
    public function startProcess(): int
    {
        $env = $this->getEnvironment();
        $this->getProcess()->start(env: $env);

        return $env['TEST_TOKEN'] ?? 0;
    }

    public function markAsTimedOut(): void
    {
        $this->timedOut = true;
    }

    public function isTimedOut(): bool
    {
        return $this->timedOut;
    }

    public function markAsFinished(): void
    {
        $this->finishedAt = microtime(true);
    }

    public function getFinishedAt(): float
    {
        return $this->finishedAt;
    }

    public function getMutantExecutionResult(): MutantExecutionResult
    {
        // todo [phpstan-integration] cache it
        return $this->mutantExecutionResultFactory->createFromProcess($this);
    }

    private function getEnvironment(): array
    {
        if ($this->testTokenHandler === null) {
            return [];
        }

        return [
            'INFECTION' => '1',
            'TEST_TOKEN' => $this->testTokenHandler->getNextToken(),
        ];
    }
}
