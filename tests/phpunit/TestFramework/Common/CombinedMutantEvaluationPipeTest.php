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

namespace Infection\Tests\TestFramework\Common;

use Closure;
use Infection\Mutant\DetectionStatus;
use Infection\Mutant\EvaluationAttemptResult;
use Infection\Mutant\EvaluationProcessDetails;
use Infection\Mutant\EvaluationStrategy;
use Infection\Mutant\FinishEvaluation;
use Infection\Mutant\RunEvaluation;
use Infection\Process\MutantProcess;
use Infection\Process\MutantProcessContainer;
use Infection\TestFramework\Common\CombinedMutantEvaluationPipe;
use Infection\Tests\Mutant\MutantBuilder;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(CombinedMutantEvaluationPipe::class)]
final class CombinedMutantEvaluationPipeTest extends TestCase
{
    public function test_it_collects_nested_history_without_creating_unused_followups(): void
    {
        $mutant = MutantBuilder::withMinimalTestData()->build();
        $firstObservation = new EvaluationAttemptResult('first tests', DetectionStatus::ESCAPED, 'first output', new EvaluationProcessDetails('test-1', 1.0));
        $secondObservation = new EvaluationAttemptResult('second tests', DetectionStatus::ESCAPED, 'second output', new EvaluationProcessDetails('test-2', 2.0));
        $thirdObservation = new EvaluationAttemptResult('first analysis', DetectionStatus::ESCAPED, 'third output', new EvaluationProcessDetails('analysis-1', 3.0));
        $fourthObservation = new EvaluationAttemptResult('second analysis', DetectionStatus::KILLED_BY_STATIC_ANALYSIS, 'final output', new EvaluationProcessDetails('analysis-2', 4.0));
        $first = $this->process(DetectionStatus::ESCAPED, $firstObservation);
        $second = $this->process(DetectionStatus::ESCAPED, $secondObservation);
        $third = $this->process(DetectionStatus::ESCAPED, $thirdObservation);
        $fourth = $this->process(DetectionStatus::KILLED_BY_STATIC_ANALYSIS, $fourthObservation);
        $firstPipe = MutantProcessContainer::from($mutant, static fn (): MutantProcess => $first)
            ->append(static fn (): MutantProcess => $second)
        ;
        $secondPipe = MutantProcessContainer::from($mutant, static fn (): MutantProcess => $third)
            ->append(static fn (): MutantProcess => $fourth)
        ;
        $pipe = $firstPipe->merge($secondPipe)->merge(
            MutantProcessContainer::from($mutant, function (): MutantProcess {
                $this->fail('Collecting history must not create an unused follow-up.');
            }),
        );
        $pipe->getCurrent();
        $pipe->createNext();
        $pipe->createNext();
        $pipe->createNext();

        $actual = $pipe->getMutantExecutionResult();

        $this->assertSame(
            [$firstObservation, $secondObservation, $thirdObservation, $fourthObservation],
            $actual->getEvaluationHistory(),
        );
        $this->assertSame(DetectionStatus::KILLED_BY_STATIC_ANALYSIS, $actual->getDetectionStatus());
        $this->assertSame('analysis-2', $actual->getProcessCommandLine());
        $this->assertSame('final output', $actual->getProcessOutput());
        $this->assertSame(4.0, $actual->getProcessRuntime());
        $this->assertSame($fourthObservation, $fourth->getEvaluationAttemptResult());
    }

