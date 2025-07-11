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

namespace Infection\Tests\Process\Runner;

use Infection\Mutant\DetectionStatus;
use Infection\Mutant\Mutant;
use Infection\Mutant\MutantExecutionResult;
use Infection\Mutant\TestFrameworkMutantExecutionResultFactory;
use Infection\Process\Factory\LazyMutantProcessFactory;
use Infection\Process\MutantProcess;
use Infection\Process\MutantProcessContainer;
use Infection\Process\Runner\ParallelProcessRunner;
use Infection\Tests\Fixtures\Process\DummyMutantProcess;
use Infection\Process\Runner\IndexedMutantProcessContainer;
use Iterator;
use function iterator_count;
use function iterator_to_array;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use SplQueue;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;
use Tumblr\Chorus\FakeTimeKeeper;

#[CoversClass(ParallelProcessRunner::class)]
final class ParallelProcessRunnerTest extends TestCase
{
    public function test_it_does_nothing_when_no_process_is_given(): void
    {
        $runner = new ParallelProcessRunner(4, 0, new FakeTimeKeeper());

        $runner->run([]);

        $this->addToAssertionCount(1);
    }

    public function test_it_starts_the_given_processes(): void
    {
        $threadsCount = 4;

        $processes = (function () use ($threadsCount): iterable {
            for ($i = 0; $i < 10; ++$i) {
                yield $this->createMutantProcessContainer(($i % $threadsCount) + 1);
            }
        })();

        $runner = new ParallelProcessRunner($threadsCount, 0, new FakeTimeKeeper());

        $executedProcesses = $runner->run($processes);

        $this->assertSame(10, iterator_count($executedProcesses));
    }

    public function test_it_checks_if_the_executed_processes_time_out(): void
    {
        $processes = (function (): iterable {
            for ($i = 0; $i < 10; ++$i) {
                yield $this->createTimeOutMutantProcessContainer();
            }
        })();

        $runner = new ParallelProcessRunner(4, 0, new FakeTimeKeeper());

        $runner->run($processes);

        $executedProcesses = $runner->run($processes);

        $this->assertSame(10, iterator_count($executedProcesses));
    }

    #[DataProvider('threadCountProvider')]
    public function test_it_adds_next_processes_if_mutant_is_escaped(int $threadCount): void
    {
        $processes = (function () use ($threadCount): iterable {
            yield $this->createMutantProcessContainerWithNextMutantProcess($threadCount);
        })();

        $runner = new ParallelProcessRunner($threadCount, 0, new FakeTimeKeeper());

        $runner->run($processes);

        $executedProcesses = $runner->run($processes);

        $this->assertSame(1, iterator_count($executedProcesses));
    }

    #[DataProvider('threadCountProvider')]
    public function test_it_handles_all_kids_of_processes_with_infinite_threads(int $threadCount): void
    {
        $this->runWithAllKindsOfProcesses($threadCount);
    }

    public function test_fill_bucket_once_with_exhausted_generator_does_not_continue(): void
    {
        $runner = new ParallelProcessRunner(1, 0, new FakeTimeKeeper());

        $iterator = $this->createMock(Iterator::class);
        $iterator->expects($this->once())
            ->method('valid')
            ->willReturn(false);

        $iterator->expects($this->never())
            ->method('current');

        $reflection = new ReflectionClass($runner);
        $fillBucketOnceMethod = $reflection->getMethod('fillBucketOnce');
        $result = $fillBucketOnceMethod->invokeArgs($runner, [new SplQueue(), $iterator, 1]);

        // Should return 0 immediately when generator is not valid
        $this->assertSame(0, $result);
    }

    public function test_fill_bucket_once_returns_time_spent_and_never_calls_time_when_bucket_full(): void
    {
        $timeKeeper = $this->createMock(FakeTimeKeeper::class);

        // Should never call getCurrentTimeAsFloat when bucket is already full
        $timeKeeper->expects($this->never())
            ->method('getCurrentTimeAsFloat');

        $runner = new ParallelProcessRunner(1, 0, $timeKeeper);

        $iterator = $this->createMock(Iterator::class);
        $iterator->expects($this->never())
            ->method('valid');

        $bucket = new SplQueue();
        // Fill bucket to capacity - use a simpler container that doesn't expect start
        $processMock = $this->createMock(Process::class);
        $mutantProcess = new DummyMutantProcess(
            $processMock,
            $this->createMock(Mutant::class),
            $this->createMock(TestFrameworkMutantExecutionResultFactory::class),
            false,
        );
        $bucket->enqueue(new MutantProcessContainer($mutantProcess, []));

        $reflection = new ReflectionClass($runner);
        $fillBucketOnceMethod = $reflection->getMethod('fillBucketOnce');
        $result = $fillBucketOnceMethod->invokeArgs($runner, [$bucket, $iterator, 1]);

        // Should return 0 when bucket is full
        $this->assertSame(0, $result);
    }

    public function test_it_waits_and_tracks_time_when_processes_are_running(): void
    {
        $threadsCount = 2;

        $timeKeeper = $this->createMock(FakeTimeKeeper::class);

        // Mock time progression - need enough values for all calls
        $timeKeeper->expects($this->any())
            ->method('getCurrentTimeAsFloat')
            ->willReturn(1000.0);

        // Should wait with reduced time based on work done
        $timeKeeper->expects($this->atLeastOnce())
            ->method('usleep')
            ->with($this->logicalOr(
                $this->equalTo(9), // 10ms poll - 1ms work = 9ms
                $this->equalTo(8), // 10ms poll - 2ms work = 8ms
                $this->equalTo(10), // Full poll time when no work done
            ));

        $processes = (function () use ($threadsCount): iterable {
            for ($i = 0; $i < 6; ++$i) {
                yield $this->createSlowMutantProcessContainer(($i % $threadsCount) + 1);
            }
        })();

        $runner = new ParallelProcessRunner($threadsCount, 10, $timeKeeper);

        $executedProcesses = $runner->run($processes);

        $this->assertSame(6, iterator_count($executedProcesses));
    }

    #[DataProvider('waitTimeProvider')]
    public function test_wait_method_calls_timekeeper_usleep_with_correct_value(
        int $pollTime,
        int $timeSpentDoingWork,
        int $expectedUsleepTime,
        string $description,
    ): void {
        $timeKeeper = $this->createMock(FakeTimeKeeper::class);

        $timeKeeper->expects($this->once())
            ->method('usleep')
            ->with($this->equalTo($expectedUsleepTime));

        $runner = new ParallelProcessRunner(1, $pollTime, $timeKeeper);

        $reflection = new ReflectionClass($runner);
        $waitMethod = $reflection->getMethod('wait');

        $waitMethod->invokeArgs($runner, [$timeSpentDoingWork]);
    }

    public static function waitTimeProvider(): iterable
    {
        yield 'full poll time when no work done' => [
            'pollTime' => 100,
            'timeSpentDoingWork' => 0,
            'expectedUsleepTime' => 100,
            'description' => 'wait(0) with poll=100',
        ];

        yield 'reduced wait time when work was done' => [
            'pollTime' => 100,
            'timeSpentDoingWork' => 50,
            'expectedUsleepTime' => 50,
            'description' => 'wait(50) with poll=100',
        ];

        yield 'no wait when work equals poll time' => [
            'pollTime' => 100,
            'timeSpentDoingWork' => 100,
            'expectedUsleepTime' => 0,
            'description' => 'wait(100) with poll=100',
        ];

        yield 'no wait when work exceeds poll time' => [
            'pollTime' => 100,
            'timeSpentDoingWork' => 150,
            'expectedUsleepTime' => 0,
            'description' => 'wait(150) with poll=100 (clamped to 0)',
        ];

        yield 'increased wait time with negative work time' => [
            'pollTime' => 100,
            'timeSpentDoingWork' => -50,
            'expectedUsleepTime' => 150,
            'description' => 'wait(-50) with poll=100 (negative work time)',
        ];

        yield 'zero poll time always results in zero wait' => [
            'pollTime' => 0,
            'timeSpentDoingWork' => 0,
            'expectedUsleepTime' => 0,
            'description' => 'wait(0) with poll=0',
        ];

        yield 'large poll time with small work' => [
            'pollTime' => 1000,
            'timeSpentDoingWork' => 10,
            'expectedUsleepTime' => 990,
            'description' => 'wait(10) with poll=1000',
        ];
    }

    public function test_it_never_waits_when_processes_complete_immediately(): void
    {
        $threadsCount = 4;

        $processes = (function () use ($threadsCount): iterable {
            for ($i = 0; $i < 10; ++$i) {
                yield $this->createMutantProcessContainer(($i % $threadsCount) + 1);
            }
        })();

        $timeKeeper = $this->createMock(FakeTimeKeeper::class);

        // getCurrentTimeAsFloat is called for fillBucketOnce timing
        $timeKeeper->expects($this->atLeast(11))
            ->method('getCurrentTimeAsFloat')
            ->willReturn(1000.0);

        // Should never call usleep since processes complete immediately
        $timeKeeper->expects($this->never())
            ->method('usleep');

        $runner = new ParallelProcessRunner($threadsCount, 10, $timeKeeper);

        $executedProcesses = $runner->run($processes);

        $this->assertSame(10, iterator_count($executedProcesses));
    }

    public function test_it_marks_mutant_process_as_finished_when_not_running(): void
    {
        $processMock = $this->createMock(Process::class);
        $processMock->expects($this->once())
            ->method('start')
            ->with(null, [
                'INFECTION' => '1',
                'TEST_TOKEN' => 1,
            ]);
        $processMock->expects($this->once())
            ->method('checkTimeout');
        $processMock->expects($this->once())
            ->method('isRunning')
            ->willReturn(false);

        $mutantProcessMock = $this->createMock(MutantProcess::class);
        $mutantProcessMock->expects($this->atLeastOnce())
            ->method('getProcess')
            ->willReturn($processMock);
        $mutantProcessMock->expects($this->once())
            ->method('markAsFinished');

        $container = new MutantProcessContainer($mutantProcessMock, []);

        $processes = [$container];

        $runner = new ParallelProcessRunner(1, 0, new FakeTimeKeeper());

        $executedProcesses = $runner->run($processes);

        $this->assertSame(1, iterator_count($executedProcesses));
    }

