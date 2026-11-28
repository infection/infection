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

use function array_key_exists;
use Closure;
use Infection\Mutant\DetectionStatus;
use Infection\Mutant\MutantExecutionResult;

/**
 * @phpstan-type MutantProcessFactory = Closure(): (MutantProcess|self)
 *
 * @internal
 * @final
 */
class MutantProcessContainer
{
    /**
     * @var array<non-negative-int, MutantProcess|self>
     */
    private array $processes = [];

    /**
     * @var non-negative-int
     */
    private int $currentProcessIndex = 0;

    /**
     * @param non-empty-list<MutantProcessFactory> $factories
     */
    private function __construct(
        private array $factories,
    ) {
    }

    /**
     * @param MutantProcessFactory $factory
     */
    public static function from(Closure $factory): self
    {
        return new self([$factory]);
    }

    /**
     * @param MutantProcessFactory $factory
     */
    public function append(Closure $factory): self
    {
        $this->factories[] = $factory;

        return $this;
    }

    /**
     * Completes nested follow-ups before advancing to another factory after escape.
     */
    public function hasNext(): bool
    {
        $current = $this->getCurrentEvaluation();

        if ($current instanceof self
            && $current->hasNext()
        ) {
            return true;
        }

        return array_key_exists($this->currentProcessIndex + 1, $this->factories)
            && $this->getMutantExecutionResult()->getDetectionStatus() === DetectionStatus::ESCAPED;
    }

    public function createNext(): MutantProcess
    {
        $current = $this->getCurrentEvaluation();

        if ($current instanceof self
            && $current->hasNext()
        ) {
            return $current->createNext();
        }

        ++$this->currentProcessIndex;

        return $this->getCurrent();
    }

    public function getCurrent(): MutantProcess
    {
        $current = $this->getCurrentEvaluation();

        return $current instanceof self
            ? $current->getCurrent()
            : $current;
    }

    public function getMutantExecutionResult(): MutantExecutionResult
    {
        return $this->getCurrent()->getMutantExecutionResult();
    }

    private function getCurrentEvaluation(): MutantProcess|self
    {
        return $this->processes[$this->currentProcessIndex] ??= ($this->factories[$this->currentProcessIndex])();
    }
}
