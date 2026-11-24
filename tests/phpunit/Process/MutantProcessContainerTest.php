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

namespace Infection\Tests\Process;

use Infection\Mutant\DetectionStatus;
use Infection\Mutant\EvaluationAttemptResult;
use Infection\Mutant\EvaluationStrategy\Action\Action;
use Infection\Mutant\EvaluationStrategy\Action\FinishEvaluation;
use Infection\Mutant\EvaluationStrategy\Action\RunEvaluation;
use Infection\Mutant\EvaluationStrategy\EvaluationStrategy;
use Infection\Process\MutantProcess;
use Infection\Process\MutantProcessContainer;
use Infection\TestFramework\Contracts\Mutant\MutantProcessDetectionStatusResolver\SuspiciousResult;
use Infection\Tests\Mutant\MutantBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;
use Webmozart\Assert\InvalidArgumentException;

#[CoversClass(MutantProcessContainer::class)]
final class MutantProcessContainerTest extends TestCase
{
    public function test_it_changes_strategy_on_a_cold_copy(): void
    {
        $mutant = MutantBuilder::withCompleteTestData()->build();
        $attempt = new EvaluationAttemptResult('Evaluator', DetectionStatus::ESCAPED);
        $original = MutantProcessContainer::from($mutant, static fn (): EvaluationAttemptResult => $attempt);
        $strategy = $this->createStub(EvaluationStrategy::class);
        $strategy->method('decide')->willReturn(new FinishEvaluation(DetectionStatus::ERROR));

        $copy = $original->withStrategy($strategy);

        $this->assertTrue($original->isCold());
        $this->assertTrue($copy->isCold());
        $this->assertSame($attempt, $copy->getCurrent());
        $this->assertFalse($copy->isCold());
        $this->assertSame($attempt, $original->getCurrent());
        $this->assertSame(DetectionStatus::ESCAPED, $original->getMutantExecutionResult()->getDetectionStatus());
        $this->assertSame(DetectionStatus::ERROR, $copy->getMutantExecutionResult()->getDetectionStatus());
    }

    public function test_it_finishes_a_completed_attempt_without_a_follow_up_factory(): void
    {
        $result = new EvaluationAttemptResult('Evaluator', DetectionStatus::ESCAPED);
        $container = MutantProcessContainer::from(MutantBuilder::withCompleteTestData()->build(), static fn (): EvaluationAttemptResult => $result);

        $this->assertSame($result, $container->getCurrent());
        $this->assertFalse($container->hasNext());
    }

    public function test_a_strategy_cannot_request_an_evaluation_after_the_sequence_ends(): void
    {
        $mutant = MutantBuilder::withCompleteTestData()->build();
        $attempt = new EvaluationAttemptResult('Evaluator', DetectionStatus::ESCAPED);
        $strategy = $this->createMock(EvaluationStrategy::class);
        $strategy
            ->expects($this->once())
            ->method('decide')
            ->with([$attempt], false)
            ->willReturn(new RunEvaluation())
        ;
        $container = MutantProcessContainer::from($mutant, static fn (): EvaluationAttemptResult => $attempt)
            ->withStrategy($strategy)
        ;

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The strategy requested another evaluation after the configured sequence ended.');

        $container->hasNext();
    }

    public function test_it_rejects_an_action_without_a_handler(): void
    {
        $mutant = MutantBuilder::withCompleteTestData()->build();
        $attempt = new EvaluationAttemptResult('Evaluator', DetectionStatus::ESCAPED);
        $strategy = $this->createStub(EvaluationStrategy::class);
        $strategy->method('decide')->willReturn($this->createStub(Action::class));
        $container = MutantProcessContainer::from($mutant, static fn (): EvaluationAttemptResult => $attempt)
            ->withStrategy($strategy)
        ;

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The evaluation strategy returned an unsupported action.');

        $container->hasNext();
    }

    #[DataProvider('detectionStatusProvider')]
    public function test_only_escaped_mutants_have_a_follow_up_process(DetectionStatus $status, bool $hasNext): void
    {
        $process = $this->createStub(MutantProcess::class);
        $process->method('getFinishedAt')->willReturn(2.0);
        $process->method('getResult')->willReturn($status);

        $container = MutantProcessContainer::from(MutantBuilder::withCompleteTestData()->build(), static fn (): MutantProcess => $process)
            ->append(function (): MutantProcess {
                $this->fail('Checking for a follow-up must not create its process.');
            })
        ;

        $this->assertSame($hasNext, $container->hasNext());
    }

