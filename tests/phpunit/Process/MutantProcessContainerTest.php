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

use Closure;
use Infection\Mutant\DetectionStatus;
use Infection\Mutant\EvaluationAttemptResult;
use Infection\Mutant\EvaluationProcessDetails;
use Infection\Mutant\EvaluationStrategy;
use Infection\Mutant\FinishEvaluation;
use Infection\Mutant\RunEvaluation;
use Infection\Process\MutantProcess;
use Infection\Process\MutantProcessContainer;
use Infection\Tests\Mutant\MutantBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;
use Webmozart\Assert\InvalidArgumentException;

#[CoversClass(MutantProcessContainer::class)]
final class MutantProcessContainerTest extends TestCase
{
    public function test_it_exposes_the_mutant_without_starting_evaluation(): void
    {
        $mutant = MutantBuilder::withMinimalTestData()->build();
        $process = $this->createStub(MutantProcess::class);
        $container = MutantProcessContainer::from($mutant, static fn (): MutantProcess => $process);

        $this->assertSame($mutant, $container->getMutant());
        $this->assertTrue($container->isCold());
        $this->assertSame($process, $container->getCurrent());
        $this->assertFalse($container->isCold());
    }

    public function test_it_finishes_a_completed_attempt_without_a_follow_up_factory(): void
    {
        $result = new EvaluationAttemptResult('Evaluator', DetectionStatus::ESCAPED, '', null);
        $container = MutantProcessContainer::from(MutantBuilder::withCompleteTestData()->build(), static fn (): EvaluationAttemptResult => $result);

        $this->assertSame($result, $container->getCurrent());
        $this->assertFalse($container->hasNext());
    }

