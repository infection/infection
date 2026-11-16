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

namespace Infection\Tests\TestFramework;

use BadMethodCallException;
use Closure;
use function count;
use Infection\Mutant\DetectionStatus;
use Infection\Mutant\EvaluationAttemptResult;
use Infection\Mutant\EvaluationStrategy;
use Infection\Mutant\FinishEvaluation;
use Infection\Mutant\RunEvaluation;
use Infection\Mutant\SequentialEvaluationStrategy;
use Infection\Process\MutantProcess;
use Infection\Process\MutantProcessContainer;
use Infection\TestFramework\CombinedTestFramework;
use Infection\TestFramework\Contracts\TestFramework;
use Infection\Tests\Mutant\MutantBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(CombinedTestFramework::class)]
final class CombinedTestFrameworkTest extends TestCase
{
    public function test_it_preserves_the_test_framework_evaluation_when_static_analysis_is_disabled(): void
    {
        $mutant = MutantBuilder::withMinimalTestData()->build();
        $attempt = $this->createStub(MutantProcess::class);
        $evaluation = MutantProcessContainer::from($mutant, static fn (): MutantProcess => $attempt);
        $testFramework = $this->createMock(TestFramework::class);
        $testFramework
            ->expects($this->once())
            ->method('test')
            ->with($this->identicalTo($mutant))
            ->willReturn($evaluation)
        ;

        $framework = new CombinedTestFramework($testFramework, null, new SequentialEvaluationStrategy());

        $pipe = $framework->test($mutant);
        $this->assertTrue($pipe->isCold());
        $this->assertSame($attempt, $pipe->getCurrent());
    }

    #[DataProvider('evaluationStatusProvider')]
    public function test_it_only_creates_static_analysis_processes_after_the_tests_escape(DetectionStatus $status, bool $hasNext): void
    {
        $mutant = MutantBuilder::withMinimalTestData()->build();
        $testProcess = $this->createStub(MutantProcess::class);
        $staticAnalysisProcess = $this->createStub(MutantProcess::class);
        $testProcess->method('getFinishedAt')->willReturn(2.0);
        $staticAnalysisProcess->method('getFinishedAt')->willReturn(3.0);
        $staticAnalysisProcess->method('getEvaluationAttemptResult')->willReturn(
            new EvaluationAttemptResult('PHPStan', DetectionStatus::KILLED_BY_STATIC_ANALYSIS, '', null),
        );
        $result = new EvaluationAttemptResult('PHPUnit', $status, '', null);
        $testProcess->method('getEvaluationAttemptResult')->willReturn($result);
        $testEvaluation = MutantProcessContainer::from($mutant, static fn (): MutantProcess => $testProcess);
        $testFramework = $this->createMock(TestFramework::class);
        $testFramework->expects($this->once())->method('test')->with($this->identicalTo($mutant))->willReturn($testEvaluation);
        $created = false;
        $staticEvaluation = MutantProcessContainer::from(
            $mutant,
            static function () use (&$created, $staticAnalysisProcess): MutantProcess {
                $created = true;

                return $staticAnalysisProcess;
            },
        );
        $staticAnalysis = $this->createMock(TestFramework::class);
        $staticAnalysis->expects($hasNext ? $this->once() : $this->never())->method('test')->with($this->identicalTo($mutant))->willReturn($staticEvaluation);

        $framework = new CombinedTestFramework($testFramework, $staticAnalysis, new SequentialEvaluationStrategy());
        $evaluation = $framework->test($mutant);

        $this->assertTrue($evaluation->isCold());
        $this->assertSame($testProcess, $evaluation->getCurrent());
        $this->assertSame($hasNext, $evaluation->hasNext());
        $this->assertFalse($created);

        if ($hasNext) {
            $this->assertSame($staticAnalysisProcess, $evaluation->createNext());
            $this->assertSame($staticAnalysisProcess, $evaluation->getCurrent());
            $this->assertTrue($created);
            $this->assertFalse($evaluation->hasNext());
        }
    }

    /**
     * @param Closure(CombinedTestFramework): mixed $operation
     */
    #[DataProvider('unsupportedOperationProvider')]
    public function test_it_rejects_operations_that_still_use_the_individual_frameworks(Closure $operation): void
    {
        $framework = new CombinedTestFramework($this->createStub(TestFramework::class), null, new SequentialEvaluationStrategy());

        $this->expectExceptionObject(new BadMethodCallException('CombinedTestFramework only supports mutant evaluation.'));

        $operation($framework);
    }

