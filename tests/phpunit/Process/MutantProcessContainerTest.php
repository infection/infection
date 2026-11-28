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
use Infection\Process\MutantProcess;
use Infection\Process\MutantProcessContainer;
use Infection\Testing\TestFramework\Debug\FixedMutantProcessDetectionStatusResolver;
use Infection\Tests\Mutant\MutantBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\Process\Process;
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
        $result = $this->createStub(MutantProcess::class);
        $result->method('getResult')->willReturn(DetectionStatus::ESCAPED);
        $container = MutantProcessContainer::from(MutantBuilder::withCompleteTestData()->build(), static fn (): MutantProcess => $result);

        $this->assertSame($result, $container->getCurrent());
        $this->assertFalse($container->hasNext());
    }

    #[DataProvider('detectionStatusProvider')]
    public function test_only_escaped_mutants_have_a_follow_up_process(DetectionStatus $status, bool $hasNext): void
    {
        $process = $this->createStub(MutantProcess::class);
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
        $first->method('getResult')->willReturn(DetectionStatus::ESCAPED);
        $second->method('getResult')->willReturn(DetectionStatus::ESCAPED);
        $third->method('getResult')->willReturn(DetectionStatus::KILLED_BY_STATIC_ANALYSIS);
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
        $first = $this->stubCompletedProcess($firstStatus, 'phpunit', 'test output', 1.25);
        $container = MutantProcessContainer::from($mutant, static fn (): MutantProcess => $first);
        $container->getCurrent();
        $expectedHistory = [$first];
        $expectedCommand = 'phpunit';
        $expectedOutput = 'test output';
        $expectedRuntime = 1.25;

        if ($secondStatus !== null) {
            $expectedCommand = 'phpstan';
            $expectedOutput = 'analysis output';
            $expectedRuntime = 0.25;
            $second = $this->stubCompletedProcess($secondStatus, $expectedCommand, $expectedOutput, $expectedRuntime);
            $container->append(static fn (): MutantProcess => $second);
            $this->assertTrue($container->hasNext());
            $container->createNext();
            $expectedHistory[] = $second;
        }

        $expectedStatus = $secondStatus ?? $firstStatus;

        if ($expectedStatus !== DetectionStatus::ESCAPED) {
            $container->append(function (): MutantProcess {
                $this->fail('Finalization must not create unused processes.');
            });
        }

        $actual = $container->getMutantExecutionResult();

        $this->assertSame($expectedHistory, $actual->getEvaluationHistory());
        $this->assertSame($expectedStatus, $actual->getDetectionStatus());
        $this->assertSame($expectedCommand, $actual->getProcessCommandLine());
        $this->assertSame($expectedOutput, $actual->getProcessOutput());
        $this->assertSame($expectedRuntime, $actual->getProcessRuntime());
        $this->assertSame($actual, $container->getMutantExecutionResult());
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
        $mutant = MutantBuilder::withMinimalTestData()->build();
        $process = new MutantProcess(
            new Process(['phpunit']),
            $mutant,
            'PHPUnit',
            new FixedMutantProcessDetectionStatusResolver(DetectionStatus::ESCAPED),
            includeErrorOutput: false,
        );
        $container = MutantProcessContainer::from($mutant, static fn (): MutantProcess => $process);
        $container->getCurrent();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot resolve the detection status before the mutant process has finished.');

        $container->getMutantExecutionResult();
    }

    public function test_it_cannot_finalize_while_a_follow_up_is_pending(): void
    {
        $process = $this->createStub(MutantProcess::class);
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

    public function test_it_stops_after_a_nested_follow_up_detects_the_mutant(): void
    {
        $mutant = MutantBuilder::withCompleteTestData()->build();
        $first = $this->stubCompletedProcess(DetectionStatus::ESCAPED, 'phpunit', 'test output', 1.25);
        $last = $this->stubCompletedProcess(DetectionStatus::KILLED_BY_STATIC_ANALYSIS, 'phpstan', 'analysis output', 0.25);
        $child = MutantProcessContainer::from($mutant, static fn (): MutantProcess => $first)
            ->append(static fn (): MutantProcess => $last)
        ;
        $container = MutantProcessContainer::from($mutant, static fn (): MutantProcessContainer => $child)
            ->append(function (): MutantProcess {
                $this->fail('A nested follow-up detected the mutant, so the outer follow-up must stay unused.');
            })
        ;

        $this->assertSame($first, $container->getCurrent());
        $this->assertTrue($container->hasNext());
        $this->assertSame($last, $container->createNext());
        $this->assertFalse($container->hasNext());
        $result = $container->getMutantExecutionResult();

        $this->assertSame([$first, $last], $result->getEvaluationHistory());
        $this->assertSame(DetectionStatus::KILLED_BY_STATIC_ANALYSIS, $result->getDetectionStatus());
    }

    public function test_it_finishes_nested_follow_ups_without_another_outer_evaluator(): void
    {
        $mutant = MutantBuilder::withCompleteTestData()->build();
        $first = $this->stubCompletedProcess(DetectionStatus::ESCAPED, 'phpunit', 'test output', 1.25);
        $last = $this->stubCompletedProcess(DetectionStatus::KILLED_BY_STATIC_ANALYSIS, 'phpstan', 'analysis output', 0.25);
        $child = MutantProcessContainer::from($mutant, static fn (): MutantProcess => $first)
            ->append(static fn (): MutantProcess => $last)
        ;
        $container = MutantProcessContainer::from($mutant, static fn (): MutantProcessContainer => $child);

        $this->assertSame($first, $container->getCurrent());
        $this->assertTrue($container->hasNext());
        $this->assertSame($last, $container->createNext());
        $this->assertFalse($container->hasNext());
        $this->assertSame([$first, $last], $container->getMutantExecutionResult()->getEvaluationHistory());
    }

    private function stubCompletedProcess(DetectionStatus $status, string $command, string $output, float $runtime): MutantProcess
    {
        $process = $this->createStub(Process::class);
        $process->method('getCommandLine')->willReturn($command);
        $attempt = $this->createStub(MutantProcess::class);
        $attempt->method('getProcess')->willReturn($process);
        $attempt->method('getResult')->willReturn($status);
        $attempt->method('getResultOutput')->willReturn($output);
        $attempt->method('getRuntime')->willReturn($runtime);

        return $attempt;
    }
}
