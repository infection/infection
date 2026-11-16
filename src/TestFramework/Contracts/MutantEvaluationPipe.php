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

namespace Infection\TestFramework\Contracts;

use Infection\Mutant\EvaluationAttemptResult;
use Infection\Mutant\EvaluationStrategy;
use Infection\Mutant\Mutant;
use Infection\Mutant\MutantExecutionResult;
use Infection\Process\MutantProcess;

/**
 * Coordinates process-backed or direct evaluations until a strategy selects a final result.
 * It preserves completed observations and exposes the current work without consuming it.
 *
 * @internal
 */
interface MutantEvaluationPipe
{
    public function getMutant(): Mutant;

    /**
     * Returns a cold copy using this strategy between its configured evaluations.
     * A nested pipe retains its own strategy for its internal follow-ups.
     */
    public function withStrategy(EvaluationStrategy $strategy): self;

    /**
     * Whether evaluating the mutant has not started yet.
     */
    public function isCold(): bool;

    /**
     * Creates the first evaluation lazily, then exposes the same current attempt.
     */
    public function getCurrent(): MutantProcess|EvaluationAttemptResult;

    /**
     * Decides continuation after the current attempt completes. A direct result is
     * already complete; a process must first be marked as finished by the runner.
     */
    public function hasNext(): bool;

    /**
     * Executes the selected continuation, preserving a nested pipe's follow-ups.
     */
    public function createNext(): MutantProcess|EvaluationAttemptResult;

    /**
     * Merges this pipe with the given pipe, preserving their evaluation order.
     *
     * Both pipes must be cold. The returned pipe is cold and neither input is mutated.
     */
    public function merge(self $other): self;

    /**
     * Returns a memoized final result only after a finish decision has been locked in.
     * Does not create processes for unused follow-ups.
     */
    public function getMutantExecutionResult(): MutantExecutionResult;
}