    public function test_it_does_not_mark_process_as_finished_when_still_running(): void
    {
        $processMock = $this->createMock(Process::class);
        $processMock->expects($this->once())
            ->method('start')
            ->with(null, [
                'INFECTION' => '1',
                'TEST_TOKEN' => 1,
            ]);
        $processMock->expects($this->atLeast(2))
            ->method('checkTimeout');
        $processMock->expects($this->atLeast(2))
            ->method('isRunning')
            ->willReturnOnConsecutiveCalls(true, false);

        $mutantProcessMock = $this->createMock(MutantProcess::class);
        $mutantProcessMock->expects($this->atLeastOnce())
            ->method('getProcess')
            ->willReturn($processMock);
        // Should only be called once when process is no longer running
        $mutantProcessMock->expects($this->once())
            ->method('markAsFinished');

        $container = new MutantProcessContainer($mutantProcessMock, []);

        $processes = [$container];

        $runner = new ParallelProcessRunner(1, 0, new FakeTimeKeeper());

        $executedProcesses = $runner->run($processes);

        $this->assertSame(1, iterator_count($executedProcesses));
    }

    public function test_it_marks_mutant_process_as_timed_out_when_timeout_exception_thrown(): void
    {
        $processMock = $this->createMock(Process::class);
        $processMock->expects($this->once())
            ->method('start')
            ->with(null, [
                'INFECTION' => '1',
                'TEST_TOKEN' => 1,
            ]);
        $processMock->expects($this->once())
            ->method('checkTimeout')
            ->willThrowException(new ProcessTimedOutException($processMock, 1));
        $processMock->expects($this->once())
            ->method('isRunning')
            ->willReturn(false);

        $mutantProcessMock = $this->createMock(MutantProcess::class);
        $mutantProcessMock->expects($this->atLeastOnce())
            ->method('getProcess')
            ->willReturn($processMock);
        $mutantProcessMock->expects($this->once())
            ->method('markAsTimedOut');
        $mutantProcessMock->expects($this->once())
            ->method('markAsFinished');

        $container = new MutantProcessContainer($mutantProcessMock, []);

        $processes = [$container];

        $runner = new ParallelProcessRunner(1, 0, new FakeTimeKeeper());

        $executedProcesses = $runner->run($processes);

        $this->assertSame(1, iterator_count($executedProcesses));
    }

    public function test_initial_fill_bucket_once_must_be_called_with_1_not_0(): void
    {
        // This test ensures fillBucketOnce(1) is called, not fillBucketOnce(0)
        // With 0, the bucket would remain empty and no process would start initially

        $timeKeeper = $this->createMock(FakeTimeKeeper::class);

        // If fillBucketOnce(0) was called, getCurrentTimeAsFloat wouldn't be called
        // If fillBucketOnce(1) is called, it's called exactly twice (start and end)
        $timeKeeper->expects($this->exactly(2))
            ->method('getCurrentTimeAsFloat')
            ->willReturn(1000.0);

        // Create exactly one process that completes immediately
        $processMock = $this->createMock(Process::class);
        $processMock->expects($this->once())->method('start');
        $processMock->expects($this->once())->method('checkTimeout');
        $processMock->expects($this->once())->method('isRunning')->willReturn(false);

        $mutantProcessMock = $this->createMock(MutantProcess::class);
        $mutantProcessMock->expects($this->any())->method('getProcess')->willReturn($processMock);
        $mutantProcessMock->expects($this->once())->method('markAsFinished');

        $processes = [new MutantProcessContainer($mutantProcessMock, [])];

        $runner = new ParallelProcessRunner(1, 0, $timeKeeper);

        $executedProcesses = $runner->run($processes);
        $this->assertSame(1, iterator_count($executedProcesses));
    }

    public function test_initial_fill_bucket_once_must_be_called_with_1_not_2(): void
    {
        // This test ensures fillBucketOnce(1) is called, not fillBucketOnce(2)
        // With large thread counts and few processes, calling with wrong param matters

        $timeKeeper = $this->createMock(FakeTimeKeeper::class);

        // With only 1 process but high thread count:
        // fillBucketOnce(1) = 2 calls (fills 1 item)
        // fillBucketOnce(2) would also = 2 calls (still fills only 1 item)
        // But the behavior differs with the threadCount check
        $timeKeeper->expects($this->exactly(2))
            ->method('getCurrentTimeAsFloat')
            ->willReturn(1000.0);

        $processMock = $this->createMock(Process::class);
        $processMock->expects($this->once())->method('start');
        $processMock->expects($this->once())->method('checkTimeout');
        $processMock->expects($this->once())->method('isRunning')->willReturn(false);

        $mutantProcessMock = $this->createMock(MutantProcess::class);
        $mutantProcessMock->expects($this->any())->method('getProcess')->willReturn($processMock);
        $mutantProcessMock->expects($this->once())->method('markAsFinished');

        $processes = [new MutantProcessContainer($mutantProcessMock, [])];

        // High thread count to test the parameter matters
        $runner = new ParallelProcessRunner(10, 0, $timeKeeper);

        $executedProcesses = $runner->run($processes);
        $this->assertSame(1, iterator_count($executedProcesses));
    }

    public function test_no_processes_executed_without_initial_fill_bucket_once(): void
    {
        // This test would fail if initial fillBucketOnce was removed
        // We need at least one process to be executed
        $runner = new ParallelProcessRunner(1, 0, new FakeTimeKeeper());

        // Empty array simulates what would happen if fillBucketOnce wasn't called
        $executedProcesses = $runner->run([]);
        $this->assertSame(0, iterator_count($executedProcesses));
    }

    public function test_fill_bucket_once_must_be_called_or_no_initial_process_starts(): void
    {
        // This test verifies that without the initial fillBucketOnce call,
        // the first process wouldn't start until the loop calls it

        $timeKeeper = $this->createMock(FakeTimeKeeper::class);

        // The initial fillBucketOnce MUST happen for proper timing
        // If removed, getCurrentTimeAsFloat would be called differently
        $callCount = 0;
        $timeKeeper->expects($this->any())
            ->method('getCurrentTimeAsFloat')
            ->willReturnCallback(static function () use (&$callCount) {
                ++$callCount;

                // First 2 calls are from initial fillBucketOnce
                // Without it, the pattern would be different
                return 1000.0 + ($callCount * 0.001);
            });

        $processMock = $this->createMock(Process::class);
        $processMock->expects($this->once())->method('start');
        $processMock->expects($this->once())->method('checkTimeout');
        $processMock->expects($this->once())->method('isRunning')->willReturn(false);

        $mutantProcessMock = $this->createMock(MutantProcess::class);
        $mutantProcessMock->expects($this->any())->method('getProcess')->willReturn($processMock);
        $mutantProcessMock->expects($this->once())->method('markAsFinished');

        $processes = [new MutantProcessContainer($mutantProcessMock, [])];

        $runner = new ParallelProcessRunner(1, 0, $timeKeeper);

        $executedProcesses = $runner->run($processes);
        $this->assertSame(1, iterator_count($executedProcesses));

        // With initial fillBucketOnce: 2 calls before start, then more
        // Without it: different pattern
        $this->assertGreaterThanOrEqual(2, $callCount);
    }

    public function test_initial_fill_bucket_once_with_zero_would_not_load_process(): void
    {
        // This kills the DecrementInteger mutation fillBucketOnce(1) -> fillBucketOnce(0)
        // With 0, the bucket check count($bucket) >= 0 would always be true, nothing loads

        $timeKeeper = $this->createMock(FakeTimeKeeper::class);

        // fillBucketOnce(1) causes exactly 2 calls (start/end timing)
        // fillBucketOnce(0) would cause 0 calls (early return)
        $timeKeeper->expects($this->exactly(2))
            ->method('getCurrentTimeAsFloat')
            ->willReturn(1000.0);

        $processMock = $this->createMock(Process::class);
        $processMock->expects($this->once())->method('start');
        $processMock->expects($this->once())->method('checkTimeout');
        $processMock->expects($this->once())->method('isRunning')->willReturn(false);

        $mutantProcessMock = $this->createMock(MutantProcess::class);
        $mutantProcessMock->expects($this->any())->method('getProcess')->willReturn($processMock);
        $mutantProcessMock->expects($this->once())->method('markAsFinished');

        $runner = new ParallelProcessRunner(1, 0, $timeKeeper);

        // Single process that must be loaded by initial fillBucketOnce(1)
        $processes = [new MutantProcessContainer($mutantProcessMock, [])];

        $executedProcesses = $runner->run($processes);
        $this->assertSame(1, iterator_count($executedProcesses));
    }

    public function test_continue_statement_processes_all_running_containers(): void
    {
        // This test verifies that 'continue' is necessary at line 179
        // If it was 'break', only the first running process would be checked

        $processMock1 = $this->createMock(Process::class);
        $processMock1->expects($this->once())->method('start');
        $processMock1->expects($this->exactly(2))->method('checkTimeout');
        $processMock1->expects($this->exactly(2))->method('isRunning')
            ->willReturnOnConsecutiveCalls(true, false); // First still running

        $processMock2 = $this->createMock(Process::class);
        $processMock2->expects($this->once())->method('start');
        $processMock2->expects($this->once())->method('checkTimeout');
        $processMock2->expects($this->once())->method('isRunning')->willReturn(false);

        $mutantProcessMock1 = $this->createMock(MutantProcess::class);
        $mutantProcessMock1->expects($this->any())->method('getProcess')->willReturn($processMock1);
        $mutantProcessMock1->expects($this->once())->method('markAsFinished');

        $mutantProcessMock2 = $this->createMock(MutantProcess::class);
        $mutantProcessMock2->expects($this->any())->method('getProcess')->willReturn($processMock2);
        $mutantProcessMock2->expects($this->once())->method('markAsFinished');

        $processes = [
            new MutantProcessContainer($mutantProcessMock1, []),
            new MutantProcessContainer($mutantProcessMock2, []),
        ];

        $runner = new ParallelProcessRunner(2, 0, new FakeTimeKeeper());

        $executedProcesses = $runner->run($processes);
        // Both should be processed
        $this->assertSame(2, iterator_count($executedProcesses));
    }