    public static function evaluationStatusProvider(): iterable
    {
        yield 'escaped' => [DetectionStatus::ESCAPED, true];

        yield 'killed by tests' => [DetectionStatus::KILLED_BY_TESTS, false];

        yield 'error' => [DetectionStatus::ERROR, false];

        yield 'timeout' => [DetectionStatus::TIMED_OUT, false];

        yield 'syntax error' => [DetectionStatus::SYNTAX_ERROR, false];
    }

    public static function unsupportedOperationProvider(): iterable
    {
        yield 'name' => [static fn (CombinedTestFramework $framework) => $framework->getName()];

        yield 'version' => [static fn (CombinedTestFramework $framework) => $framework->getVersion()];

        yield 'requirements' => [static fn (CombinedTestFramework $framework) => $framework->checkRequirements()];

        yield 'initial run' => [static fn (CombinedTestFramework $framework) => $framework->executeInitialRun()];

        yield 'JUnit availability' => [
            // @phpstan-ignore method.deprecated (The combined framework must reject this legacy API too.)
            static fn (CombinedTestFramework $framework) => $framework->hasJUnitReport(),
        ];
    }

    public function test_it_uses_the_configured_strategy_to_finalize_before_static_analysis(): void
    {
        $mutant = MutantBuilder::withCompleteTestData()->build();
        $attempt = new EvaluationAttemptResult('Evaluator', DetectionStatus::ESCAPED, '', null);
        $testFramework = $this->createStub(TestFramework::class);
        $testFramework->method('test')->willReturn($attempt);
        $staticAnalysis = $this->createMock(TestFramework::class);
        $staticAnalysis->expects($this->never())->method('test');
        $strategy = $this->createMock(EvaluationStrategy::class);
        $strategy->expects($this->once())->method('decide')->willReturn(new FinishEvaluation(DetectionStatus::ERROR));
        $framework = new CombinedTestFramework($testFramework, $staticAnalysis, $strategy);

        $evaluation = $framework->test($mutant);
        $evaluation->getCurrent();
        $final = $evaluation->getMutantExecutionResult();

        $this->assertSame(DetectionStatus::ERROR, $final->getDetectionStatus());
        $this->assertSame([$attempt], $final->getEvaluationHistory());
    }

    #[DataProvider('evaluationStatusProvider')]
    public function test_it_composes_direct_results_using_the_same_continuation_rule(DetectionStatus $status, bool $hasNext): void
    {
        $mutant = MutantBuilder::withCompleteTestData()->build();
        $first = new EvaluationAttemptResult('First evaluator', $status, 'First output', null);
        $last = new EvaluationAttemptResult('Second evaluator', DetectionStatus::KILLED_BY_STATIC_ANALYSIS, 'Second output', null);
        $testFramework = $this->createStub(TestFramework::class);
        $testFramework->method('test')->willReturn($first);
        $staticAnalysis = $this->createMock(TestFramework::class);
        $staticAnalysis
            ->expects($hasNext ? $this->once() : $this->never())
            ->method('test')
            ->with($this->identicalTo($mutant))
            ->willReturn($last)
        ;
        $framework = new CombinedTestFramework($testFramework, $staticAnalysis, new SequentialEvaluationStrategy());

        $evaluation = $framework->test($mutant);

        $this->assertInstanceOf(MutantProcessContainer::class, $evaluation);
        $this->assertSame($first, $evaluation->getCurrent());
        $this->assertSame($hasNext, $evaluation->hasNext());

        if ($hasNext) {
            $this->assertSame($last, $evaluation->createNext());
            $this->assertSame([$first, $last], $evaluation->getMutantExecutionResult()->getEvaluationHistory());

            return;
        }

        $this->assertSame([$first], $evaluation->getMutantExecutionResult()->getEvaluationHistory());
    }

    public function test_it_finalizes_a_direct_result_when_no_other_evaluator_is_configured(): void
    {
        $mutant = MutantBuilder::withCompleteTestData()->build();
        $result = new EvaluationAttemptResult('In-memory analyser', DetectionStatus::ESCAPED, '', null);
        $testFramework = $this->createStub(TestFramework::class);
        $testFramework->method('test')->willReturn($result);
        $framework = new CombinedTestFramework($testFramework, null, new SequentialEvaluationStrategy());

        $evaluation = $framework->test($mutant);
        $evaluation->getCurrent();
        $this->assertSame([$result], $evaluation->getMutantExecutionResult()->getEvaluationHistory());
    }

