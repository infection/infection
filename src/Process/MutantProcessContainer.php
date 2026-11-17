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
use Infection\Mutant\Mutant;
use Infection\TestFramework\Common\CombinedMutantEvaluationPipe;
use Infection\TestFramework\Contracts\MutantEvaluationPipe;

/**
 * @phpstan-type MutantProcessFactory = Closure(): MutantProcess
 *
 * @internal
 * @final
 */
class MutantProcessContainer implements MutantEvaluationPipe
{
    /**
     * @var array<non-negative-int, MutantProcess>
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
        private readonly Mutant $mutant,
        private array $factories,
    ) {
    }

    /**
     * @param MutantProcessFactory $factory
     */
    public static function from(Mutant $mutant, Closure $factory): self
    {
        return new self($mutant, [$factory]);
    }

    public function getMutant(): Mutant
    {
        return $this->mutant;
    }

    public function isCold(): bool
    {
        return $this->processes === [];
    }

    public function merge(MutantEvaluationPipe $other): MutantEvaluationPipe
    {
        return CombinedMutantEvaluationPipe::from($this, $other);
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
     * Container has a next process only if Mutant is Escaped
     */
    public function hasNext(): bool
    {
        return array_key_exists($this->currentProcessIndex + 1, $this->factories)
            && $this->getCurrentMutantProcessDetectionStatus() === DetectionStatus::ESCAPED;
    }

    public function createNext(): MutantProcess
    {
        ++$this->currentProcessIndex;

        return $this->getCurrent();
    }

    public function getCurrent(): MutantProcess
    {
        return $this->processes[$this->currentProcessIndex] ??= ($this->factories[$this->currentProcessIndex])();
    }

    private function getCurrentMutantProcessDetectionStatus(): DetectionStatus
    {
        return $this->getCurrent()->getMutantExecutionResult()->getDetectionStatus();
    }
}