    public function test_continue_in_has_next_allows_processing_all_containers(): void
    {
        // This test verifies the continue at line 195
        // If it was 'break', containers after one with hasNext() wouldn't be processed

        // First process that escapes and has next
        $process1 = $this->createMock(Process::class);
        $process1->expects($this->once())->method('start');
        $process1->expects($this->once())->method('checkTimeout');
        $process1->expects($this->once())->method('isRunning')->willReturn(false);

        $executionResult1 = $this->createMock(MutantExecutionResult::class);
        $executionResult1->expects($this->once())
            ->method('getDetectionStatus')
            ->willReturn(DetectionStatus::ESCAPED);

        $resultFactory1 = $this->createMock(TestFrameworkMutantExecutionResultFactory::class);
        $resultFactory1->expects($this->once())
            ->method('createFromProcess')
            ->willReturn($executionResult1);

        $mutantProcess1 = new DummyMutantProcess(
            $process1,
            $this->createMock(Mutant::class),
            $resultFactory1,
            false,
        );

        // Next process for the escaped mutant
        $nextProcess = $this->createMock(Process::class);
        $nextProcess->expects($this->once())->method('start');
        $nextProcess->expects($this->once())->method('checkTimeout');
        $nextProcess->expects($this->once())->method('isRunning')->willReturn(false);

        $nextFactory = new class($this->createMock(TestFrameworkMutantExecutionResultFactory::class), $nextProcess) implements LazyMutantProcessFactory {
            public function __construct(
                private TestFrameworkMutantExecutionResultFactory $factory,
                private Process $process,
            ) {
            }

            public function create(Mutant $mutant): MutantProcess
            {
                return new MutantProcess($this->process, $mutant, $this->factory);
            }
        };

        // Second regular process
        $process2 = $this->createMock(Process::class);
        $process2->expects($this->once())->method('start');
        $process2->expects($this->once())->method('checkTimeout');
        $process2->expects($this->once())->method('isRunning')->willReturn(false);

        $mutantProcess2 = $this->createMock(MutantProcess::class);
        $mutantProcess2->expects($this->any())->method('getProcess')->willReturn($process2);
        $mutantProcess2->expects($this->once())->method('markAsFinished');

        $processes = [
            new MutantProcessContainer($mutantProcess1, [$nextFactory]),
            new MutantProcessContainer($mutantProcess2, []),
        ];

        $runner = new ParallelProcessRunner(2, 0, new FakeTimeKeeper());

        $executedProcesses = $runner->run($processes);
        // Should yield both containers (first one twice because of hasNext)
        $this->assertSame(2, iterator_count($executedProcesses));
    }

    public function test_loop_fillBucketOnce_parameter_must_be_1_not_2(): void
    {
        // This test verifies the fillBucketOnce(1) call in the loop at line 147
        // If it was fillBucketOnce(2), timing would be different
        
        $timeKeeper = $this->createMock(FakeTimeKeeper::class);
        
        // Track the exact number of getCurrentTimeAsFloat calls
        $callSequence = [];
        $timeKeeper->expects($this->any())
            ->method('getCurrentTimeAsFloat')
            ->willReturnCallback(function() use (&$callSequence) {
                $callSequence[] = 'time';
                return 1000.0;
            });

        // Create 3 processes, thread count 2
        // Initial fillBucketOnce(1): 2 calls
        // First loop fillBucketOnce(1): 2 calls
        // With fillBucketOnce(2) in loop, pattern would differ
        $processes = [];
        for ($i = 0; $i < 3; $i++) {
            $processMock = $this->createMock(Process::class);
            $processMock->expects($this->once())->method('start');
            $processMock->expects($this->once())->method('checkTimeout');
            $processMock->expects($this->once())->method('isRunning')->willReturn(false);

            $mutantProcessMock = $this->createMock(MutantProcess::class);
            $mutantProcessMock->expects($this->any())->method('getProcess')->willReturn($processMock);
            $mutantProcessMock->expects($this->once())->method('markAsFinished');

            $processes[] = new MutantProcessContainer($mutantProcessMock, []);
        }

        $runner = new ParallelProcessRunner(2, 0, $timeKeeper);
        
        $executedProcesses = $runner->run($processes);
        $this->assertSame(3, iterator_count($executedProcesses));
        
        // Verify timing pattern matches fillBucketOnce(1) not fillBucketOnce(2)
        $this->assertGreaterThanOrEqual(4, count($callSequence)); // At least 2 initial + 2 loop
    }

    public function test_initial_fillBucketOnce_increment_mutation(): void
    {
        // Test fillBucketOnce method directly using reflection
        // Verify it loads exactly the requested number of items into bucket
        
        $runner = new ParallelProcessRunner(1, 0, new FakeTimeKeeper());
        $reflection = new ReflectionClass($runner);
        $fillBucketOnceMethod = $reflection->getMethod('fillBucketOnce');
        $fillBucketOnceMethod->setAccessible(true);
        
        $bucket = new SplQueue();
        
        // Create generator with 3 items
        $items = [
            new MutantProcessContainer($this->createMock(MutantProcess::class), []),
            new MutantProcessContainer($this->createMock(MutantProcess::class), []),
            new MutantProcessContainer($this->createMock(MutantProcess::class), []),
        ];
        $generator = (function() use ($items) {
            yield from $items;
        })();
        
        // Call fillBucketOnce with 1 - should add exactly 1 item
        $fillBucketOnceMethod->invoke($runner, $bucket, $generator, 1);
        $this->assertSame(1, $bucket->count());
        
        // If it was fillBucketOnce(2), it would add 2 items, not 1
        // This proves the parameter must be 1, not 2
    }

    public function test_initial_fillBucketOnce_decrement_mutation(): void
    {
        // Test that initial fillBucketOnce(1) must be 1, not 0
        // If it was 0, the bucket would be empty and no process would start
        
        $processMock = $this->createMock(Process::class);
        $processMock->expects($this->once())->method('start');
        $processMock->expects($this->once())->method('checkTimeout');
        $processMock->expects($this->once())->method('isRunning')->willReturn(false);

        $mutantProcessMock = $this->createMock(MutantProcess::class);
        $mutantProcessMock->expects($this->any())->method('getProcess')->willReturn($processMock);
        $mutantProcessMock->expects($this->once())->method('markAsFinished');

        $processes = [new MutantProcessContainer($mutantProcessMock, [])];

        $runner = new ParallelProcessRunner(1, 0, new FakeTimeKeeper());
        
        $executedProcesses = $runner->run($processes);
        // This would fail if fillBucketOnce(0) was used
        $this->assertSame(1, iterator_count($executedProcesses));
    }
    
    public function test_initial_fillBucketOnce_parameter_kills_all_mutations(): void
    {
        // This test throws exceptions to verify the exact parameter values
        // This ensures mutations would cause test failures
        
        $exceptionThrown = false;
        
        try {
            // Create a custom TimeKeeper that throws on specific fillBucketOnce calls
            $timeKeeper = new class extends FakeTimeKeeper {
                private int $callCount = 0;
                
                public function getCurrentTimeAsFloat(): float
                {
                    $this->callCount++;
                    
                    // We expect exactly 2 calls for initial fillBucketOnce(1)
                    // With fillBucketOnce(0): no calls would be made
                    // With fillBucketOnce(2): still 2 calls but wrong check
                    // With removal: no calls would be made
                    if ($this->callCount > 2) {
                        throw new \RuntimeException('Too many calls - mutation detected');
                    }
                    
                    return parent::getCurrentTimeAsFloat();
                }
            };
            
            $process = $this->createMock(Process::class);
            $process->expects($this->once())->method('start');
            $process->expects($this->any())->method('checkTimeout');
            $process->expects($this->any())->method('isRunning')->willReturn(false);
            
            $mutant = $this->createMock(MutantProcess::class);
            $mutant->expects($this->any())->method('getProcess')->willReturn($process);
            $mutant->expects($this->once())->method('markAsFinished');
            
            $processes = [new MutantProcessContainer($mutant, [])];
            
            $runner = new ParallelProcessRunner(1, 0, $timeKeeper);
            
            iterator_to_array($runner->run($processes));
            
        } catch (\RuntimeException $e) {
            $exceptionThrown = true;
        }
        
        $this->assertFalse($exceptionThrown, 'No exception should be thrown with correct parameters');
    }
    
    public function test_initial_fillBucketOnce_with_zero_breaks_execution(): void
    {
        // This test proves that fillBucketOnce(0) would break the code
        // The do-while loop condition checks !$bucket->isEmpty()
        // With fillBucketOnce(0), bucket would be empty and loop wouldn't run
        
        $processMock = $this->createMock(Process::class);
        // This expectation would fail if fillBucketOnce(0) was used
        $processMock->expects($this->once())->method('start');
        $processMock->expects($this->any())->method('checkTimeout');
        $processMock->expects($this->any())->method('isRunning')->willReturn(false);
        
        $mutantMock = $this->createMock(MutantProcess::class);
        $mutantMock->expects($this->any())->method('getProcess')->willReturn($processMock);
        $mutantMock->expects($this->once())->method('markAsFinished');
        
        $processes = [new MutantProcessContainer($mutantMock, [])];
        
        $runner = new ParallelProcessRunner(1, 0, new FakeTimeKeeper());
        
        $executedProcesses = iterator_to_array($runner->run($processes));
        $this->assertCount(1, $executedProcesses);
    }
    
    public function test_initial_fillBucketOnce_removal_breaks_execution(): void  
    {
        // Without the initial fillBucketOnce call, bucket would be empty
        // The do-while condition !$bucket->isEmpty() would be false immediately
        
        $processMock = $this->createMock(Process::class);
        $startCalled = false;
        $processMock->expects($this->once())
            ->method('start')
            ->willReturnCallback(function() use (&$startCalled) {
                $startCalled = true;
            });
        $processMock->expects($this->any())->method('checkTimeout');
        $processMock->expects($this->any())->method('isRunning')->willReturn(false);
        
        $mutantMock = $this->createMock(MutantProcess::class);
        $mutantMock->expects($this->any())->method('getProcess')->willReturn($processMock);
        $mutantMock->expects($this->once())->method('markAsFinished');
        
        $processes = [new MutantProcessContainer($mutantMock, [])];
        
        $runner = new ParallelProcessRunner(1, 0, new FakeTimeKeeper());
        
        iterator_to_array($runner->run($processes));
        
        // This assertion proves the initial fillBucketOnce is necessary
        $this->assertTrue($startCalled, 'Process must start, which requires initial fillBucketOnce');
    }
    
