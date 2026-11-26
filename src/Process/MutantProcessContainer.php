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

use Closure;
use Infection\Mutant\DetectionStatus;
use Infection\Mutant\Mutant;
use Infection\Mutant\MutantExecutionResult;
use Webmozart\Assert\Assert;

/**
 * @phpstan-type MutantProcessFactory = Closure(): (MutantProcess|self)
 *
 * @internal
 * @final
 */
class MutantProcessContainer
{
    private MutantProcess|self|null $current = null;

    /**
     * @var list<MutantProcess>
     */
    private array $evaluationHistory = [];

    private ?DetectionStatus $completedStatus = null;

    private ?MutantExecutionResult $finalResult = null;

    private int $nextEvaluationIndex = 0;

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

    /**
     * @param MutantProcessFactory $factory
     */
    public function append(Closure $factory): self
    {
        Assert::null($this->completedStatus, 'Cannot append an evaluation after the current evaluation has been recorded.');

        $this->factories[] = $factory;

        return $this;
    }

    public function getMutant(): Mutant
    {
        return $this->mutant;
    }

    public function isCold(): bool
    {
        return $this->current === null;
    }

    public function getCurrent(): MutantProcess
    {
        $this->current ??= $this->evaluate(0);

        return $this->current instanceof self
            ? $this->current->getCurrent()
            : $this->current;
    }

    public function hasNext(): bool
    {
        $this->getCurrent();

        if ($this->current instanceof self
            && $this->current->hasNext()
        ) {
            return true;
        }

        return $this->hasNextEvaluation();
    }

    public function createNext(): MutantProcess
    {
        if ($this->current instanceof self
            && $this->current->hasNext()
        ) {
            return $this->current->createNext();
        }

        Assert::true($this->hasNextEvaluation(), 'Cannot start another attempt without a pending evaluation.');

        $this->current = $this->evaluate($this->nextEvaluationIndex);
        $this->completedStatus = null;

        return $this->getCurrent();
    }

    public function getMutantExecutionResult(): MutantExecutionResult
    {
        Assert::false($this->isCold(), 'Cannot finalize a mutant before evaluation has started.');
        Assert::false($this->hasNext(), 'Cannot finalize a mutant while another evaluation attempt is pending.');
        $status = $this->recordCompletedEvaluation();
        $history = $this->evaluationHistory;
        Assert::notEmpty($history, 'Cannot build a final mutant result without a completed evaluation.');

        return $this->finalResult ??= MutantExecutionResult::createFromEvaluation(
            $this->mutant,
            $status,
            $history,
        );
    }

    private function evaluate(int $index): MutantProcess|self
    {
        $attempt = ($this->factories[$index])();
        $this->nextEvaluationIndex = $index + 1;

        return $attempt;
    }

    /**
     * Checks for a follow-up in this container once the current evaluator has finished.
     */
    private function hasNextEvaluation(): bool
    {
        if ($this->recordCompletedEvaluation() !== DetectionStatus::ESCAPED) {
            return false;
        }

        return isset($this->factories[$this->nextEvaluationIndex]);
    }

    private function recordCompletedEvaluation(): DetectionStatus
    {
        if ($this->completedStatus !== null) {
            return $this->completedStatus;
        }

        $attempt = $this->current;
        Assert::notNull($attempt, 'Cannot record an evaluation before its first attempt has been created.');

        if ($attempt instanceof self) {
            // The caller has checked for nested follow-ups, so the child's history is complete.
            $status = $attempt->recordCompletedEvaluation();
            $this->evaluationHistory = [...$this->evaluationHistory, ...$attempt->evaluationHistory];

            return $this->completedStatus = $status;
        }

        $status = $attempt->getResult();
        $this->evaluationHistory[] = $attempt;

        return $this->completedStatus = $status;
    }
}
