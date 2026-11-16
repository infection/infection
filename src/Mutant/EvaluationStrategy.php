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

namespace Infection\Mutant;

use Closure;
use Infection\Process\MutantProcess;
use Infection\TestFramework\Contracts\MutantEvaluationPipe;

/**
 * Chooses another evaluation or a final status from all completed observations.
 * Factories remain lazy and may be selected again to retry an evaluator.
 *
 * @internal
 */
interface EvaluationStrategy
{
    /**
     * A nested pipe contributes its complete history and its selected status.
     * The next evaluator is tracked separately from the number of observations.
     *
     * @param non-empty-list<EvaluationAttemptResult> $history
     * @param non-empty-list<Closure(): (MutantProcess|EvaluationAttemptResult|MutantEvaluationPipe)> $evaluations
     * @param (Closure(): (MutantProcess|EvaluationAttemptResult|MutantEvaluationPipe))|null $nextEvaluation
     */
    public function decide(
        array $history,
        array $evaluations,
        ?Closure $nextEvaluation,
        DetectionStatus $currentStatus,
    ): RunEvaluation|FinishEvaluation;
}