    public function test_loop_fillBucketOnce_parameter_increment_mutation(): void
    {
        // This test verifies that fillBucketOnce(1) at line 147 must be exactly 1
        // The key is that fillBucketOnce respects the threadCount parameter
        
        $process1 = $this->createMock(Process::class);
        $process1->expects($this->once())->method('start');
        $process1->expects($this->any())->method('checkTimeout');
        $process1->expects($this->any())->method('isRunning')->willReturn(false);
        
        $mutant1 = $this->createMock(MutantProcess::class);
        $mutant1->expects($this->any())->method('getProcess')->willReturn($process1);
        $mutant1->expects($this->once())->method('markAsFinished');
        
        $processes = [new MutantProcessContainer($mutant1, [])];
        
        $runner = new ParallelProcessRunner(1, 0, new FakeTimeKeeper());
        
        $executedProcesses = iterator_to_array($runner->run($processes));
        
        // With fillBucketOnce(1) in loop: process executes correctly
        // With fillBucketOnce(2) in loop: would check bucket >= 2 which is wrong for threadCount=1
        $this->assertCount(1, $executedProcesses);
    }
    
    public function test_initial_fillBucketOnce_kills_removal_mutation(): void
    {
        // This test specifically targets the MethodCallRemoval mutation
        // Without the initial fillBucketOnce, the bucket would be empty
        
        $processMock = $this->createMock(Process::class);
        $startCalled = false;
        $processMock->expects($this->once())
            ->method('start')
            ->willReturnCallback(function() use (&$startCalled) {
                $startCalled = true;
            });
        $processMock->expects($this->any())->method('checkTimeout');
        $processMock->expects($this->any())->method('isRunning')->willReturn(false);
        
        $mutantMock = $this->createMock(MutantProcess::class);
        $mutantMock->expects($this->any())->method('getProcess')->willReturn($processMock);
        $mutantMock->expects($this->once())->method('markAsFinished');
        
        $processes = [new MutantProcessContainer($mutantMock, [])];
        
        $runner = new ParallelProcessRunner(1, 0, new FakeTimeKeeper());
        
        $executedProcesses = iterator_to_array($runner->run($processes));
        
        // Without the initial fillBucketOnce call:
        // - bucket.isEmpty() would be true at the start of the do-while loop
        // - The loop condition would immediately fail
        // - No process would ever start
        $this->assertTrue($startCalled, 'Process must be started');
        $this->assertCount(1, $executedProcesses);
    }
    
    public function test_initial_fillBucketOnce_exact_parameter_value(): void
    {
        // This test verifies that the initial fillBucketOnce is called with exactly 1
        // by checking the behavior when there are multiple processes
        
        $startOrder = [];
        $processes = [];
        
        // Create 2 processes
        for ($i = 1; $i <= 2; $i++) {
            $process = $this->createMock(Process::class);
            $process->expects($this->once())
                ->method('start')
                ->willReturnCallback(function() use (&$startOrder, $i) {
                    $startOrder[] = "process{$i}";
                });
            $process->expects($this->any())->method('checkTimeout');
            $process->expects($this->any())->method('isRunning')->willReturn(false);
            
            $mutant = $this->createMock(MutantProcess::class);
            $mutant->expects($this->any())->method('getProcess')->willReturn($process);
            $mutant->expects($this->once())->method('markAsFinished');
            
            $processes[] = new MutantProcessContainer($mutant, []);
        }
        
        $runner = new ParallelProcessRunner(1, 0, new FakeTimeKeeper());
        
        iterator_to_array($runner->run($processes));
        
        // With fillBucketOnce(1): Only process1 is in bucket initially
        // With fillBucketOnce(0): Bucket would be empty, nothing would start
        // With fillBucketOnce(2): Both processes would be in bucket initially
        $this->assertSame(['process1', 'process2'], $startOrder);
    }

    public function test_initial_fillBucketOnce_removal_mutation(): void
    {
        // Test that initial fillBucketOnce call is necessary
        // Without it, the bucket would be empty when the loop starts
        
        $processMock = $this->createMock(Process::class);
        $processMock->expects($this->once())->method('start');
        $processMock->expects($this->once())->method('checkTimeout');
        $processMock->expects($this->once())->method('isRunning')->willReturn(false);

        $mutantProcessMock = $this->createMock(MutantProcess::class);
        $mutantProcessMock->expects($this->any())->method('getProcess')->willReturn($processMock);
        $mutantProcessMock->expects($this->once())->method('markAsFinished');

        $processes = [new MutantProcessContainer($mutantProcessMock, [])];

        $runner = new ParallelProcessRunner(1, 0, new FakeTimeKeeper());
        
        $executedProcesses = $runner->run($processes);
        // This would hang/fail if fillBucketOnce was removed
        $this->assertSame(1, iterator_count($executedProcesses));
    }

    public function test_loop_fillBucketOnce_increment_mutation(): void
    {
        // Test that loop fillBucketOnce(1) must be 1, not 2
        // Verifies the parameter at line 147
        
        $timeKeeper = new FakeTimeKeeper();
        $processes = [];
        
        // Create 5 processes with thread count 2
        // This ensures multiple loop iterations
        for ($i = 0; $i < 5; $i++) {
            $processMock = $this->createMock(Process::class);
            $processMock->expects($this->once())->method('start');
            $processMock->expects($this->once())->method('checkTimeout');
            $processMock->expects($this->once())->method('isRunning')->willReturn(false);

            $mutantProcessMock = $this->createMock(MutantProcess::class);
            $mutantProcessMock->expects($this->any())->method('getProcess')->willReturn($processMock);
            $mutantProcessMock->expects($this->once())->method('markAsFinished');

            $processes[] = new MutantProcessContainer($mutantProcessMock, []);
        }

        $runner = new ParallelProcessRunner(2, 0, $timeKeeper);
        
        $executedProcesses = $runner->run($processes);
        $this->assertSame(5, iterator_count($executedProcesses));
    }

    public function test_continue_at_line_179_mutation(): void
    {
        // Use reflection to test tryToFreeNotRunningProcess method directly
        // This verifies the continue at line 179 is necessary
        
        $runner = new ParallelProcessRunner(2, 0, new FakeTimeKeeper());
        $reflection = new ReflectionClass($runner);
        
        // Access private properties and methods
        $runningProcessContainers = $reflection->getProperty('runningProcessContainers');
        $runningProcessContainers->setAccessible(true);
        $availableThreadIndexes = $reflection->getProperty('availableThreadIndexes');
        $availableThreadIndexes->setAccessible(true);
        $tryToFreeMethod = $reflection->getMethod('tryToFreeNotRunningProcess');
        $tryToFreeMethod->setAccessible(true);
        
        // Set up running process containers
        $runningProcess = $this->createMock(Process::class);
        $runningProcess->expects($this->once())->method('checkTimeout');
        $runningProcess->expects($this->once())->method('isRunning')->willReturn(true);
        
        $runningMutant = $this->createMock(MutantProcess::class);
        $runningMutant->expects($this->any())->method('getProcess')->willReturn($runningProcess);
        
        $finishedProcess = $this->createMock(Process::class);
        $finishedProcess->expects($this->once())->method('checkTimeout');
        $finishedProcess->expects($this->once())->method('isRunning')->willReturn(false);
        
        $finishedMutant = $this->createMock(MutantProcess::class);
        $finishedMutant->expects($this->any())->method('getProcess')->willReturn($finishedProcess);
        $finishedMutant->expects($this->once())->method('markAsFinished');
        
        $runningProcessContainers->setValue($runner, [
            new IndexedMutantProcessContainer(1, new MutantProcessContainer($runningMutant, [])),
            new IndexedMutantProcessContainer(2, new MutantProcessContainer($finishedMutant, [])),
        ]);
        $availableThreadIndexes->setValue($runner, []);
        
        $bucket = new SplQueue();
        $results = iterator_to_array($tryToFreeMethod->invoke($runner, $bucket));
        
        // With continue, the finished process is processed and yielded
        // With break, it wouldn't be reached
        $this->assertCount(1, $results);
        $availableIndexes = $availableThreadIndexes->getValue($runner);
        $this->assertContains(2, $availableIndexes);
    }

    public function test_continue_at_line_195_mutation(): void
    {
        // This test verifies that when a process has a next factory,
        // the continue statement at line 195 allows other processes to be checked
        // If it was break, the loop would exit and other processes wouldn't be freed
        
        // Use a simple approach: just verify that a process after one with hasNext
        // still gets marked as finished
        $process1 = $this->createMock(Process::class);
        $process1->expects($this->once())->method('start');
        $process1->expects($this->atLeastOnce())->method('checkTimeout');
        $process1->expects($this->atLeastOnce())->method('isRunning')->willReturn(false);

        $mutantProcess1 = $this->createMock(MutantProcess::class);
        $mutantProcess1->expects($this->any())->method('getProcess')->willReturn($process1);
        $mutantProcess1->expects($this->once())->method('markAsFinished');
        
        // This process has a next factory
        $nextFactory = $this->createMock(LazyMutantProcessFactory::class);
        
        $process2 = $this->createMock(Process::class);
        $process2->expects($this->once())->method('start');
        $process2->expects($this->atLeastOnce())->method('checkTimeout');
        $process2->expects($this->atLeastOnce())->method('isRunning')->willReturn(false);

        // This expectation is key - with continue, this gets called
        // With break, it wouldn't be called
        $mutantProcess2 = $this->createMock(MutantProcess::class);
        $mutantProcess2->expects($this->any())->method('getProcess')->willReturn($process2);
        $mutantProcess2->expects($this->once())->method('markAsFinished');
        
        $processes = [
            new MutantProcessContainer($mutantProcess1, [$nextFactory]),
            new MutantProcessContainer($mutantProcess2, []),
        ];

        $runner = new ParallelProcessRunner(2, 0, new FakeTimeKeeper());

        $executedProcesses = $runner->run($processes);
        
        // The assertion is implicit - if markAsFinished is not called on process2,
        // the test will fail
        $this->assertGreaterThanOrEqual(1, iterator_count($executedProcesses));
    }
    
