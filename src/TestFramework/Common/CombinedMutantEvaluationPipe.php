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

namespace Infection\TestFramework\Common;

use Infection\Mutant\EvaluationAttemptResult;
use Infection\Mutant\EvaluationStrategy;
use Infection\Mutant\Mutant;
use Infection\Mutant\MutantExecutionResult;
use Infection\Process\MutantProcess;
use Infection\Process\MutantProcessContainer;
use Infection\TestFramework\Contracts\MutantEvaluationPipe;
use Webmozart\Assert\Assert;

/**
 * Composes whole cold pipes while leaving continuation and finalization to their strategies.
 *
 * @internal
 */
final class CombinedMutantEvaluationPipe implements MutantEvaluationPipe
{
    private function __construct(
        private MutantProcessContainer $evaluation,
    ) {
    }

    public function __clone()
    {
        $this->evaluation = clone $this->evaluation;
    }

    public static function from(MutantEvaluationPipe $first, MutantEvaluationPipe $second): self
    {
        Assert::true($first->isCold(), 'Cannot merge a mutant evaluation pipe after evaluation has started.');
        Assert::true($second->isCold(), 'Cannot merge a mutant evaluation pipe after evaluation has started.');
        Assert::same($first->getMutant(), $second->getMutant(), 'Combined evaluation pipes must evaluate the same mutant.');

        $first = clone $first;
        $second = clone $second;

        // Keep cold templates so a selected retry gets a fresh complete lifecycle.
        return new self(
            MutantProcessContainer::from($first->getMutant(), static fn (): MutantEvaluationPipe => clone $first)
                ->append(static fn (): MutantEvaluationPipe => clone $second),
        );
    }

    public function getMutant(): Mutant
    {
        return $this->evaluation->getMutant();
    }

    public function isCold(): bool
    {
        return $this->evaluation->isCold();
    }

    public function withStrategy(EvaluationStrategy $strategy): self
    {
        return new self($this->evaluation->withStrategy($strategy));
    }

    public function getCurrent(): MutantProcess|EvaluationAttemptResult
    {
        return $this->evaluation->getCurrent();
    }

    public function hasNext(): bool
    {
        return $this->evaluation->hasNext();
    }

    public function createNext(): MutantProcess|EvaluationAttemptResult
    {
        return $this->evaluation->createNext();
    }

    public function merge(MutantEvaluationPipe $other): MutantEvaluationPipe
    {
        return self::from($this, $other);
    }

    public function getMutantExecutionResult(): MutantExecutionResult
    {
        return $this->evaluation->getMutantExecutionResult();
    }
}
