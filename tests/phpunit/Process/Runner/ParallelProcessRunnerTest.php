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

    private function createMutantProcessContainer(int $threadIndex): MutantProcessContainer
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