    #[DataProvider('detectionStatusProvider')]
    public function test_only_escaped_mutants_have_a_follow_up_process(DetectionStatus $status, bool $hasNext): void
    {
        $process = $this->createStub(MutantProcess::class);
        $process->method('getFinishedAt')->willReturn(2.0);
        $result = new EvaluationAttemptResult(
            'PHPUnit',
            $status,
            'output',
            new EvaluationProcessDetails('phpunit', 1.0),
        );
        $process
            ->method('getEvaluationAttemptResult')
            ->willReturn($result)
        ;

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
        $escaped = new EvaluationAttemptResult(
            'PHPUnit',
            DetectionStatus::ESCAPED,
            'output',
            new EvaluationProcessDetails('phpunit', 1.0),
        );
        $first
            ->method('getEvaluationAttemptResult')
            ->willReturn($escaped)
        ;
        $second
            ->method('getEvaluationAttemptResult')
            ->willReturn($escaped)
        ;
        $killed = new EvaluationAttemptResult(
            'PHPUnit',
            DetectionStatus::KILLED_BY_STATIC_ANALYSIS,
            'output',
            new EvaluationProcessDetails('phpunit', 1.0),
        );
        $third
            ->method('getEvaluationAttemptResult')
            ->willReturn($killed)
        ;
        $calls = new stdClass();
        $calls->first = 0;
        $calls->second = 0;

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
        $first = new EvaluationAttemptResult(
            'PHPUnit',
            $firstStatus,
            'test output',
            new EvaluationProcessDetails('phpunit', 1.25),
        );
        $firstProcess = $this->createStub(MutantProcess::class);
        $firstProcess->method('getFinishedAt')->willReturn(2.0);
        $firstProcess->method('getEvaluationAttemptResult')->willReturn($first);
        $firstProcess->method('getMutant')->willReturn($mutant);
        $container = MutantProcessContainer::from($mutant, static fn (): MutantProcess => $firstProcess);
        $container->getCurrent();
        $expectedHistory = [$first];
        $last = $first;

        if ($secondStatus !== null) {
            $last = new EvaluationAttemptResult(
                'PHPStan',
                $secondStatus,
                'analysis output',
                new EvaluationProcessDetails('phpstan', 0.25),
            );
            $secondProcess = $this->createStub(MutantProcess::class);
            $secondProcess->method('getFinishedAt')->willReturn(3.0);
            $secondProcess->method('getEvaluationAttemptResult')->willReturn($last);
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
        $processDetails = $last->process;

        $this->assertSame($expectedHistory, $actual->getEvaluationHistory());
        $this->assertSame($last->detectionStatus, $actual->getDetectionStatus());
        $this->assertNotNull($processDetails);
        $this->assertSame($processDetails->commandLine, $actual->getProcessCommandLine());
        $this->assertSame($last->output, $actual->getProcessOutput());
        $this->assertSame($processDetails->runtime, $actual->getProcessRuntime());
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

    public function test_it_cannot_report_an_unfinished_process(): void
    {
        $process = $this->createMock(MutantProcess::class);
        $process->method('getFinishedAt')->willReturn(0.0);
        $process->expects($this->never())->method('getEvaluationAttemptResult');
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
        $attempt = new EvaluationAttemptResult('Evaluator', DetectionStatus::ESCAPED, 'Attempt output', null);
        $unused = function (): EvaluationAttemptResult {
            $this->fail('The strategy has decided that no further evaluation is needed.');
        };
        $strategy = $this->createMock(EvaluationStrategy::class);
        $strategy
            ->expects($this->once())
            ->method('decide')
            ->with(
                [$attempt],
                $this->logicalAnd($this->countOf(2), $this->arrayHasKey(1)),
            )
            ->willReturn(new FinishEvaluation(DetectionStatus::KILLED_BY_TESTS))
        ;
        $unconfigured = MutantProcessContainer::from($mutant, static fn (): EvaluationAttemptResult => $attempt)->append($unused);
        $evaluation = $unconfigured->withStrategy($strategy);

        $this->assertNotSame($unconfigured, $evaluation);
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
        $result = new EvaluationAttemptResult('In-memory analyser', DetectionStatus::KILLED_BY_STATIC_ANALYSIS, 'Type mismatch', null);
        $evaluation = MutantProcessContainer::from($mutant, static fn (): EvaluationAttemptResult => $result);

        $evaluation->getCurrent();
        $final = $evaluation->getMutantExecutionResult();

        $this->assertSame($result, $evaluation->getCurrent());
        $this->assertSame([$result], $final->getEvaluationHistory());
        $this->assertSame(DetectionStatus::KILLED_BY_STATIC_ANALYSIS, $final->getDetectionStatus());
        $this->assertSame('Type mismatch', $final->getProcessOutput());
        $this->assertSame('', $final->getProcessCommandLine());
        $this->assertSame(0.0, $final->getProcessRuntime());
        $this->assertSame($mutant->getMutation()->getHash(), $final->getMutantHash());
    }

    public function test_it_cannot_finalize_while_a_follow_up_is_pending(): void
    {
        $process = $this->createStub(MutantProcess::class);
        $process->method('getFinishedAt')->willReturn(2.0);
        $process->method('getEvaluationAttemptResult')->willReturn(
            new EvaluationAttemptResult(
                'PHPUnit',
                DetectionStatus::ESCAPED,
                '',
                new EvaluationProcessDetails('phpunit', 1.0),
            ),
        );
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

    public function test_a_childs_selected_status_drives_the_enclosing_strategy(): void
    {
        $mutant = MutantBuilder::withCompleteTestData()->build();
        $first = new EvaluationAttemptResult('Tests', DetectionStatus::ERROR, '', null);
        $second = new EvaluationAttemptResult('Analysis', DetectionStatus::KILLED_BY_STATIC_ANALYSIS, '', null);
        $strategy = $this->createMock(EvaluationStrategy::class);
        $strategy->expects($this->once())->method('decide')->willReturn(new FinishEvaluation(DetectionStatus::ESCAPED));
        $child = MutantProcessContainer::from($mutant, static fn (): EvaluationAttemptResult => $first)->withStrategy($strategy);
        $container = MutantProcessContainer::from($mutant, static fn (): MutantProcessContainer => $child)
            ->append(static fn (): EvaluationAttemptResult => $second)
        ;

        $this->assertSame($first, $container->getCurrent());
        $this->assertTrue($container->hasNext());
        $this->assertSame($second, $container->createNext());
        $result = $container->getMutantExecutionResult();

        $this->assertSame([$first, $second], $result->getEvaluationHistory());
        $this->assertSame(DetectionStatus::KILLED_BY_STATIC_ANALYSIS, $result->getDetectionStatus());
    }

    public function test_it_finishes_nested_follow_ups_without_another_outer_evaluator(): void
    {
        $mutant = MutantBuilder::withCompleteTestData()->build();
        $first = new EvaluationAttemptResult('Tests', DetectionStatus::ESCAPED, '', null);
        $last = new EvaluationAttemptResult('Analysis', DetectionStatus::KILLED_BY_STATIC_ANALYSIS, '', null);
        $child = MutantProcessContainer::from($mutant, static fn (): EvaluationAttemptResult => $first)
            ->append(static fn (): EvaluationAttemptResult => $last)
        ;
        $container = MutantProcessContainer::from($mutant, static fn (): MutantProcessContainer => $child);

        $this->assertSame($first, $container->getCurrent());
        $this->assertTrue($container->hasNext());
        $this->assertSame($last, $container->createNext());
        $this->assertFalse($container->hasNext());
        $this->assertSame([$first, $last], $container->getMutantExecutionResult()->getEvaluationHistory());
    }

    public function test_the_strategy_can_select_another_evaluator_and_retry_it_after_a_nested_history(): void
    {
        $mutant = MutantBuilder::withCompleteTestData()->build();
        $first = new EvaluationAttemptResult('First', DetectionStatus::ESCAPED, '', null);
        $second = new EvaluationAttemptResult('Second', DetectionStatus::ESCAPED, '', null);
        $third = new EvaluationAttemptResult('Selected', DetectionStatus::ERROR, 'first result', null);
        $fourth = new EvaluationAttemptResult('Selected', DetectionStatus::ESCAPED, 'retried result', null);
        $nested = MutantProcessContainer::from($mutant, static fn (): EvaluationAttemptResult => $first)
            ->append(static fn (): EvaluationAttemptResult => $second)
        ;
        $calls = 0;
        $selected = static function () use (&$calls, $third, $fourth): EvaluationAttemptResult {
            return ++$calls === 1
                ? $third
                : $fourth;
        };
        $strategy = $this->createMock(EvaluationStrategy::class);
        $strategy->expects($this->exactly(3))->method('decide')->willReturnCallback(
            function (array $history, array $evaluations, ?Closure $next, DetectionStatus $status) use ($first, $second, $third, $fourth): RunEvaluation|FinishEvaluation {
                $this->assertCount(3, $evaluations);

                if ($history === [$first, $second]) {
                    $this->assertSame($evaluations[1], $next);

                    return new RunEvaluation($evaluations[2]);
                }

                if ($history === [$first, $second, $third]) {
                    $this->assertNull($next);

                    return new RunEvaluation($evaluations[2]);
                }

                $this->assertSame([$first, $second, $third, $fourth], $history);

                return new FinishEvaluation(DetectionStatus::KILLED_BY_TESTS);
            },
        );
        $pipe = MutantProcessContainer::from($mutant, static fn (): MutantProcessContainer => $nested)
            ->append(function (): EvaluationAttemptResult {
                $this->fail('The strategy selected the third evaluator.');
            })
            ->append($selected)
            ->withStrategy($strategy)
        ;

        $this->assertSame($first, $pipe->getCurrent());
        $this->assertSame($second, $pipe->createNext());
        $this->assertTrue($pipe->hasNext());
        $this->assertSame($third, $pipe->createNext());
        $this->assertSame($fourth, $pipe->createNext());
        $this->assertFalse($pipe->hasNext());
        $result = $pipe->getMutantExecutionResult();

        $this->assertSame(2, $calls);
        $this->assertSame([$first, $second, $third, $fourth], $result->getEvaluationHistory());
        $this->assertSame(DetectionStatus::KILLED_BY_TESTS, $result->getDetectionStatus());
        $this->assertSame($result, $pipe->getMutantExecutionResult());
    }
}
