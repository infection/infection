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
use Infection\Mutant\DetectionStatus;
use Infection\Mutant\DirectEvaluationResult;
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
    public function test_it_preserves_the_first_evaluation_without_a_follow_up_framework(): void
    {
        $mutant = MutantBuilder::withMinimalTestData()->build();

        $firstProcess = $this->createStub(MutantProcess::class);
        $firstEvaluation = MutantProcessContainer::from($mutant, static fn (): MutantProcess => $firstProcess);

        $firstTestFramework = $this->createMock(TestFramework::class);
        $firstTestFramework
            ->expects($this->once())
            ->method('test')
            ->with($this->identicalTo($mutant))
            ->willReturn($firstEvaluation)
        ;

        $framework = new CombinedTestFramework($firstTestFramework, null);

        $evaluation = $framework->test($mutant);

        $this->assertTrue($evaluation->isCold());
        $this->assertSame($firstProcess, $evaluation->getCurrent());
    }

    #[DataProvider('evaluationStatusProvider')]
    public function test_it_only_creates_follow_up_processes_after_the_first_evaluation_escapes(
        DetectionStatus $status,
        bool $hasNext,
    ): void {
        $mutant = MutantBuilder::withMinimalTestData()->build();

        $followUpTestFramework = $this->createMock(TestFramework::class);
        $followUpProcess = $this->createStub(MutantProcess::class);
        $followUpProcess
            ->method('getResult')
            ->willReturn(DetectionStatus::KILLED_BY_STATIC_ANALYSIS)
        ;

        $firstProcess = $this->createStub(MutantProcess::class);
        $firstProcess
            ->method('getResult')
            ->willReturn($status)
        ;
        $firstEvaluation = MutantProcessContainer::from($mutant, static fn (): MutantProcess => $firstProcess);
        $firstTestFramework = $this->createMock(TestFramework::class);
        $firstTestFramework
            ->expects($this->once())
            ->method('test')
            ->with($this->identicalTo($mutant))
            ->willReturn($firstEvaluation)
        ;

        $followUpEvaluation = MutantProcessContainer::from($mutant, static fn (): MutantProcess => $followUpProcess);
        $followUpTestFramework
            ->expects(
                $hasNext
                    ? $this->once()
                    : $this->never(),
            )
            ->method('test')
            ->with($this->identicalTo($mutant))
            ->willReturn($followUpEvaluation)
        ;

        $framework = new CombinedTestFramework($firstTestFramework, $followUpTestFramework);
        $evaluation = $framework->test($mutant);

        $this->assertTrue($evaluation->isCold());
        $this->assertSame($firstProcess, $evaluation->getCurrent());
        $this->assertSame($hasNext, $evaluation->hasNext());
        $this->assertTrue($followUpEvaluation->isCold());

        if ($hasNext) {
            $this->assertSame($followUpProcess, $evaluation->createNext());
            $this->assertSame($followUpProcess, $evaluation->getCurrent());
            $this->assertFalse($followUpEvaluation->isCold());
            $this->assertFalse($evaluation->hasNext());
        }
    }

    /**
     * @param Closure(CombinedTestFramework): mixed $operation
     */
    #[DataProvider('unsupportedOperationProvider')]
    public function test_it_rejects_operations_that_still_use_the_individual_frameworks(Closure $operation): void
    {
        $framework = new CombinedTestFramework($this->createStub(TestFramework::class), null);

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

    #[DataProvider('evaluationStatusProvider')]
    public function test_it_composes_direct_results_using_the_same_continuation_rule(DetectionStatus $status, bool $hasNext): void
    {
        $mutant = MutantBuilder::withCompleteTestData()->build();
        $first = new DirectEvaluationResult('First evaluator', $status, 'First output');
        $last = new DirectEvaluationResult('Second evaluator', DetectionStatus::KILLED_BY_STATIC_ANALYSIS, 'Second output');
        $firstTestFramework = $this->createStub(TestFramework::class);
        $firstTestFramework
            ->method('test')
            ->willReturn($first)
        ;
        $followUpTestFramework = $this->createMock(TestFramework::class);
        $followUpTestFramework
            ->expects(
                $hasNext
                    ? $this->once()
                    : $this->never(),
            )
            ->method('test')
            ->with($this->identicalTo($mutant))
            ->willReturn($last)
        ;
        $framework = new CombinedTestFramework($firstTestFramework, $followUpTestFramework);

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
        $result = new DirectEvaluationResult('Evaluator', DetectionStatus::ESCAPED, '');
        $firstTestFramework = $this->createStub(TestFramework::class);
        $firstTestFramework
            ->method('test')
            ->willReturn($result)
        ;
        $framework = new CombinedTestFramework($firstTestFramework, null);

        $evaluation = $framework->test($mutant);
        $evaluation->getCurrent();
        $this->assertSame([$result], $evaluation->getMutantExecutionResult()->getEvaluationHistory());
    }

    public function test_it_preserves_a_frameworks_whole_pipe_before_invoking_the_next_framework(): void
    {
        $mutant = MutantBuilder::withCompleteTestData()->build();
        $first = new DirectEvaluationResult('First evaluator, first attempt', DetectionStatus::ESCAPED, '');
        $second = new DirectEvaluationResult('First evaluator, second attempt', DetectionStatus::ESCAPED, '');
        $third = new DirectEvaluationResult('Follow-up evaluator, first attempt', DetectionStatus::ESCAPED, '');
        $fourth = new DirectEvaluationResult('Follow-up evaluator, second attempt', DetectionStatus::KILLED_BY_STATIC_ANALYSIS, '');
        $firstEvaluation = MutantProcessContainer::from($mutant, static fn (): DirectEvaluationResult => $first)
            ->append(static fn (): DirectEvaluationResult => $second)
        ;
        $followUpEvaluation = MutantProcessContainer::from($mutant, static fn (): DirectEvaluationResult => $third)
            ->append(static fn (): DirectEvaluationResult => $fourth)
        ;
        $firstTestFramework = $this->createMock(TestFramework::class);
        $firstTestFramework
            ->expects($this->once())
            ->method('test')
            ->willReturn($firstEvaluation)
        ;
        $followUpTestFramework = $this->createMock(TestFramework::class);
        $followUpTestFramework
            ->expects($this->once())
            ->method('test')
            ->willReturnCallback(function () use ($firstEvaluation, $first, $second, $followUpEvaluation) {
                $this->assertSame([$first, $second], $firstEvaluation->getMutantExecutionResult()->getEvaluationHistory());

                return $followUpEvaluation;
            })
        ;
        $framework = new CombinedTestFramework($firstTestFramework, $followUpTestFramework);
        $pipe = $framework->test($mutant);

        $this->assertSame($first, $pipe->getCurrent());
        $this->assertSame($second, $pipe->createNext());
        $this->assertSame($third, $pipe->createNext());
        $this->assertSame($fourth, $pipe->createNext());
        $this->assertFalse($pipe->hasNext());
        $this->assertSame([$first, $second, $third, $fourth], $pipe->getMutantExecutionResult()->getEvaluationHistory());
    }
}
