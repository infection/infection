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
use Infection\Mutant\EvaluationAttemptResult;
use Infection\Mutant\EvaluationStrategy\Action\Action;
use Infection\Mutant\EvaluationStrategy\Action\FinishEvaluation;
use Infection\Mutant\EvaluationStrategy\Action\RunEvaluation;
use Infection\Mutant\EvaluationStrategy\EvaluationStrategy;
use Infection\Mutant\EvaluationStrategy\SequentialEvaluationStrategy;
use Infection\Mutant\Mutant;
use Infection\Mutant\MutantExecutionResult;
use Infection\TestFramework\Contracts\Mutant\MutantProcessDetectionStatusResolver\SuspiciousResult;
use Webmozart\Assert\Assert;

/**
 * Coordinates lazy evaluators and retains their observations after a final decision.
 * A nested container retains its own lifecycle; it is never reduced to its first process.
 *
 * @internal
 * @final
 */
class MutantProcessContainer
{
    private MutantProcess|EvaluationAttemptResult|self|null $current = null;

    /**
     * @var list<EvaluationAttemptResult|SuspiciousResult>
     */
    private array $evaluationHistory = [];

    private ?Action $decision = null;

    private ?MutantExecutionResult $finalResult = null;

    private int $nextEvaluationIndex = 0;

    /**
     * @param non-empty-list<Closure(): (MutantProcess|EvaluationAttemptResult|self)> $evaluations
     */
    private function __construct(
        private readonly Mutant $mutant,
        private array $evaluations,
        private EvaluationStrategy $strategy,
    ) {
    }

    /**
     * @param Closure(): (MutantProcess|EvaluationAttemptResult|self) $factory
     */
    public static function from(Mutant $mutant, Closure $factory): self
    {
        return new self($mutant, [$factory], new SequentialEvaluationStrategy());
    }

    public function isCold(): bool
    {
        return $this->current === null;
    }

    public function withStrategy(EvaluationStrategy $strategy): self
    {
        Assert::true($this->isCold(), 'Cannot replace an evaluation strategy after evaluation has started.');

        $clone = clone $this;
        $clone->strategy = $strategy;

        return $clone;
    }

    public function getCurrent(): MutantProcess|EvaluationAttemptResult
    {
        $this->current ??= $this->evaluate(0);

        return $this->current instanceof self
            ? $this->current->getCurrent()
            : $this->current;
    }

    public function hasNext(): bool
    {
        $this->getCurrent();

        // The nested container's strategy owns its internal follow-ups. Our strategy
        // decides between evaluators after that complete sequence contributes history.
        if ($this->current instanceof self
            && $this->current->hasNext()
        ) {
            return true;
        }

        return $this->decide() instanceof RunEvaluation;
    }

    public function createNext(): MutantProcess|EvaluationAttemptResult
    {
        if ($this->current instanceof self
            && $this->current->hasNext()
        ) {
            return $this->current->createNext();
        }

        $decision = $this->decide();
        Assert::isInstanceOf($decision, RunEvaluation::class, 'Cannot start another attempt after the strategy has finalized the mutant.');

        $this->current = $this->evaluate($this->nextEvaluationIndex);
        $this->decision = null;

        return $this->getCurrent();
    }

    /**
     * @param Closure(): (MutantProcess|EvaluationAttemptResult|self) $factory
     */
    public function append(Closure $factory): self
    {
        Assert::null($this->decision, 'Cannot change the configured evaluations after the strategy has decided what to do.');

        $this->evaluations[] = $factory;

        return $this;
    }

    public function getMutantExecutionResult(): MutantExecutionResult
    {
        Assert::false($this->isCold(), 'Cannot finalize a mutant before evaluation has started.');
        Assert::false($this->hasNext(), 'Cannot finalize a mutant while another evaluation attempt is pending.');
        $decision = $this->decision;
        Assert::isInstanceOf($decision, FinishEvaluation::class, 'Only a finish decision permits construction of a mutant result.');

        $history = $this->evaluationHistory;
        Assert::notEmpty($history, 'A completed evaluation must retain at least one observation.');

        if ($this->finalResult !== null) {
            return $this->finalResult;
        }

        $lastAttempt = $this->getCurrent();
        $lastProcess = $lastAttempt instanceof MutantProcess
            ? $lastAttempt
            : null;

        return $this->finalResult = MutantExecutionResult::createFromEvaluation(
            $this->mutant,
            $decision->detectionStatus,
            $history,
            $lastProcess,
        );
    }

    private function evaluate(int $index): MutantProcess|EvaluationAttemptResult|self
    {
        $attempt = ($this->evaluations[$index])();
        $this->nextEvaluationIndex = $index + 1;

        return $attempt;
    }

    private function decide(): Action
    {
        if ($this->decision !== null) {
            return $this->decision;
        }

        $attempt = $this->current;
        Assert::notNull($attempt, 'Cannot decide an evaluation before its first attempt has been created.');

        $history = [...$this->evaluationHistory, ...$this->observe($attempt)];
        Assert::notEmpty($history, 'A completed evaluation must retain at least one observation.');
        $hasNextEvaluation = isset($this->evaluations[$this->nextEvaluationIndex]);
        $decision = $this->strategy->decide($history, $hasNextEvaluation);
        Assert::true(
            $decision instanceof FinishEvaluation || $decision instanceof RunEvaluation,
            'The evaluation strategy returned an unsupported action.',
        );

        if ($decision instanceof RunEvaluation) {
            Assert::true($hasNextEvaluation, 'The strategy requested another evaluation after the configured sequence ended.');
        }

        $this->evaluationHistory = $history;

        return $this->decision = $decision;
    }

    /**
     * @return list<EvaluationAttemptResult|SuspiciousResult>
     */
    private function observe(MutantProcess|EvaluationAttemptResult|self $attempt): array
    {
        if ($attempt instanceof self) {
            return $attempt->evaluationHistory;
        }

        if ($attempt instanceof EvaluationAttemptResult) {
            return [$attempt];
        }

        Assert::greaterThan($attempt->getFinishedAt(), 0.0, 'Cannot collect evaluation history before the process has finished.');
        $result = $attempt->getResult();

        if ($result instanceof SuspiciousResult) {
            return [$result];
        }

        return [new EvaluationAttemptResult($attempt->getTestFrameworkName(), $result)];
    }
}