    public function test_it_creates_each_process_lazily_once_and_advances_in_order(): void
    {
        $first = $this->createStub(MutantProcess::class);
        $second = $this->createStub(MutantProcess::class);
        $third = $this->createStub(MutantProcess::class);
        $first->method('getFinishedAt')->willReturn(2.0);
        $second->method('getFinishedAt')->willReturn(3.0);
        $third->method('getFinishedAt')->willReturn(4.0);
        $first->method('getResult')->willReturn(DetectionStatus::ESCAPED);
        $second->method('getResult')->willReturn(DetectionStatus::ESCAPED);
        $third->method('getResult')->willReturn(DetectionStatus::KILLED_BY_STATIC_ANALYSIS);
        $calls = new class {
            public int $first = 0;

            public int $second = 0;
        };

        $container = MutantProcessContainer::from(MutantBuilder::withCompleteTestData()->build(), static function () use ($first, $calls): MutantProcess {
            ++$calls->first;

            return $first;
        });
        $appended = $container->append(static function () use ($second, $calls): MutantProcess {
            ++$calls->second;

            return $second;
        });
        $container
            ->append(static fn (): MutantProcess => $third)
            ->append(function (): MutantProcess {
                $this->fail('A killed mutant must not advance to the remaining process.');
            })
        ;

        $this->assertSame($container, $appended);
        $this->assertSame(0, $calls->first);
        $this->assertSame(0, $calls->second);
        $this->assertSame($first, $container->getCurrent());
        $this->assertSame($first, $container->getCurrent());
        $this->assertTrue($container->hasNext());
        $this->assertSame(1, $calls->first);
        $this->assertSame(0, $calls->second);

        $this->assertSame($second, $container->createNext());
        $this->assertSame($second, $container->getCurrent());
        $this->assertSame(1, $calls->second);
        $this->assertTrue($container->hasNext());

        $this->assertSame($third, $container->createNext());
        $this->assertSame($third, $container->getCurrent());
        $this->assertFalse($container->hasNext());
    }

    #[DataProvider('completedAttemptsProvider')]
    public function test_it_finalizes_with_the_last_status_and_retains_every_attempt(DetectionStatus $firstStatus, ?DetectionStatus $secondStatus): void
    {
        $mutant = MutantBuilder::withCompleteTestData()->build();
        $first = new EvaluationAttemptResult('PHPUnit', $firstStatus);
        $firstProcess = $this->createStub(MutantProcess::class);
        $firstProcess->method('getFinishedAt')->willReturn(2.0);
        $firstProcess->method('getResult')->willReturn($first->detectionStatus);
        $firstProcess->method('getTestFrameworkName')->willReturn($first->evaluatorName);
        $firstProcess->method('getResultOutput')->willReturn('test output');
        $firstProcess->method('getRuntime')->willReturn(1.25);
        $underlying = $this->createStub(Process::class);
        $underlying->method('getCommandLine')->willReturn('phpunit');
        $firstProcess->method('getProcess')->willReturn($underlying);
        $firstProcess->method('getMutant')->willReturn($mutant);
        $container = MutantProcessContainer::from($mutant, static fn (): MutantProcess => $firstProcess);
        $container->getCurrent();
        $expectedHistory = [$first];
        $last = $first;

        if ($secondStatus !== null) {
            $last = new EvaluationAttemptResult('PHPStan', $secondStatus);
            $secondProcess = $this->createStub(MutantProcess::class);
            $secondProcess->method('getFinishedAt')->willReturn(3.0);
            $secondProcess->method('getResult')->willReturn($last->detectionStatus);
            $secondProcess->method('getTestFrameworkName')->willReturn($last->evaluatorName);
            $secondProcess->method('getResultOutput')->willReturn('analysis output');
            $secondProcess->method('getRuntime')->willReturn(0.25);
            $underlying = $this->createStub(Process::class);
            $underlying->method('getCommandLine')->willReturn('phpstan');
            $secondProcess->method('getProcess')->willReturn($underlying);
            $secondProcess->method('getMutant')->willReturn($mutant);
            $container->append(static fn (): MutantProcess => $secondProcess);
            $this->assertTrue($container->hasNext());
            $container->createNext();
            $expectedHistory[] = $last;
        }

        if ($last->detectionStatus !== DetectionStatus::ESCAPED) {
            $container->append(function (): MutantProcess {
                $this->fail('Finalization must not create unused processes.');
            });
        }

        $actual = $container->getMutantExecutionResult();

        $this->assertEquals($expectedHistory, $actual->getEvaluationHistory());
        $this->assertSame($last->detectionStatus, $actual->getDetectionStatus());
        $expectedCommand = $secondStatus === null
            ? 'phpunit'
            : 'phpstan';
        $expectedOutput = $secondStatus === null
            ? 'test output'
            : 'analysis output';
        $expectedRuntime = $secondStatus === null
            ? 1.25
            : 0.25;
        $this->assertSame($expectedCommand, $actual->getProcessCommandLine());
        $this->assertSame($expectedOutput, $actual->getProcessOutput());
        $this->assertSame($expectedRuntime, $actual->getProcessRuntime());
        $this->assertSame($mutant->getMutation()->getHash(), $actual->getMutantHash());
    }