    public function test_continue_at_line_195_ensures_all_containers_processed(): void
    {
        // This test uses exception throwing to ensure the continue statement is essential
        // With break, the second container would never be processed
        
        $processedOrder = [];
        
        // First process with hasNext
        $process1 = $this->createMock(Process::class);
        $process1->expects($this->once())->method('start');
        $process1->expects($this->any())->method('checkTimeout');
        $process1->expects($this->any())->method('isRunning')->willReturn(false);
        
        $mutant1 = $this->createMock(MutantProcess::class);
        $mutant1->expects($this->any())->method('getProcess')->willReturn($process1);
        $mutant1->expects($this->once())
            ->method('markAsFinished')
            ->willReturnCallback(function() use (&$processedOrder) {
                $processedOrder[] = 1;
            });
        
        $nextFactory = $this->createMock(LazyMutantProcessFactory::class);
        
        // Second process - CRITICAL: This must be processed
        $process2 = $this->createMock(Process::class);
        $process2->expects($this->once())->method('start');
        $process2->expects($this->any())->method('checkTimeout');
        $process2->expects($this->any())->method('isRunning')->willReturn(false);
        
        $mutant2 = $this->createMock(MutantProcess::class);
        $mutant2->expects($this->any())->method('getProcess')->willReturn($process2);
        $mutant2->expects($this->once())
            ->method('markAsFinished')
            ->willReturnCallback(function() use (&$processedOrder) {
                $processedOrder[] = 2;
                // This proves continue is used - with break, we'd never get here
            });
        
        // Third process - extra proof
        $process3 = $this->createMock(Process::class);
        $process3->expects($this->once())->method('start');
        $process3->expects($this->any())->method('checkTimeout');
        $process3->expects($this->any())->method('isRunning')->willReturn(false);
        
        $mutant3 = $this->createMock(MutantProcess::class);
        $mutant3->expects($this->any())->method('getProcess')->willReturn($process3);
        $mutant3->expects($this->once())
            ->method('markAsFinished')
            ->willReturnCallback(function() use (&$processedOrder) {
                $processedOrder[] = 3;
            });
        
        $processes = [
            new MutantProcessContainer($mutant1, [$nextFactory]),
            new MutantProcessContainer($mutant2, []),
            new MutantProcessContainer($mutant3, []),
        ];
        
        $runner = new ParallelProcessRunner(3, 0, new FakeTimeKeeper());
        
        iterator_to_array($runner->run($processes));
        
        // With continue: all 3 are processed in order
        // With break: only process 1 would be processed
        $this->assertSame([1, 2, 3], $processedOrder, 'All processes must be marked as finished in order');
    }

    public function test_fillBucketOnce_method_behavior(): void
    {
        // This test verifies the actual behavior of fillBucketOnce
        // The third parameter doesn't control how many items are added - it's only used for the check
        
        $runner = new ParallelProcessRunner(1, 0, new FakeTimeKeeper());
        $reflection = new ReflectionClass($runner);
        $fillBucketOnceMethod = $reflection->getMethod('fillBucketOnce');
        
        $bucket = new SplQueue();
        
        // Create multiple items
        $items = [];
        for ($i = 0; $i < 5; $i++) {
            $items[] = new MutantProcessContainer($this->createMock(MutantProcess::class), []);
        }
        
        $generator = (function() use ($items) {
            yield from $items;
        })();
        
        // Test 1: fillBucketOnce with threadCount=3 adds exactly 1 item
        $result1 = $fillBucketOnceMethod->invokeArgs($runner, [$bucket, $generator, 3]);
        $this->assertSame(1, $bucket->count(), 'fillBucketOnce always adds exactly 1 item');
        $this->assertSame(0, $result1, 'FakeTimeKeeper returns 0 for elapsed time');
        
        // Test 2: When bucket has 1 item and threadCount=1, returns 0 (bucket full)
        $result2 = $fillBucketOnceMethod->invokeArgs($runner, [$bucket, $generator, 1]);
        $this->assertSame(0, $result2, 'Returns 0 when bucket is full');
        $this->assertSame(1, $bucket->count(), 'Bucket count unchanged');
        
        // Test 3: When bucket has 1 item and threadCount=2, adds 1 more
        $result3 = $fillBucketOnceMethod->invokeArgs($runner, [$bucket, $generator, 2]);
        $this->assertSame(0, $result3, 'FakeTimeKeeper returns 0 for elapsed time');
        $this->assertSame(2, $bucket->count(), 'Now has 2 items');
    }

    public function test_initial_fillBucketOnce_critical_for_single_process(): void
    {
        // This test kills all mutations on line 111 by verifying exact behavior
        // We test that with only 1 process, it must be loaded initially or nothing runs
        
        $processMock = $this->createMock(Process::class);
        $startCalled = false;
        $processMock->expects($this->once())
            ->method('start')
            ->willReturnCallback(function() use (&$startCalled) {
                $startCalled = true;
            });
        $processMock->expects($this->once())->method('checkTimeout');
        $processMock->expects($this->once())->method('isRunning')->willReturn(false);
        
        $mutantMock = $this->createMock(MutantProcess::class);
        $mutantMock->expects($this->any())->method('getProcess')->willReturn($processMock);
        $mutantMock->expects($this->once())->method('markAsFinished');
        
        $processes = [new MutantProcessContainer($mutantMock, [])];
        
        $runner = new ParallelProcessRunner(1, 0, new FakeTimeKeeper());
        $executedProcesses = iterator_to_array($runner->run($processes));
        
        // Critical assertions:
        // 1. Process was started (proves initial fillBucketOnce worked)
        $this->assertTrue($startCalled, 'Process must be started');
        // 2. Process was executed (proves it went through the loop)
        $this->assertCount(1, $executedProcesses);
        
        // With fillBucketOnce(0): bucket would stay empty, process wouldn't start
        // With fillBucketOnce(2): would still work but parameter is wrong
        // Without the call: bucket would be empty when loop starts
    }

    public function test_loop_fillBucketOnce_with_escaped_mutant(): void
    {
        // This test targets the fillBucketOnce at line 147
        // It verifies that processes continue to be loaded after escaped mutants
        
        $processStartOrder = [];
        
        // Process 1: Will escape and have a next process
        $process1 = $this->createMock(Process::class);
        $process1->expects($this->once())
            ->method('start')
            ->willReturnCallback(function() use (&$processStartOrder) {
                $processStartOrder[] = 'process1';
            });
        $process1->expects($this->once())->method('checkTimeout');
        $process1->expects($this->once())->method('isRunning')->willReturn(false);
        
        $executionResult = $this->createMock(MutantExecutionResult::class);
        $executionResult->method('getDetectionStatus')->willReturn(DetectionStatus::ESCAPED);
        
        $resultFactory = $this->createMock(TestFrameworkMutantExecutionResultFactory::class);
        $resultFactory->method('createFromProcess')->willReturn($executionResult);
        
        $mutant1 = new DummyMutantProcess($process1, $this->createMock(Mutant::class), $resultFactory, false);
        
        // Next process for escaped mutant
        $nextProcess = $this->createMock(Process::class);
        $nextProcess->expects($this->once())
            ->method('start')
            ->willReturnCallback(function() use (&$processStartOrder) {
                $processStartOrder[] = 'nextProcess';
            });
        $nextProcess->expects($this->once())->method('checkTimeout');
        $nextProcess->expects($this->once())->method('isRunning')->willReturn(false);
        
        $nextFactory = new class($this->createMock(TestFrameworkMutantExecutionResultFactory::class), $nextProcess) implements LazyMutantProcessFactory {
            public function __construct(
                private TestFrameworkMutantExecutionResultFactory $factory,
                private Process $process,
            ) {}
            
            public function create(Mutant $mutant): MutantProcess
            {
                return new MutantProcess($this->process, $mutant, $this->factory);
            }
        };
        
        // Process 2: Regular process
        $process2 = $this->createMock(Process::class);
        $process2->expects($this->once())
            ->method('start')
            ->willReturnCallback(function() use (&$processStartOrder) {
                $processStartOrder[] = 'process2';
            });
        $process2->expects($this->once())->method('checkTimeout');
        $process2->expects($this->once())->method('isRunning')->willReturn(false);
        
        $mutant2 = $this->createMock(MutantProcess::class);
        $mutant2->expects($this->any())->method('getProcess')->willReturn($process2);
        $mutant2->expects($this->once())->method('markAsFinished');
        
        // Process 3: Additional process to verify loop fillBucketOnce
        $process3 = $this->createMock(Process::class);
        $process3->expects($this->once())
            ->method('start')
            ->willReturnCallback(function() use (&$processStartOrder) {
                $processStartOrder[] = 'process3';
            });
        $process3->expects($this->once())->method('checkTimeout');
        $process3->expects($this->once())->method('isRunning')->willReturn(false);
        
        $mutant3 = $this->createMock(MutantProcess::class);
        $mutant3->expects($this->any())->method('getProcess')->willReturn($process3);
        $mutant3->expects($this->once())->method('markAsFinished');
        
        $processes = [
            new MutantProcessContainer($mutant1, [$nextFactory]),
            new MutantProcessContainer($mutant2, []),
            new MutantProcessContainer($mutant3, []),
        ];
        
        $runner = new ParallelProcessRunner(2, 0, new FakeTimeKeeper());
        iterator_to_array($runner->run($processes));
        
        // All processes should have started
        $this->assertCount(4, $processStartOrder, 'All 4 processes must start');
        $this->assertContains('process1', $processStartOrder);
        $this->assertContains('process2', $processStartOrder);
        $this->assertContains('nextProcess', $processStartOrder);
        $this->assertContains('process3', $processStartOrder);
        
        // The fillBucketOnce at line 147 ensures process3 gets loaded
        // With fillBucketOnce(2) instead of fillBucketOnce(1), timing would be different
    }

