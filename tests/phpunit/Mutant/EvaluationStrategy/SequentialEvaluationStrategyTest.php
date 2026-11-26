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

namespace Infection\Tests\Mutant\EvaluationStrategy;

use Infection\Mutant\DetectionStatus;
use Infection\Mutant\EvaluationAttemptResult;
use Infection\Mutant\EvaluationStrategy\Action\FinishEvaluation;
use Infection\Mutant\EvaluationStrategy\Action\RunEvaluation;
use Infection\Mutant\EvaluationStrategy\SequentialEvaluationStrategy;
use Infection\TestFramework\Contracts\Mutant\MutantProcessDetectionStatusResolver\SuspiciousResult;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(SequentialEvaluationStrategy::class)]
final class SequentialEvaluationStrategyTest extends TestCase
{
    public function test_it_requests_the_next_evaluation_when_the_last_one_escaped(): void
    {
        $first = new EvaluationAttemptResult('First', DetectionStatus::KILLED_BY_TESTS);
        $second = new EvaluationAttemptResult('Second', DetectionStatus::ESCAPED);
        $strategy = new SequentialEvaluationStrategy();

        $decision = $strategy->decide([$first, $second], hasNextEvaluation: true);

        $this->assertInstanceOf(RunEvaluation::class, $decision);
    }

    public function test_it_finishes_with_an_error_when_the_last_result_is_suspicious(): void
    {
        $first = new EvaluationAttemptResult('First', DetectionStatus::ESCAPED);
        $strategy = new SequentialEvaluationStrategy();

        $decision = $strategy->decide([$first, new SuspiciousResult()], hasNextEvaluation: true);

        $this->assertInstanceOf(FinishEvaluation::class, $decision);
        $this->assertSame(DetectionStatus::ERROR, $decision->detectionStatus);
    }

    #[DataProvider('terminalStatusProvider')]
    public function test_it_finishes_with_the_last_status_when_continuation_is_not_needed(DetectionStatus $status, bool $hasAnotherEvaluator): void
    {
        $result = new EvaluationAttemptResult('First', $status);
        $strategy = new SequentialEvaluationStrategy();

        $decision = $strategy->decide([$result], $hasAnotherEvaluator);

        $this->assertInstanceOf(FinishEvaluation::class, $decision);
        $this->assertSame($status, $decision->detectionStatus);
    }

    public static function terminalStatusProvider(): iterable
    {
        yield 'escaped with no remaining evaluator' => [DetectionStatus::ESCAPED, false];

        yield 'killed by tests stops the sequence' => [DetectionStatus::KILLED_BY_TESTS, true];

        yield 'killed by static analysis stops the sequence' => [DetectionStatus::KILLED_BY_STATIC_ANALYSIS, true];

        yield 'error stops the sequence' => [DetectionStatus::ERROR, true];

        yield 'timeout stops the sequence' => [DetectionStatus::TIMED_OUT, true];

        yield 'syntax error stops the sequence' => [DetectionStatus::SYNTAX_ERROR, true];

        yield 'skipped stops the sequence' => [DetectionStatus::SKIPPED, true];

        yield 'uncovered stops the sequence' => [DetectionStatus::NOT_COVERED, true];

        yield 'ignored stops the sequence' => [DetectionStatus::IGNORED, true];
    }
}
