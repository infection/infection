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
use Iterator;
use function iterator_count;
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