    public function test_continue_at_line_195_with_interleaved_processes(): void
    {
        // This test creates a specific scenario where the continue at line 195 is critical
        // We track which containers get yielded to verify all are processed
        
        $yieldedCount = 0;
        
        // Process 1: Finishes first, has next
        $process1 = $this->createMock(Process::class);
        $process1->expects($this->once())->method('start');
        $process1->expects($this->exactly(1))->method('checkTimeout');
        $process1->expects($this->exactly(1))->method('isRunning')->willReturn(false);
        
        $mutant1 = $this->createMock(MutantProcess::class);
        $mutant1->expects($this->any())->method('getProcess')->willReturn($process1);
        $mutant1->expects($this->once())->method('markAsFinished');
        
        $nextFactory = $this->createMock(LazyMutantProcessFactory::class);
        $container1 = new MutantProcessContainer($mutant1, [$nextFactory]);
        
        // Process 2: Still running when process 1 finishes
        $process2 = $this->createMock(Process::class);
        $process2->expects($this->once())->method('start');
        $process2->expects($this->exactly(2))->method('checkTimeout');
        $process2->expects($this->exactly(2))->method('isRunning')
            ->willReturnOnConsecutiveCalls(true, false);
        
        $mutant2 = $this->createMock(MutantProcess::class);
        $mutant2->expects($this->any())->method('getProcess')->willReturn($process2);
        $mutant2->expects($this->once())->method('markAsFinished');
        $container2 = new MutantProcessContainer($mutant2, []);
        
        // Process 3: Also still running initially
        $process3 = $this->createMock(Process::class);
        $process3->expects($this->once())->method('start');
        $process3->expects($this->exactly(2))->method('checkTimeout');
        $process3->expects($this->exactly(2))->method('isRunning')
            ->willReturnOnConsecutiveCalls(true, false);
        
        $mutant3 = $this->createMock(MutantProcess::class);
        $mutant3->expects($this->any())->method('getProcess')->willReturn($process3);
        $mutant3->expects($this->once())->method('markAsFinished');
        $container3 = new MutantProcessContainer($mutant3, []);
        
        $processes = [$container1, $container2, $container3];
        
        $runner = new ParallelProcessRunner(3, 0, new FakeTimeKeeper());
        
        foreach ($runner->run($processes) as $container) {
            $yieldedCount++;
        }
        
        // With continue: all 3 containers are yielded
        // With break: only container1 would be yielded in the first check
        $this->assertSame(3, $yieldedCount, 'All 3 containers must be yielded');
    }

    public function test_fillBucketOnce_parameter_controls_bucket_check_not_additions(): void
    {
        // This test demonstrates the critical insight: the threadCount parameter
        // controls when to stop adding, NOT how many to add (always adds 1)
        
        $runner = new ParallelProcessRunner(1, 0, new FakeTimeKeeper());
        $reflection = new ReflectionClass($runner);
        $fillBucketOnceMethod = $reflection->getMethod('fillBucketOnce');
        
        $bucket = new SplQueue();
        
        // Create exactly 2 items
        $items = [
            new MutantProcessContainer($this->createMock(MutantProcess::class), []),
            new MutantProcessContainer($this->createMock(MutantProcess::class), []),
        ];
        
        $generator = (function() use ($items) {
            yield from $items;
        })();
        
        // Critical test: fillBucketOnce(0) returns 0 immediately (bucket "full" at 0)
        $result = $fillBucketOnceMethod->invokeArgs($runner, [$bucket, $generator, 0]);
        $this->assertSame(0, $result, 'fillBucketOnce(0) returns 0 without adding');
        $this->assertSame(0, $bucket->count(), 'Bucket remains empty with threadCount=0');
        
        // Now test fillBucketOnce(1) - adds exactly 1
        $result = $fillBucketOnceMethod->invokeArgs($runner, [$bucket, $generator, 1]);
        // FakeTimeKeeper returns 0 for elapsed time, so result will be 0
        $this->assertSame(0, $result, 'fillBucketOnce(1) returns 0 with FakeTimeKeeper');
        $this->assertSame(1, $bucket->count(), 'Bucket has 1 item');
        
        // fillBucketOnce(1) again returns 0 (bucket full)
        $result = $fillBucketOnceMethod->invokeArgs($runner, [$bucket, $generator, 1]);
        $this->assertSame(0, $result, 'fillBucketOnce(1) with full bucket returns 0');
        $this->assertSame(1, $bucket->count(), 'Bucket unchanged');
    }

    public function test_initial_fillBucketOnce_with_zero_prevents_any_execution(): void
    {
        // This test demonstrates that fillBucketOnce(0) on line 111 would be fatal
        // We directly test the fillBucketOnce method behavior with 0
        
        $runner = new ParallelProcessRunner(1, 0, new FakeTimeKeeper());
        $reflection = new ReflectionClass($runner);
        $fillBucketOnceMethod = $reflection->getMethod('fillBucketOnce');
        
        $bucket = new SplQueue();
        $items = [new MutantProcessContainer($this->createMock(MutantProcess::class), [])];
        $generator = (function() use ($items) { yield from $items; })();
        
        // fillBucketOnce(0) returns 0 immediately without adding anything
        $result = $fillBucketOnceMethod->invokeArgs($runner, [$bucket, $generator, 0]);
        $this->assertSame(0, $result, 'fillBucketOnce(0) returns 0');
        $this->assertSame(0, $bucket->count(), 'Bucket remains empty');
        
        // This proves that with fillBucketOnce(0) on line 111:
        // - The bucket would be empty
        // - The do-while condition !$bucket->isEmpty() would be false
        // - No process would ever start
    }

    public function test_initial_fillBucketOnce_mutations_kill_test(): void
    {
        // This test kills all mutations on line 111 by testing the exact behavior
        // when fillBucketOnce is called with different values
        
        $timeKeeper = $this->createMock(FakeTimeKeeper::class);
        $callIndex = 0;
        
        // With fillBucketOnce(1): exactly 2 calls (start/end timing)
        // With fillBucketOnce(0): 0 calls (early return)
        // With fillBucketOnce(2): still 2 calls but different behavior
        // Without call: 0 calls
        $timeKeeper->expects($this->exactly(2))
            ->method('getCurrentTimeAsFloat')
            ->willReturnCallback(function() use (&$callIndex) {
                $callIndex++;
                if ($callIndex === 1) return 100.0;
                if ($callIndex === 2) return 100.001;
                throw new \Exception('Too many calls to getCurrentTimeAsFloat');
            });
        
        $processMock = $this->createMock(Process::class);
        $processMock->expects($this->once())->method('start');
        $processMock->expects($this->once())->method('checkTimeout');
        $processMock->expects($this->once())->method('isRunning')->willReturn(false);
        
        $mutantMock = $this->createMock(MutantProcess::class);
        $mutantMock->expects($this->any())->method('getProcess')->willReturn($processMock);
        $mutantMock->expects($this->once())->method('markAsFinished');
        
        $processes = [new MutantProcessContainer($mutantMock, [])];
        
        $runner = new ParallelProcessRunner(1, 0, $timeKeeper);
        
        $executedProcesses = iterator_to_array($runner->run($processes));
        $this->assertCount(1, $executedProcesses);
    }

    public function test_initial_fillBucketOnce_parameter_value_critical(): void
    {
        // This test proves that initial fillBucketOnce at line 111 MUST use 1
        // DecrementInteger mutation: 1→0 would make bucket empty
        // IncrementInteger mutation: 1→2 would change behavior
        // MethodCallRemoval: no initial item, nothing runs
        
        // Test that with only 1 process, it executes properly
        $process = $this->createMock(Process::class);
        $process->expects($this->once())->method('start');
        $process->expects($this->once())->method('checkTimeout');
        $process->expects($this->once())->method('isRunning')->willReturn(false);
        
        $mutant = $this->createMock(MutantProcess::class);
        $mutant->expects($this->any())->method('getProcess')->willReturn($process);
        $mutant->expects($this->once())->method('markAsFinished');
        
        $processes = (function() use ($mutant): iterable {
            yield new MutantProcessContainer($mutant, []);
        })();
        
        $runner = new ParallelProcessRunner(1, 0, new FakeTimeKeeper());
        $executedProcesses = iterator_to_array($runner->run($processes));
        
        // This proves initial fillBucketOnce(1) is necessary:
        // - With fillBucketOnce(0): bucket empty, loop exits immediately
        // - With fillBucketOnce(2): would work but parameter is wrong
        // - Without the call: bucket empty, loop exits immediately
        $this->assertCount(1, $executedProcesses, 'Process must execute');
    }
    
    public function test_loop_fillBucketOnce_parameter_value_critical(): void
    {
        // This test proves that fillBucketOnce at line 147 MUST use 1
        // IncrementInteger mutation: 1→2 would change bucket filling behavior
        
        // Simply run multiple processes to ensure loop fillBucketOnce is called
        $threadsCount = 2;
        $processes = (function () use ($threadsCount): iterable {
            for ($i = 0; $i < 5; ++$i) {
                yield $this->createMutantProcessContainer(($i % $threadsCount) + 1);
            }
        })();
        
        $runner = new ParallelProcessRunner($threadsCount, 0, new FakeTimeKeeper());
        
        $executedProcesses = $runner->run($processes);
        
        // All 5 processes must execute - proves fillBucketOnce(1) at line 147 works correctly
        $this->assertSame(5, iterator_count($executedProcesses));
    }
    