    public function test_it_preserves_every_process_in_nested_framework_evaluations(): void
    {
        $mutant = MutantBuilder::withMinimalTestData()->build();
        $first = $this->process(DetectionStatus::ESCAPED);
        $second = $this->process(DetectionStatus::ESCAPED);
        $third = $this->process(DetectionStatus::ESCAPED);
        $fourth = $this->process(DetectionStatus::KILLED_BY_STATIC_ANALYSIS);
        $fifth = $this->process(DetectionStatus::ESCAPED);
        $firstPipe = MutantProcessContainer::from($mutant, static fn (): MutantProcess => $first)
            ->append(static fn (): MutantProcess => $second)
        ;
        $secondPipe = MutantProcessContainer::from($mutant, static fn (): MutantProcess => $third)
            ->append(static fn (): MutantProcess => $fourth)
        ;
        $pipe = $firstPipe->merge($secondPipe)->merge(
            MutantProcessContainer::from($mutant, static fn (): MutantProcess => $fifth),
        );

        $this->assertSame($mutant, $pipe->getMutant());
        $this->assertTrue($pipe->isCold());
        $this->assertTrue($firstPipe->isCold());
        $this->assertTrue($secondPipe->isCold());
        $this->assertSame($first, $pipe->getCurrent());
        $this->assertFalse($pipe->isCold());
        $this->assertTrue($pipe->hasNext());
        $this->assertSame($second, $pipe->createNext());
        $this->assertTrue($pipe->hasNext());
        $this->assertSame($third, $pipe->createNext());
        $this->assertTrue($pipe->hasNext());
        $this->assertSame($fourth, $pipe->createNext());
        $this->assertFalse($pipe->hasNext());
        $this->assertSame($fourth, $pipe->getCurrent());
    }

    #[DataProvider('completedStatusProvider')]
    public function test_it_only_advances_to_another_pipe_after_escape(DetectionStatus $status, bool $hasNext): void
    {
        $mutant = MutantBuilder::withMinimalTestData()->build();
        $first = $this->process($status);
        $pipe = CombinedMutantEvaluationPipe::from(
            MutantProcessContainer::from($mutant, static fn (): MutantProcess => $first),
            MutantProcessContainer::from($mutant, function (): MutantProcess {
                $this->fail('Checking continuation must not create the next process.');
            }),
        );

        $this->assertSame($hasNext, $pipe->hasNext());
    }

    #[DataProvider('startedPipeProvider')]
    public function test_it_rejects_merging_either_started_pipe(bool $startFirst, bool $mergeCombined): void
    {
        $mutant = MutantBuilder::withMinimalTestData()->build();
        $process = $this->process(DetectionStatus::ESCAPED);
        $first = MutantProcessContainer::from($mutant, static fn (): MutantProcess => $process);
        $second = MutantProcessContainer::from($mutant, static fn (): MutantProcess => $process);
        $third = MutantProcessContainer::from($mutant, static fn (): MutantProcess => $process);
        $left = $mergeCombined ? $first->merge($second) : $first;
        $right = $mergeCombined ? $third : $second;
        ($startFirst ? $left : $right)->getCurrent();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot merge a mutant evaluation pipe after evaluation has started.');

        $left->merge($right);
    }

    public static function completedStatusProvider(): iterable
    {
        yield 'escaped' => [DetectionStatus::ESCAPED, true];

        yield 'killed by tests' => [DetectionStatus::KILLED_BY_TESTS, false];

        yield 'killed by analysis' => [DetectionStatus::KILLED_BY_STATIC_ANALYSIS, false];

        yield 'timeout' => [DetectionStatus::TIMED_OUT, false];

        yield 'error' => [DetectionStatus::ERROR, false];

        yield 'syntax error' => [DetectionStatus::SYNTAX_ERROR, false];
    }

    public static function startedPipeProvider(): iterable
    {
        yield 'first single pipe started' => [true, false];

        yield 'second single pipe started' => [false, false];

        yield 'combined pipe started' => [true, true];

        yield 'pipe appended to combined started' => [false, true];
    }