    public function test_it_cannot_report_before_creating_a_process(): void
    {
        $container = MutantProcessContainer::from(
            MutantBuilder::withMinimalTestData()->build(),
            function (): MutantProcess {
                $this->fail('Reporting must not create a process.');
            },
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot finalize a mutant before evaluation has started.');

        $container->getMutantExecutionResult();
    }

    public function test_it_records_a_suspicious_process_as_an_error(): void
    {
        $mutant = MutantBuilder::withCompleteTestData()->build();
        $process = $this->createStub(MutantProcess::class);
        $process->method('getFinishedAt')->willReturn(2.0);
        $suspicious = new SuspiciousResult();
        $process->method('getResult')->willReturn($suspicious);
        $container = MutantProcessContainer::from($mutant, static fn (): MutantProcess => $process);
        $container->getCurrent();

        $result = $container->getMutantExecutionResult();

        $this->assertSame(DetectionStatus::ERROR, $result->getDetectionStatus());
        $this->assertSame([$suspicious], $result->getEvaluationHistory());
    }

    public function test_it_cannot_report_an_unfinished_process(): void
    {
        $process = $this->createMock(MutantProcess::class);
        $process->method('getFinishedAt')->willReturn(0.0);
        $process->expects($this->never())->method('getResult');
        $container = MutantProcessContainer::from(
            MutantBuilder::withMinimalTestData()->build(),
            static fn (): MutantProcess => $process,
        );
        $container->getCurrent();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot collect evaluation history before the process has finished.');

        $container->getMutantExecutionResult();
    }

    public function test_the_strategy_can_finalize_without_running_the_remaining_evaluator(): void
    {
        $mutant = MutantBuilder::withCompleteTestData()->build();
        $attempt = new EvaluationAttemptResult('Evaluator', DetectionStatus::ESCAPED);
        $unused = function (): EvaluationAttemptResult {
            $this->fail('The strategy has decided that no further evaluation is needed.');
        };
        $strategy = $this->createMock(EvaluationStrategy::class);
        $strategy
            ->expects($this->once())
            ->method('decide')
            ->with([$attempt], true)
            ->willReturn(new FinishEvaluation(DetectionStatus::KILLED_BY_TESTS))
        ;
        $evaluation = MutantProcessContainer::from($mutant, static fn (): EvaluationAttemptResult => $attempt)
            ->append($unused)
            ->withStrategy($strategy)
        ;

        $this->assertFalse($evaluation->hasNext());
        $this->assertFalse($evaluation->hasNext());
        $evaluation->getCurrent();
        $final = $evaluation->getMutantExecutionResult();

        $this->assertSame(DetectionStatus::KILLED_BY_TESTS, $final->getDetectionStatus());
        $this->assertSame([$attempt], $final->getEvaluationHistory());
        $this->assertSame($final, $evaluation->getMutantExecutionResult());
    }

    public function test_it_finalizes_a_direct_result_without_process_details(): void
    {
        $mutant = MutantBuilder::withCompleteTestData()->build();
        $result = new EvaluationAttemptResult('In-memory analyser', DetectionStatus::KILLED_BY_STATIC_ANALYSIS);
        $evaluation = MutantProcessContainer::from($mutant, static fn (): EvaluationAttemptResult => $result);

        $evaluation->getCurrent();
        $final = $evaluation->getMutantExecutionResult();

        $this->assertSame($result, $evaluation->getCurrent());
        $this->assertSame([$result], $final->getEvaluationHistory());
        $this->assertSame(DetectionStatus::KILLED_BY_STATIC_ANALYSIS, $final->getDetectionStatus());
        $this->assertSame('', $final->getProcessOutput());
        $this->assertSame('', $final->getProcessCommandLine());
        $this->assertSame(0.0, $final->getProcessRuntime());
        $this->assertSame($mutant->getMutation()->getHash(), $final->getMutantHash());
    }

    public function test_it_cannot_finalize_while_a_follow_up_is_pending(): void
    {
        $process = $this->createStub(MutantProcess::class);
        $process->method('getFinishedAt')->willReturn(2.0);
        $process->method('getResult')->willReturn(DetectionStatus::ESCAPED);
        $container = MutantProcessContainer::from(MutantBuilder::withCompleteTestData()->build(), static fn (): MutantProcess => $process);
        $container->getCurrent();
        $container->append(function (): MutantProcess {
            $this->fail('Checking completion must not create the pending process.');
        });

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot finalize a mutant while another evaluation attempt is pending.');

        $container->getMutantExecutionResult();
    }

    public static function completedAttemptsProvider(): iterable
    {
        yield 'tests escape then analysis detects' => [DetectionStatus::ESCAPED, DetectionStatus::KILLED_BY_STATIC_ANALYSIS];

        yield 'both evaluators escape' => [DetectionStatus::ESCAPED, DetectionStatus::ESCAPED];

        yield 'analysis errors' => [DetectionStatus::ESCAPED, DetectionStatus::ERROR];

        yield 'analysis times out' => [DetectionStatus::ESCAPED, DetectionStatus::TIMED_OUT];

        yield 'single escaped stage' => [DetectionStatus::ESCAPED, null];

        yield 'tests kill' => [DetectionStatus::KILLED_BY_TESTS, null];

        yield 'tests error' => [DetectionStatus::ERROR, null];

        yield 'tests time out' => [DetectionStatus::TIMED_OUT, null];
    }

    public static function detectionStatusProvider(): iterable
    {
        yield 'escaped' => [DetectionStatus::ESCAPED, true];

        yield 'killed by tests' => [DetectionStatus::KILLED_BY_TESTS, false];

        yield 'killed by static analysis' => [DetectionStatus::KILLED_BY_STATIC_ANALYSIS, false];

        yield 'error' => [DetectionStatus::ERROR, false];

        yield 'timeout' => [DetectionStatus::TIMED_OUT, false];

        yield 'syntax error' => [DetectionStatus::SYNTAX_ERROR, false];

        yield 'skipped' => [DetectionStatus::SKIPPED, false];

        yield 'not covered' => [DetectionStatus::NOT_COVERED, false];

        yield 'ignored' => [DetectionStatus::IGNORED, false];
    }

    public function test_nested_attempts_contribute_to_history_without_consuming_outer_follow_ups(): void
    {
        $mutant = MutantBuilder::withCompleteTestData()->build();
        $first = new EvaluationAttemptResult('First', DetectionStatus::ESCAPED);
        $second = new EvaluationAttemptResult('Second', DetectionStatus::ESCAPED);
        $third = new EvaluationAttemptResult('Follow-up', DetectionStatus::KILLED_BY_TESTS);
        $nested = MutantProcessContainer::from($mutant, static fn (): EvaluationAttemptResult => $first)
            ->append(static fn (): EvaluationAttemptResult => $second)
        ;
        $strategy = $this->createMock(EvaluationStrategy::class);
        $strategy
            ->expects($this->exactly(2))
            ->method('decide')
            ->willReturnMap([
                [[$first, $second], true, new RunEvaluation()],
                [[$first, $second, $third], false, new FinishEvaluation(DetectionStatus::KILLED_BY_TESTS)],
            ])
        ;
        $pipe = MutantProcessContainer::from($mutant, static fn (): MutantProcessContainer => $nested)
            ->append(static fn (): EvaluationAttemptResult => $third)
            ->withStrategy($strategy)
        ;

        $this->assertSame($first, $pipe->getCurrent());
        $this->assertTrue($pipe->hasNext());
        $this->assertSame($second, $pipe->createNext());
        $this->assertTrue($pipe->hasNext());
        $this->assertSame($third, $pipe->createNext());
        $this->assertFalse($pipe->hasNext());

        $result = $pipe->getMutantExecutionResult();

        $this->assertSame([$first, $second, $third], $result->getEvaluationHistory());
        $this->assertSame(DetectionStatus::KILLED_BY_TESTS, $result->getDetectionStatus());
        $this->assertSame($result, $pipe->getMutantExecutionResult());
    }
}