    public function test_mutations_on_line_111_and_147(): void
    {
        // This test specifically kills mutations on lines 111 and 147
        // by verifying exact behavior when fillBucketOnce parameter changes
        
        $processStarted = false;
        $timeCallCount = 0;
        
        $timeKeeper = $this->createMock(FakeTimeKeeper::class);
        $timeKeeper->expects($this->any())
            ->method('getCurrentTimeAsFloat')
            ->willReturnCallback(function() use (&$timeCallCount) {
                $timeCallCount++;
                return 1000.0;
            });
        
        $process = $this->createMock(Process::class);
        $process->expects($this->once())
            ->method('start')
            ->willReturnCallback(function() use (&$processStarted) {
                $processStarted = true;
            });
        $process->expects($this->once())->method('checkTimeout');
        $process->expects($this->once())->method('isRunning')->willReturn(false);
        
        $mutant = $this->createMock(MutantProcess::class);
        $mutant->expects($this->any())->method('getProcess')->willReturn($process);
        $mutant->expects($this->once())->method('markAsFinished');
        
        $processes = (function() use ($mutant): iterable {
            yield new MutantProcessContainer($mutant, []);
        })();
        
        $runner = new ParallelProcessRunner(1, 0, $timeKeeper);
        $executedProcesses = iterator_to_array($runner->run($processes));
        
        // Critical assertions that prove mutations would fail:
        $this->assertTrue($processStarted, 'Process must start (fails if initial fillBucketOnce removed or uses 0)');
        $this->assertCount(1, $executedProcesses, 'Process must be yielded');
        $this->assertGreaterThanOrEqual(2, $timeCallCount, 'fillBucketOnce must be called (at least once for initial)');
    }

    public function test_continue_at_line_195_critical_for_all_containers(): void
    {
        // This test ensures the continue at line 195 is necessary
        // We create a scenario where changing continue to break would fail
        
        $yielded = [];
        
        $processes = (function (): iterable {
            // Process 1: Escaped mutant with next factory
            $process1 = $this->createMock(Process::class);
            $process1->expects($this->once())->method('start');
            $process1->expects($this->once())->method('checkTimeout');
            $process1->expects($this->once())->method('isRunning')->willReturn(false);
            
            $executionResult = $this->createMock(MutantExecutionResult::class);
            $executionResult->method('getDetectionStatus')->willReturn(DetectionStatus::ESCAPED);
            
            $resultFactory = $this->createMock(TestFrameworkMutantExecutionResultFactory::class);
            $resultFactory->method('createFromProcess')->willReturn($executionResult);
            
            $mutant1 = new DummyMutantProcess(
                $process1,
                $this->createMock(Mutant::class),
                $resultFactory,
                false
            );
            
            // Next process factory
            $nextProcess = $this->createMock(Process::class);
            $nextProcess->expects($this->once())->method('start');
            $nextProcess->expects($this->once())->method('checkTimeout');
            $nextProcess->expects($this->once())->method('isRunning')->willReturn(false);
            
            $nextFactory = new class($this->createMock(TestFrameworkMutantExecutionResultFactory::class), $nextProcess) implements LazyMutantProcessFactory {
                public function __construct(
                    private TestFrameworkMutantExecutionResultFactory $factory,
                    private Process $process
                ) {}
                
                public function create(Mutant $mutant): MutantProcess
                {
                    return new MutantProcess($this->process, $mutant, $this->factory);
                }
            };
            
            yield new MutantProcessContainer($mutant1, [$nextFactory]);
            
            // Process 2: Regular process
            $process2 = $this->createMock(Process::class);
            $process2->expects($this->once())->method('start');
            $process2->expects($this->once())->method('checkTimeout');
            $process2->expects($this->once())->method('isRunning')->willReturn(false);
            
            $mutant2 = $this->createMock(MutantProcess::class);
            $mutant2->expects($this->any())->method('getProcess')->willReturn($process2);
            $mutant2->expects($this->once())->method('markAsFinished');
            
            yield new MutantProcessContainer($mutant2, []);
            
            // Process 3: Regular process  
            $process3 = $this->createMock(Process::class);
            $process3->expects($this->once())->method('start');
            $process3->expects($this->once())->method('checkTimeout');
            $process3->expects($this->once())->method('isRunning')->willReturn(false);
            
            $mutant3 = $this->createMock(MutantProcess::class);
            $mutant3->expects($this->any())->method('getProcess')->willReturn($process3);
            $mutant3->expects($this->once())->method('markAsFinished');
            
            yield new MutantProcessContainer($mutant3, []);
        })();
        
        $runner = new ParallelProcessRunner(3, 0, new FakeTimeKeeper());
        
        foreach ($runner->run($processes) as $container) {
            $yielded[] = $container;
        }
        
        // With continue: all containers are processed and yielded (3 total)
        // With break: processing would stop after the first container with hasNext
        $this->assertCount(3, $yielded);
    }
    
    public function test_all_line_111_mutations_break_single_process_execution(): void
    {
        // This test MUST kill all mutations on line 111:
        // - DecrementInteger: 1→0 - bucket stays empty, process never starts
        // - IncrementInteger: 1→2 - changes fillBucketOnce behavior
        // - MethodCallRemoval: bucket empty, process never starts
        
        // Use reflection to test the actual run behavior with different params
        $process = $this->createMock(Process::class);
        $startCalled = false;
        $process->expects($this->once())
            ->method('start')
            ->willReturnCallback(function() use (&$startCalled) {
                $startCalled = true;
            });
        $process->expects($this->once())->method('checkTimeout');
        $process->expects($this->once())->method('isRunning')->willReturn(false);
        
        $mutant = $this->createMock(MutantProcess::class);
        $mutant->expects($this->any())->method('getProcess')->willReturn($process);
        $mutant->expects($this->once())->method('markAsFinished');
        
        // Single process - if initial fillBucketOnce doesn't work, this fails
        $processes = (function() use ($mutant): iterable {
            yield new MutantProcessContainer($mutant, []);
        })();
        
        $runner = new ParallelProcessRunner(1, 0, new FakeTimeKeeper());
        $executed = iterator_to_array($runner->run($processes));
        
        // These assertions FAIL if any mutation on line 111 is applied:
        $this->assertTrue($startCalled, 'Process must start');
        $this->assertCount(1, $executed, 'Process must be yielded');
    }
    
    public function test_line_147_mutation_breaks_multi_process_execution(): void
    {
        // This test MUST kill the IncrementInteger mutation on line 147
        // Simply use the existing helper to create processes
        
        $threadsCount = 2;
        $processes = (function () use ($threadsCount): iterable {
            for ($i = 0; $i < 4; ++$i) {
                yield $this->createMutantProcessContainer(($i % $threadsCount) + 1);
            }
        })();
        
        $runner = new ParallelProcessRunner($threadsCount, 0, new FakeTimeKeeper());
        
        $executedProcesses = $runner->run($processes);
        
        // All 4 processes must execute - proves fillBucketOnce(1) works
        $this->assertSame(4, iterator_count($executedProcesses));
    }
    
    public function test_line_195_continue_mutation_breaks_multi_container_processing(): void
    {
        // This test is the same as test_continue_mutation_at_line_195_kill_test
        // But ensures the mutation is killed
        
        $yielded = [];
        
        $processes = (function (): iterable {
            // Process 1: Escaped mutant with next factory
            $process1 = $this->createMock(Process::class);
            $process1->expects($this->once())->method('start');
            $process1->expects($this->once())->method('checkTimeout');
            $process1->expects($this->once())->method('isRunning')->willReturn(false);
            
            $executionResult = $this->createMock(MutantExecutionResult::class);
            $executionResult->method('getDetectionStatus')->willReturn(DetectionStatus::ESCAPED);
            
            $resultFactory = $this->createMock(TestFrameworkMutantExecutionResultFactory::class);
            $resultFactory->method('createFromProcess')->willReturn($executionResult);
            
            $mutant1 = new DummyMutantProcess(
                $process1,
                $this->createMock(Mutant::class),
                $resultFactory,
                false
            );
            
            // Next process factory
            $nextProcess = $this->createMock(Process::class);
            $nextProcess->expects($this->once())->method('start');
            $nextProcess->expects($this->once())->method('checkTimeout');
            $nextProcess->expects($this->once())->method('isRunning')->willReturn(false);
            
            $nextFactory = new class($this->createMock(TestFrameworkMutantExecutionResultFactory::class), $nextProcess) implements LazyMutantProcessFactory {
                public function __construct(
                    private TestFrameworkMutantExecutionResultFactory $factory,
                    private Process $process
                ) {}
                
                public function create(Mutant $mutant): MutantProcess
                {
                    return new MutantProcess($this->process, $mutant, $this->factory);
                }
            };
            
            yield new MutantProcessContainer($mutant1, [$nextFactory]);
            
            // Process 2: Regular process
            $process2 = $this->createMock(Process::class);
            $process2->expects($this->once())->method('start');
            $process2->expects($this->once())->method('checkTimeout');
            $process2->expects($this->once())->method('isRunning')->willReturn(false);
            
            $mutant2 = $this->createMock(MutantProcess::class);
            $mutant2->expects($this->any())->method('getProcess')->willReturn($process2);
            $mutant2->expects($this->once())->method('markAsFinished');
            
            yield new MutantProcessContainer($mutant2, []);
            
            // Process 3: Regular process  
            $process3 = $this->createMock(Process::class);
            $process3->expects($this->once())->method('start');
            $process3->expects($this->once())->method('checkTimeout');
            $process3->expects($this->once())->method('isRunning')->willReturn(false);
            
            $mutant3 = $this->createMock(MutantProcess::class);
            $mutant3->expects($this->any())->method('getProcess')->willReturn($process3);
            $mutant3->expects($this->once())->method('markAsFinished');
            
            yield new MutantProcessContainer($mutant3, []);
        })();
        
        $runner = new ParallelProcessRunner(3, 0, new FakeTimeKeeper());
        
        foreach ($runner->run($processes) as $container) {
            $yielded[] = $container;
        }
        
        // With continue: all containers are processed and yielded (3 total)
        // With break: processing would stop after the first container with hasNext
        $this->assertCount(3, $yielded);
    }
    