    public function test_retrying_a_direct_framework_invokes_it_again_and_can_select_an_earlier_status(): void
    {
        $mutant = MutantBuilder::withCompleteTestData()->build();
        $first = new EvaluationAttemptResult('Tests', DetectionStatus::TIMED_OUT, 'first', null);
        $retry = new EvaluationAttemptResult('Tests', DetectionStatus::ESCAPED, 'fresh retry', null);
        $last = new EvaluationAttemptResult('Analysis', DetectionStatus::ERROR, 'analysis error', null);
        $tests = $this->createMock(TestFramework::class);
        $tests->expects($this->exactly(2))->method('test')->with($mutant)->willReturnOnConsecutiveCalls($first, $retry);
        $analysis = $this->createMock(TestFramework::class);
        $analysis->expects($this->once())->method('test')->with($mutant)->willReturn($last);
        $strategy = $this->createMock(EvaluationStrategy::class);
        $strategy->expects($this->exactly(3))->method('decide')->willReturnCallback(
            static function (array $history, array $evaluations, ?Closure $next, DetectionStatus $status): RunEvaluation|FinishEvaluation {
                return match (count($history)) {
                    1 => new RunEvaluation($evaluations[0]),
                    2 => new RunEvaluation($next),
                    3 => new FinishEvaluation($history[1]->detectionStatus),
                };
            },
        );
        $framework = new CombinedTestFramework($tests, $analysis, $strategy);
        $pipe = $framework->test($mutant);

        $this->assertSame($first, $pipe->getCurrent());
        $this->assertTrue($pipe->hasNext());
        $this->assertSame($retry, $pipe->createNext());
        $this->assertTrue($pipe->hasNext());
        $this->assertSame($last, $pipe->createNext());
        $this->assertFalse($pipe->hasNext());
        $result = $pipe->getMutantExecutionResult();

        $this->assertSame([$first, $retry, $last], $result->getEvaluationHistory());
        $this->assertSame(DetectionStatus::ESCAPED, $result->getDetectionStatus());
        $this->assertSame($result, $pipe->getMutantExecutionResult());
    }

    public function test_it_preserves_a_frameworks_whole_pipe_before_invoking_the_next_framework(): void
    {
        $mutant = MutantBuilder::withCompleteTestData()->build();
        $first = new EvaluationAttemptResult('First tests', DetectionStatus::ESCAPED, '', null);
        $second = new EvaluationAttemptResult('Second tests', DetectionStatus::ESCAPED, '', null);
        $third = new EvaluationAttemptResult('First analysis', DetectionStatus::ESCAPED, '', null);
        $fourth = new EvaluationAttemptResult('Second analysis', DetectionStatus::KILLED_BY_STATIC_ANALYSIS, '', null);
        $testsPipe = MutantProcessContainer::from($mutant, static fn (): EvaluationAttemptResult => $first)
            ->append(static fn (): EvaluationAttemptResult => $second)
        ;
        $analysisPipe = MutantProcessContainer::from($mutant, static fn (): EvaluationAttemptResult => $third)
            ->append(static fn (): EvaluationAttemptResult => $fourth)
        ;
        $tests = $this->createMock(TestFramework::class);
        $tests->expects($this->once())->method('test')->willReturn($testsPipe);
        $analysis = $this->createMock(TestFramework::class);
        $analysis->expects($this->once())->method('test')->willReturnCallback(function () use ($testsPipe, $first, $second, $analysisPipe) {
            $this->assertSame([$first, $second], $testsPipe->getMutantExecutionResult()->getEvaluationHistory());

            return $analysisPipe;
        });
        $framework = new CombinedTestFramework($tests, $analysis, new SequentialEvaluationStrategy());
        $pipe = $framework->test($mutant);

        $this->assertSame($first, $pipe->getCurrent());
        $this->assertSame($second, $pipe->createNext());
        $this->assertSame($third, $pipe->createNext());
        $this->assertSame($fourth, $pipe->createNext());
        $this->assertFalse($pipe->hasNext());
        $this->assertSame([$first, $second, $third, $fourth], $pipe->getMutantExecutionResult()->getEvaluationHistory());
    }
}