    public function test_a_combined_pipe_obeys_its_strategy_even_after_a_non_escaped_child(): void
    {
        $mutant = MutantBuilder::withCompleteTestData()->build();
        $first = new EvaluationAttemptResult('Tests', DetectionStatus::KILLED_BY_TESTS, '', null);
        $second = new EvaluationAttemptResult('Analysis', DetectionStatus::ERROR, '', null);
        $strategy = $this->createMock(EvaluationStrategy::class);
        $strategy->expects($this->exactly(2))->method('decide')->willReturnCallback(
            static function (array $history, array $evaluations, ?Closure $next, DetectionStatus $status): RunEvaluation|FinishEvaluation {
                return $next !== null
                    ? new RunEvaluation($next)
                    : new FinishEvaluation(DetectionStatus::ESCAPED);
            },
        );
        $pipe = MutantProcessContainer::from($mutant, static fn (): EvaluationAttemptResult => $first)
            ->merge(MutantProcessContainer::from($mutant, static fn (): EvaluationAttemptResult => $second))
            ->withStrategy($strategy)
        ;

        $this->assertSame($first, $pipe->getCurrent());
        $this->assertTrue($pipe->hasNext());
        $this->assertSame($second, $pipe->createNext());
        $this->assertFalse($pipe->hasNext());
        $result = $pipe->getMutantExecutionResult();

        $this->assertSame(DetectionStatus::ESCAPED, $result->getDetectionStatus());
        $this->assertSame([$first, $second], $result->getEvaluationHistory());
    }

    public function test_a_childs_selected_status_drives_the_enclosing_strategy(): void
    {
        $mutant = MutantBuilder::withCompleteTestData()->build();
        $first = new EvaluationAttemptResult('Tests', DetectionStatus::ERROR, '', null);
        $second = new EvaluationAttemptResult('Analysis', DetectionStatus::KILLED_BY_STATIC_ANALYSIS, '', null);
        $strategy = $this->createMock(EvaluationStrategy::class);
        $strategy->expects($this->once())->method('decide')->willReturn(new FinishEvaluation(DetectionStatus::ESCAPED));
        $child = MutantProcessContainer::from($mutant, static fn (): EvaluationAttemptResult => $first)->withStrategy($strategy);
        $pipe = $child->merge(MutantProcessContainer::from($mutant, static fn (): EvaluationAttemptResult => $second));

        $pipe->getCurrent();
        $this->assertTrue($pipe->hasNext());
        $this->assertSame($second, $pipe->createNext());
        $result = $pipe->getMutantExecutionResult();

        $this->assertSame([$first, $second], $result->getEvaluationHistory());
        $this->assertSame(DetectionStatus::KILLED_BY_STATIC_ANALYSIS, $result->getDetectionStatus());
        $this->assertTrue($child->isCold());
    }

    public function test_merging_preserves_cold_templates_when_the_original_pipe_is_later_used(): void
    {
        $mutant = MutantBuilder::withCompleteTestData()->build();
        $first = MutantProcessContainer::from(
            $mutant,
            static fn (): EvaluationAttemptResult => new EvaluationAttemptResult('First', DetectionStatus::ESCAPED, '', null),
        );
        $second = MutantProcessContainer::from(
            $mutant,
            static fn (): EvaluationAttemptResult => new EvaluationAttemptResult('Second', DetectionStatus::KILLED_BY_STATIC_ANALYSIS, '', null),
        );
        $combined = $first->merge($second);
        $original = $first->getCurrent();

        $this->assertTrue($combined->isCold());
        $this->assertNotSame($original, $combined->getCurrent());
        $this->assertTrue($combined->hasNext());
        $combined->createNext();
        $this->assertFalse($combined->hasNext());
        $this->assertCount(2, $combined->getMutantExecutionResult()->getEvaluationHistory());
        $this->assertSame($original, $first->getCurrent());
        $this->assertTrue($second->isCold());
    }

    private function process(DetectionStatus $status, ?EvaluationAttemptResult $observation = null): MutantProcess
    {
        $process = $this->createStub(MutantProcess::class);
        $observation ??= new EvaluationAttemptResult('Evaluator', $status, '', null);
        $process->method('getFinishedAt')->willReturn(5.0);
        $process->method('getEvaluationAttemptResult')->willReturn($observation);

        return $process;
    }
}