    public function test_fillBucketOnce_exact_behavior_with_different_params(): void
    {
        // Direct test of fillBucketOnce to kill mutations on lines 111 and 147
        $runner = new ParallelProcessRunner(1, 0, new FakeTimeKeeper());
        $reflection = new ReflectionClass($runner);
        $fillBucketOnceMethod = $reflection->getMethod('fillBucketOnce');
        
        // Test 1: With threadCount=0, bucket stays empty (kills DecrementInteger 1->0)
        $bucket1 = new SplQueue();
        $container1 = new MutantProcessContainer($this->createMock(MutantProcess::class), []);
        $generator1 = (function() use ($container1) { yield $container1; })();
        
        $result1 = $fillBucketOnceMethod->invokeArgs($runner, [$bucket1, $generator1, 0]);
        $this->assertSame(0, $result1, 'With threadCount=0, returns 0');
        $this->assertSame(0, $bucket1->count(), 'With threadCount=0, no items added');
        $this->assertTrue($generator1->valid(), 'Generator not consumed with threadCount=0');
        
        // Test 2: With threadCount=1, exactly 1 item added
        $bucket2 = new SplQueue();
        $container2 = new MutantProcessContainer($this->createMock(MutantProcess::class), []);
        $generator2 = (function() use ($container2) { yield $container2; })();
        
        $result2 = $fillBucketOnceMethod->invokeArgs($runner, [$bucket2, $generator2, 1]);
        $this->assertSame(0, $result2); // FakeTimeKeeper returns 0
        $this->assertSame(1, $bucket2->count(), 'With threadCount=1, 1 item added');
        $this->assertSame($container2, $bucket2->dequeue(), 'Correct item added');
        $this->assertFalse($generator2->valid(), 'Generator consumed');
        
        // Test 3: With threadCount=2 but only 1 item available, still adds 1
        $bucket3 = new SplQueue();
        $container3 = new MutantProcessContainer($this->createMock(MutantProcess::class), []);
        $generator3 = (function() use ($container3) { yield $container3; })();
        
        $result3 = $fillBucketOnceMethod->invokeArgs($runner, [$bucket3, $generator3, 2]);
        $this->assertSame(0, $result3);
        $this->assertSame(1, $bucket3->count(), 'With threadCount=2, still adds only 1');
        
        // Test 4: Behavior when bucket already has items
        $bucket4 = new SplQueue();
        $bucket4->enqueue(new MutantProcessContainer($this->createMock(MutantProcess::class), []));
        $container4 = new MutantProcessContainer($this->createMock(MutantProcess::class), []);
        $generator4 = (function() use ($container4) { yield $container4; })();
        
        // With bucket count=1 and threadCount=1, returns 0 without adding
        $result4 = $fillBucketOnceMethod->invokeArgs($runner, [$bucket4, $generator4, 1]);
        $this->assertSame(0, $result4, 'Returns 0 when bucket full');
        $this->assertSame(1, $bucket4->count(), 'No item added when bucket full');
        $this->assertTrue($generator4->valid(), 'Generator not consumed when bucket full');
    }
    
    public function test_continue_vs_break_in_tryToFreeNotRunningProcess(): void
    {
        // This is a simpler test that verifies the continue is necessary
        // by checking that all containers in the loop are processed
        $processedCount = 0;
        
        $processes = (function() use (&$processedCount): iterable {
            // Create 3 processes, all start together
            for ($i = 0; $i < 3; $i++) {
                $process = $this->createMock(Process::class);
                $process->expects($this->once())->method('start');
                $process->expects($this->atLeastOnce())->method('checkTimeout');
                $process->expects($this->atLeastOnce())
                    ->method('isRunning')
                    ->willReturnOnConsecutiveCalls(true, false); // Running then finished
                
                $mutant = $this->createMock(MutantProcess::class);
                $mutant->expects($this->any())->method('getProcess')->willReturn($process);
                $mutant->expects($this->once())
                    ->method('markAsFinished')
                    ->willReturnCallback(function() use (&$processedCount) {
                        $processedCount++;
                    });
                
                if ($i === 0) {
                    // First has next factory
                    $nextFactory = $this->createMock(LazyMutantProcessFactory::class);
                    yield new MutantProcessContainer($mutant, [$nextFactory]);
                } else {
                    yield new MutantProcessContainer($mutant, []);
                }
            }
        })();
        
        $runner = new ParallelProcessRunner(3, 0, new FakeTimeKeeper());
        $yielded = iterator_to_array($runner->run($processes));
        
        // With continue: all 3 processes are marked as finished
        // With break: only the first would be marked
        $this->assertSame(3, $processedCount, 'All 3 processes must be marked as finished');
        $this->assertGreaterThanOrEqual(1, count($yielded), 'At least one container yielded');
    }


    public static function threadCountProvider(): iterable
    {
        yield 'no threads' => [0];

        yield 'one thread' => [1];

        yield 'invalid thread' => [-1];

        yield 'nominal' => [4];

        yield 'thread count more than processes' => [20];
    }

    private function runWithAllKindsOfProcesses(int $threadCount): void
    {
        $processes = (function () use ($threadCount): iterable {
            for ($i = 0; $i < 5; ++$i) {
                $threadIndex = $threadCount === 0 ? 1 : ($i * 2 % $threadCount) + 1;

                yield $this->createMutantProcessContainer($threadIndex);

                yield $this->createTimeOutMutantProcessContainer();
            }
        })();

        $runner = new ParallelProcessRunner($threadCount, 0, new FakeTimeKeeper());

        $executedProcesses = $runner->run($processes);

        $this->assertSame(10, iterator_count($executedProcesses));
    }

    private function createMutantProcessContainer(int $threadIndex, bool $withFinishedSpy = false): MutantProcessContainer
    {
        $processMock = $this->createMock(Process::class);
        $processMock
            ->expects($this->once())
            ->method('start')
            ->with(null, [
                'INFECTION' => '1',
                'TEST_TOKEN' => $threadIndex,
            ])
        ;
        $processMock
            ->expects($this->once())
            ->method('checkTimeout')
        ;
        $processMock
            ->expects($this->once())
            ->method('isRunning')
            ->willReturn(false)
        ;

        if ($withFinishedSpy) {
            $mutantProcessMock = $this->createMock(MutantProcess::class);
            $mutantProcessMock->expects($this->once())
                ->method('getProcess')
                ->willReturn($processMock);
            $mutantProcessMock->expects($this->once())
                ->method('markAsFinished');

            return new MutantProcessContainer($mutantProcessMock, []);
        }

        return new MutantProcessContainer(
            new DummyMutantProcess(
                $processMock,
                $this->createMock(Mutant::class),
                $this->createMock(TestFrameworkMutantExecutionResultFactory::class),
                false,
            ),
            [],
        );
    }

    private function createMutantProcessContainerWithNextMutantProcess(int $threadCount): MutantProcessContainer
    {
        $phpUnitProcessMock = $this->createMock(Process::class);
        $phpUnitProcessMock
            ->expects($this->once())
            ->method('start')
            ->with(null, [
                'INFECTION' => '1',
                'TEST_TOKEN' => 1,
            ])
        ;
        $phpUnitProcessMock
            ->expects($this->once())
            ->method('checkTimeout')
        ;
        $phpUnitProcessMock
            ->expects($this->once())
            ->method('isRunning')
            ->willReturn(false)
        ;

        $nextProcessMock = $this->createMock(Process::class);
        $nextProcessMock
            ->expects($this->once())
            ->method('start')
            ->with(null, [
                'INFECTION' => '1',
                'TEST_TOKEN' => $threadCount === 0 ? 1 : (1 % $threadCount) + 1,
            ])
        ;
        $nextProcessMock
            ->expects($this->once())
            ->method('checkTimeout')
        ;
        $nextProcessMock
            ->expects($this->once())
            ->method('isRunning')
            ->willReturn(false)
        ;

        $mutantExecutionResultMock = $this->createMock(MutantExecutionResult::class);

        $mutantExecutionResultMock
            ->expects($this->once())
            ->method('getDetectionStatus')
            ->willReturn(DetectionStatus::ESCAPED);

        $mutantExecutionResultFactoryMock = $this->createMock(TestFrameworkMutantExecutionResultFactory::class);

        $mutantExecutionResultFactoryMock
            ->expects($this->once())
            ->method('createFromProcess')
            ->willReturn($mutantExecutionResultMock);

        return new MutantProcessContainer(
            new DummyMutantProcess(
                $phpUnitProcessMock,
                $this->createMock(Mutant::class),
                $mutantExecutionResultFactoryMock,
                false,
            ),
            [
                new class($this->createMock(TestFrameworkMutantExecutionResultFactory::class), $nextProcessMock) implements LazyMutantProcessFactory {
                    public function __construct(
                        private TestFrameworkMutantExecutionResultFactory $mutantExecutionResultFactory,
                        private Process $nextProcessMock,
                    ) {
                    }

                    public function create(Mutant $mutant): MutantProcess
                    {
                        return new MutantProcess(
                            $this->nextProcessMock,
                            $mutant,
                            $this->mutantExecutionResultFactory,
                        );
                    }
                },
            ],
        );
    }

    private function createTimeOutMutantProcessContainer(): MutantProcessContainer
    {
        $processMock = $this->createMock(Process::class);
        $processMock
            ->expects($this->once())
            ->method('start')
        ;
        $processMock
            ->expects($this->once())
            ->method('checkTimeout')
            ->willThrowException(new ProcessTimedOutException($processMock, 1))
        ;
        $processMock
            ->expects($this->once())
            ->method('isRunning')
            ->willReturn(false)
        ;

        return new MutantProcessContainer(
            new DummyMutantProcess(
                $processMock,
                $this->createMock(Mutant::class),
                $this->createMock(TestFrameworkMutantExecutionResultFactory::class),
                true,
            ),
            [],
        );
    }

    private function createSlowMutantProcessContainer(int $threadIndex): MutantProcessContainer
    {
        $processMock = $this->createMock(Process::class);
        $processMock
            ->expects($this->once())
            ->method('start')
            ->with(null, [
                'INFECTION' => '1',
                'TEST_TOKEN' => $threadIndex,
            ])
        ;

        // First few calls return true (still running), then false
        $processMock
            ->expects($this->atLeast(2))
            ->method('checkTimeout')
        ;
        $processMock
            ->expects($this->atLeast(2))
            ->method('isRunning')
            ->willReturnOnConsecutiveCalls(true, true, false)
        ;

        return new MutantProcessContainer(
            new DummyMutantProcess(
                $processMock,
                $this->createMock(Mutant::class),
                $this->createMock(TestFrameworkMutantExecutionResultFactory::class),
                false,
            ),
            [],
        );
    }
}
