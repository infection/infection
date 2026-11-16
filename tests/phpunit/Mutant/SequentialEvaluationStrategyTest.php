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

namespace Infection\Tests\Mutant;

use Infection\Mutant\DetectionStatus;
use Infection\Mutant\EvaluationAttemptResult;
use Infection\Mutant\FinishEvaluation;
use Infection\Mutant\RunEvaluation;
use Infection\Mutant\SequentialEvaluationStrategy;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(SequentialEvaluationStrategy::class)]
final class SequentialEvaluationStrategyTest extends TestCase
{
    public function test_it_selects_the_next_configured_evaluator_without_running_it(): void
    {
        $first = new EvaluationAttemptResult('First', DetectionStatus::ESCAPED, '', null);
        $second = new EvaluationAttemptResult('Second', DetectionStatus::ESCAPED, '', null);
        $firstFactory = function (): EvaluationAttemptResult {
            $this->fail('A completed evaluation must not be repeated by the sequential strategy.');
        };
        $secondFactory = $firstFactory;
        $thirdFactory = function (): EvaluationAttemptResult {
            $this->fail('Selecting an evaluator must not run it.');
        };
        $strategy = new SequentialEvaluationStrategy();

        $decision = $strategy->decide(
            [$first, $second],
            [$firstFactory, $secondFactory, $thirdFactory],
            $thirdFactory,
            DetectionStatus::ESCAPED,
        );

        $this->assertInstanceOf(RunEvaluation::class, $decision);
        $this->assertSame($thirdFactory, $decision->createAttempt);
    }

    #[DataProvider('terminalStatusProvider')]
    public function test_it_finishes_with_the_last_status_when_continuation_is_not_needed(DetectionStatus $status, bool $hasAnotherEvaluator): void
    {
        $result = new EvaluationAttemptResult('First', $status, '', null);
        $factory = function (): EvaluationAttemptResult {
            $this->fail('Finishing evaluation must not run another evaluator.');
        };
        $evaluations = $hasAnotherEvaluator
            ? [$factory, $factory]
            : [$factory];
        $strategy = new SequentialEvaluationStrategy();

        $nextEvaluation = $hasAnotherEvaluator
            ? $factory
            : null;
        $decision = $strategy->decide([$result], $evaluations, $nextEvaluation, $status);

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
