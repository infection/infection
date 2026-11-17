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

use function array_keys;
use function array_map;
use Closure;
use Infection\Mutant\EvaluationAttemptResult;
use Infection\Mutant\EvaluationStrategy;
use Infection\Mutant\FinishEvaluation;
use Infection\Mutant\Mutant;
use Infection\Mutant\MutantExecutionResult;
use Infection\Mutant\RunEvaluation;
use Infection\Mutant\SequentialEvaluationStrategy;
use Infection\TestFramework\Common\CombinedMutantEvaluationPipe;
use Infection\TestFramework\Contracts\MutantEvaluationPipe;
use Webmozart\Assert\Assert;

/**
 * Coordinates lazy evaluators and retains their observations after a final decision.
 * A nested pipe retains its own lifecycle; it is never reduced to its first process.
 *
 * @internal
 * @final
 */
class MutantProcessContainer implements MutantEvaluationPipe
{
    private MutantProcess|EvaluationAttemptResult|MutantEvaluationPipe|null $current = null;

    /**
     * @var list<EvaluationAttemptResult>
     */
    private array $evaluationHistory = [];

    private RunEvaluation|FinishEvaluation|null $decision = null;

    private ?MutantExecutionResult $finalResult = null;

    private int $nextEvaluationIndex = 0;

    /**
     * @param non-empty-list<Closure(): (MutantProcess|EvaluationAttemptResult|MutantEvaluationPipe)> $evaluations
     */
    private function __construct(
        private readonly Mutant $mutant,
        private array $evaluations,
        private EvaluationStrategy $strategy,
    ) {
    }

    /**
     * @param Closure(): (MutantProcess|EvaluationAttemptResult|MutantEvaluationPipe) $factory
     */
    public static function from(Mutant $mutant, Closure $factory): self
    {
        return new self($mutant, [$factory], new SequentialEvaluationStrategy());
    }

    public function getMutant(): Mutant
    {
        return $this->mutant;
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

    public function merge(MutantEvaluationPipe $other): MutantEvaluationPipe
    {
        return CombinedMutantEvaluationPipe::from($this, $other);
    }

    public function getCurrent(): MutantProcess|EvaluationAttemptResult
    {
        $this->current ??= $this->evaluate(0);

        return $this->current instanceof MutantEvaluationPipe
            ? $this->current->getCurrent()
            : $this->current;
    }

    public function hasNext(): bool
    {
        $this->getCurrent();

        // The nested pipe's strategy owns its internal follow-ups. Our strategy
        // decides between evaluators after that complete sequence contributes history.
        if ($this->current instanceof MutantEvaluationPipe
            && $this->current->hasNext()
        ) {
            return true;
        }

        return $this->decide() instanceof RunEvaluation;
    }

    public function createNext(): MutantProcess|EvaluationAttemptResult
    {
        if ($this->current instanceof MutantEvaluationPipe
            && $this->current->hasNext()
        ) {
            return $this->current->createNext();
        }

        $decision = $this->decide();
        Assert::isInstanceOf($decision, RunEvaluation::class, 'Cannot start another attempt after the strategy has finalized the mutant.');

        $this->current = ($decision->createAttempt)();
        $this->decision = null;

        return $this->getCurrent();
    }

    /**
     * @param Closure(): (MutantProcess|EvaluationAttemptResult|MutantEvaluationPipe) $factory
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

        return $this->finalResult ??= MutantExecutionResult::createFromEvaluation(
            $this->mutant,
            $decision->detectionStatus,
            $this->evaluationHistory,
        );
    }

    private function evaluate(int $index): MutantProcess|EvaluationAttemptResult|MutantEvaluationPipe
    {
        $this->nextEvaluationIndex = $index + 1;

        return ($this->evaluations[$index])();
    }

    private function decide(): RunEvaluation|FinishEvaluation
    {
        if ($this->decision !== null) {
            return $this->decision;
        }

        $attempt = $this->current;
        Assert::notNull($attempt, 'Cannot decide an evaluation before its first attempt has been created.');

        if ($attempt instanceof MutantEvaluationPipe) {
            $result = $attempt->getMutantExecutionResult();
            $observations = $result->getEvaluationHistory();
            $status = $result->getDetectionStatus();
        } else {
            if ($attempt instanceof MutantProcess) {
                Assert::greaterThan($attempt->getFinishedAt(), 0.0, 'Cannot collect evaluation history before the process has finished.');
                $attempt = $attempt->getEvaluationAttemptResult();
            }

            $observations = [$attempt];
            $status = $attempt->detectionStatus;
        }

        $history = [...$this->evaluationHistory, ...$observations];
        Assert::notEmpty($history, 'A completed evaluation must retain at least one observation.');
        $evaluations = array_map(
            fn (int $index): Closure => fn (): MutantProcess|EvaluationAttemptResult|MutantEvaluationPipe => $this->evaluate($index),
            array_keys($this->evaluations),
        );
        $decision = $this->strategy->decide(
            $history,
            $evaluations,
            $evaluations[$this->nextEvaluationIndex] ?? null,
            $status,
        );
        $this->evaluationHistory = $history;

        return $this->decision = $decision;
    }
}
