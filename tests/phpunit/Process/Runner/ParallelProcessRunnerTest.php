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

use function array_search;
use function count;
use DuoClock\TimeSpy;
use Infection\Mutant\DetectionStatus;
use Infection\Mutant\Mutant;
use Infection\Mutant\TestFrameworkMutantExecutionResultFactory;
use Infection\Process\Factory\LazyMutantProcessFactory;
use Infection\Process\MutantProcess;
use Infection\Process\MutantProcessContainer;
use Infection\Process\Runner\IndexedMutantProcessContainer;
use Infection\Process\Runner\ParallelProcessRunner;
use Infection\Process\Runner\ProcessQueue;
use Infection\Tests\Fixtures\Process\DummyMutantProcess;
use Infection\Tests\Mutant\MutantBuilder;
use Infection\Tests\Mutant\MutantExecutionResultBuilder;
use function iterator_count;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;

#[CoversClass(ParallelProcessRunner::class)]
final class ParallelProcessRunnerTest extends TestCase
{
    private const SIMULATED_TIME_MICROSECONDS = 1_000;

    public function test_it_does_nothing_when_no_process_is_given(): void
    {
        $clock = $this->createMock(TimeSpy::class);
        $clock->expects($this->never())
            ->method($this->anything());

        $runner = new ParallelProcessRunner(4, 0, $clock);

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

        $runner = new ParallelProcessRunner($threadsCount, 0, new TimeSpy());

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

        $runner = new ParallelProcessRunner(4, 0, new TimeSpy());

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

        $runner = new ParallelProcessRunner($threadCount, 0, new TimeSpy());

        $runner->run($processes);

        $executedProcesses = $runner->run($processes);

        $this->assertSame(1, iterator_count($executedProcesses));
    }

    #[DataProvider('threadCountProvider')]
    public function test_it_handles_all_kids_of_processes_with_infinite_threads(int $threadCount): void
    {
        $this->runWithAllKindsOfProcesses($threadCount);
    }

    public function test_initial_enqueue_from_is_called_before_loop(): void
    {
        // This test verifies that enqueueFrom is called before the do-while loop starts
        // to pre-load the first process, buying time during setup

        $queueMock = $this->createMock(ProcessQueue::class);

        /** @var list<string> */
        $callSequence = [];

        // enqueueFrom should be called before any isEmpty checks
        $queueMock->expects($this->atLeastOnce())
            ->method('enqueueFrom')
            ->willReturnCallback(static function () use (&$callSequence) {
                $callSequence[] = 'enqueueFrom';

                return 0;
            });

        $queueMock->expects($this->atLeastOnce())
            ->method('isEmpty')
            ->willReturnCallback(static function () use (&$callSequence) {
                $callSequence[] = 'isEmpty';

                return true; // Exit loop immediately
            });

        $runner = new ParallelProcessRunner(2, 0, new TimeSpy(), $queueMock);

        iterator_count($runner->run([]));

        // Verify enqueueFrom was called before the first isEmpty check
        $this->assertNotEmpty($callSequence, 'Call sequence must be tracked');
        $this->assertSame('enqueueFrom', $callSequence[0], 'First call must be enqueueFrom (pre-loading)');
        $this->assertContains('isEmpty', $callSequence, 'isEmpty must be called during loop');

        // Find the first isEmpty call
        $firstIsEmptyIndex = array_search('isEmpty', $callSequence, true);
        $this->assertNotFalse($firstIsEmptyIndex, 'isEmpty must be called');
        $this->assertGreaterThan(0, $firstIsEmptyIndex, 'enqueueFrom must be called before isEmpty');
    }

    public static function threadCountProvider(): iterable
    {
        yield 'no threads' => [0];

        yield 'one thread' => [1];

        yield 'invalid thread' => [-1];

        yield 'nominal' => [4];

        yield 'thread count more than processes' => [20];
    }

    public function test_has_processes_that_could_be_freed_greater_than_or_equal_to_behavior(): void
    {
        // This test kills the GreaterThanOrEqualTo mutation
        // Original: count($this->runningProcessContainers) >= $threadCount
        // Mutated: count($this->runningProcessContainers) > $threadCount

        $runner = new ParallelProcessRunner(2, 0, new TimeSpy());

        $reflection = new ReflectionClass($runner);

        // Set up runningProcessContainers with exactly 2 items
        $runningProcessContainers = $reflection->getProperty('runningProcessContainers');
        $runningProcessContainers->setValue($runner, [
            0 => new IndexedMutantProcessContainer(1, $this->createMock(MutantProcessContainer::class)),
            1 => new IndexedMutantProcessContainer(2, $this->createMock(MutantProcessContainer::class)),
        ]);

        $method = $reflection->getMethod('hasProcessesThatCouldBeFreed');

        // When count == threadCount, >= returns true, > returns false
        $this->assertTrue($method->invokeArgs($runner, [2]), 'Should return true when count equals threadCount');

        // When count > threadCount, both >= and > return true
        $runningProcessContainers->setValue($runner, [
            0 => new IndexedMutantProcessContainer(1, $this->createMock(MutantProcessContainer::class)),
            1 => new IndexedMutantProcessContainer(2, $this->createMock(MutantProcessContainer::class)),
            2 => new IndexedMutantProcessContainer(3, $this->createMock(MutantProcessContainer::class)),
        ]);

        $this->assertTrue($method->invokeArgs($runner, [2]), 'Should return true when count > threadCount');

        // When count < threadCount, both >= and > return false
        $runningProcessContainers->setValue($runner, [
            0 => new IndexedMutantProcessContainer(1, $this->createMock(MutantProcessContainer::class)),
        ]);

        $this->assertFalse($method->invokeArgs($runner, [2]), 'Should return false when count < threadCount');
    }

    public function test_wait_method_with_decrement_integer_mutation(): void
    {
        // This test kills the DecrementInteger mutation
        // Original: max(0, $this->poll - $timeSpentDoingWork)
        // Mutated: max(-1, $this->poll - $timeSpentDoingWork)

        $clockMock = $this->createMock(TimeSpy::class);
        $runner = new ParallelProcessRunner(2, 10000, $clockMock); // 10ms poll time

        $reflection = new ReflectionClass($runner);
        $method = $reflection->getMethod('sleepRemaining');

        // Test scenario where poll - timeSpentDoingWork would be negative
        $timeSpentDoingWork = 15000; // 15ms, more than poll time

        // With original code: max(0, 10000 - 15000) = max(0, -5000) = 0
        // With mutated code: max(-1, 10000 - 15000) = max(-1, -5000) = -1
        $clockMock->expects($this->once())
            ->method('usleep')
            ->with($this->identicalTo(0)); // Should be 0, not -1

        $method->invokeArgs($runner, [$timeSpentDoingWork]);
    }

    public function test_wait_method_with_increment_integer_mutation(): void
    {
        // This test kills the IncrementInteger mutation
        // Original: max(0, $this->poll - $timeSpentDoingWork)
        // Mutated: max(1, $this->poll - $timeSpentDoingWork)

        $clockMock = $this->createMock(TimeSpy::class);
        $runner = new ParallelProcessRunner(2, 10000, $clockMock); // 10ms poll time

        $reflection = new ReflectionClass($runner);
        $method = $reflection->getMethod('sleepRemaining');

        // Test scenario where poll - timeSpentDoingWork would be exactly 0
        $timeSpentDoingWork = 10000; // Exactly poll time

        // With original code: max(0, 10000 - 10000) = max(0, 0) = 0
        // With mutated code: max(1, 10000 - 10000) = max(1, 0) = 1
        $clockMock->expects($this->once())
            ->method('usleep')
            ->with($this->identicalTo(0)); // Should be 0, not 1

        $method->invokeArgs($runner, [$timeSpentDoingWork]);
    }

    public function test_wait_method_with_minus_mutation(): void
    {
        // This test kills the Minus mutation
        // Original: max(0, $this->poll - $timeSpentDoingWork)
        // Mutated: max(0, $this->poll + $timeSpentDoingWork)

        $clockMock = $this->createMock(TimeSpy::class);
        $runner = new ParallelProcessRunner(2, 5000, $clockMock); // 5ms poll time

        $reflection = new ReflectionClass($runner);
        $method = $reflection->getMethod('sleepRemaining');

        $timeSpentDoingWork = 2000; // 2ms

        // With original code: max(0, 5000 - 2000) = max(0, 3000) = 3000
        // With mutated code: max(0, 5000 + 2000) = max(0, 7000) = 7000
        $clockMock->expects($this->once())
            ->method('usleep')
            ->with($this->identicalTo(3000)); // Should be 3000, not 7000

        $method->invokeArgs($runner, [$timeSpentDoingWork]);
    }

    public function test_wait_method_call_removal_mutation(): void
    {
        // This test kills the MethodCallRemoval mutation
        // Original: $this->clock->usleep(max(0, $this->poll - $timeSpentDoingWork));
        // Mutated: (empty)

        $clockMock = $this->createMock(TimeSpy::class);
        $runner = new ParallelProcessRunner(2, 5000, $clockMock); // 5ms poll time

        $reflection = new ReflectionClass($runner);
        $method = $reflection->getMethod('sleepRemaining');

        $timeSpentDoingWork = 1000; // 1ms

        // usleep must be called
        $clockMock->expects($this->once())
            ->method('usleep')
            ->with($this->identicalTo(4000)); // Should be 4000

        $method->invokeArgs($runner, [$timeSpentDoingWork]);
    }

    public function test_mark_as_timed_out_method_call_removal_mutation(): void
    {
        // This test kills the MethodCallRemoval mutation
        // Original: $mutantProcess->markAsTimedOut();
        // Mutated: (empty)

        $runner = new ParallelProcessRunner(1, 0, new TimeSpy());

        $reflection = new ReflectionClass($runner);

        // Create a timeout process that will throw ProcessTimedOutException
        $processMock = $this->createMock(Process::class);
        $processMock->expects($this->once())
            ->method('checkTimeout')
            ->willThrowException(new ProcessTimedOutException($processMock, 1));
        $processMock->expects($this->once())
            ->method('isRunning')
            ->willReturn(false);

        $mutantProcessMock = $this->createMock(MutantProcess::class);
        $mutantProcessMock->expects($this->once())
            ->method('getProcess')
            ->willReturn($processMock);
        $mutantProcessMock->expects($this->once())
            ->method('markAsTimedOut'); // This call must happen

        $mutantProcessContainerMock = $this->createMock(MutantProcessContainer::class);
        $mutantProcessContainerMock->expects($this->once())
            ->method('getCurrent')
            ->willReturn($mutantProcessMock);

        $container = new IndexedMutantProcessContainer(0, $mutantProcessContainerMock);

        // Set up runningProcessContainers
        $runningProcessContainers = $reflection->getProperty('runningProcessContainers');
        $runningProcessContainers->setValue($runner, [0 => $container]);

        $availableThreadIndexes = $reflection->getProperty('availableThreadIndexes');
        $availableThreadIndexes->setValue($runner, []);

        $method = $reflection->getMethod('tryToFreeNotRunningProcess');

        // This should call markAsTimedOut on the mutant process
        iterator_count($method->invokeArgs($runner, []));
    }

    public function test_mark_as_finished_method_call_removal_mutation(): void
    {
        // This test kills the MethodCallRemoval mutation
        // Original: $mutantProcess->markAsFinished();
        // Mutated: (empty)

        $runner = new ParallelProcessRunner(1, 0, new TimeSpy());

        $reflection = new ReflectionClass($runner);

        // Create a finished process
        $processMock = $this->createMock(Process::class);
        $processMock->expects($this->once())
            ->method('checkTimeout'); // No exception
        $processMock->expects($this->once())
            ->method('isRunning')
            ->willReturn(false); // Process is not running

        $mutantProcessMock = $this->createMock(MutantProcess::class);
        $mutantProcessMock->expects($this->once())
            ->method('getProcess')
            ->willReturn($processMock);
        $mutantProcessMock->expects($this->once())
            ->method('markAsFinished'); // This call must happen

        $mutantProcessContainerMock = $this->createMock(MutantProcessContainer::class);
        $mutantProcessContainerMock->expects($this->once())
            ->method('getCurrent')
            ->willReturn($mutantProcessMock);

        $container = new IndexedMutantProcessContainer(0, $mutantProcessContainerMock);

        // Set up runningProcessContainers
        $runningProcessContainers = $reflection->getProperty('runningProcessContainers');
        $runningProcessContainers->setValue($runner, [0 => $container]);

        $availableThreadIndexes = $reflection->getProperty('availableThreadIndexes');
        $availableThreadIndexes->setValue($runner, []);

        $method = $reflection->getMethod('tryToFreeNotRunningProcess');

        // This should call markAsFinished on the mutant process
        iterator_count($method->invokeArgs($runner, []));
    }

    public function test_while_loop_condition_with_while_mutation(): void
    {
        // This test kills the While_ mutation
        // Original: while ($this->hasProcessesThatCouldBeFreed($threadCount))
        // Mutated: while (false)

        $runner = $this->getMockBuilder(ParallelProcessRunner::class)
            ->setConstructorArgs([2, 0, new TimeSpy(), new ProcessQueue()])
            ->onlyMethods(['hasProcessesThatCouldBeFreed'])
            ->getMock();

        $callCount = 0;

        // hasProcessesThatCouldBeFreed should be called and return true at least once
        $runner->expects($this->atLeastOnce())
            ->method('hasProcessesThatCouldBeFreed')
            ->willReturnCallback(static function () use (&$callCount) {
                ++$callCount;

                return $callCount <= 2; // Return true twice, then false
            });

        // Create processes
        $processes = [];

        for ($i = 0; $i < 3; ++$i) {
            $process = $this->createMock(Process::class);
            $process->expects($this->once())->method('start');
            $process->method('isRunning')->willReturn(false);

            $mutantProcess = new DummyMutantProcess(
                $process,
                MutantBuilder::withMinimalTestData()->build(),
                $this->createMock(TestFrameworkMutantExecutionResultFactory::class),
                false,
            );

            $processes[] = new MutantProcessContainer($mutantProcess, []);
        }

        // Run the processes - the while loop should execute because hasProcessesThatCouldBeFreed returns true
        iterator_count($runner->run($processes));

        // If the while condition was mutated to false, hasProcessesThatCouldBeFreed would never be called
        $this->assertGreaterThan(0, $callCount, 'hasProcessesThatCouldBeFreed should be called in while loop');
    }

    public function test_while_loop_wait_call_with_method_call_removal_mutation(): void
    {
        // This test kills the MethodCallRemoval mutation
        // Original: $this->sleepRemaining(timeSpentDoingWork: $this->queue->enqueueFrom(...));
        // Mutated: (empty)

        $queueMock = $this->createMock(ProcessQueue::class);
        $queueMock->expects($this->atLeastOnce())
            ->method('enqueueFrom')
            ->willReturn(self::SIMULATED_TIME_MICROSECONDS); // Return 1000 microseconds

        $queueMock->expects($this->atLeastOnce())
            ->method('isEmpty')
            ->willReturn(false, false, false, true); // Need processes to run, then stop

        $runner = $this->getMockBuilder(ParallelProcessRunner::class)
            ->setConstructorArgs([2, 0, new TimeSpy(), $queueMock])
            ->onlyMethods(['hasProcessesThatCouldBeFreed', 'sleepRemaining'])
            ->getMock();

        $callCount = 0;

        // hasProcessesThatCouldBeFreed should return true once to enter the loop
        $runner->expects($this->atLeastOnce())
            ->method('hasProcessesThatCouldBeFreed')
            ->willReturnCallback(static function () use (&$callCount) {
                ++$callCount;

                return $callCount <= 1; // Return true once, then false
            });

        // sleepRemaining should be called with the return value from enqueueFrom
        $runner->expects($this->atLeastOnce())
            ->method('sleepRemaining')
            ->with($this->identicalTo(self::SIMULATED_TIME_MICROSECONDS)); // Must be called with the enqueueFrom return value

        // Create processes
        $processes = [];

        for ($i = 0; $i < 2; ++$i) {
            $process = $this->createMock(Process::class);
            $process->method('start'); // May or may not be called depending on mock behavior
            $process->method('isRunning')->willReturn(false);

            $mutantProcess = new DummyMutantProcess(
                $process,
                MutantBuilder::withMinimalTestData()->build(),
                $this->createMock(TestFrameworkMutantExecutionResultFactory::class),
                false,
            );

            $processes[] = new MutantProcessContainer($mutantProcess, []);
        }

        // Setup queue mock to return the processes
        $container = $processes[0];
        $queueMock->expects($this->atLeastOnce())
            ->method('dequeue')
            ->willReturn($container);

        // Run the processes - sleepRemaining should be called with the enqueueFrom return value
        iterator_count($runner->run($processes));
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

        $runner = new ParallelProcessRunner($threadCount, 0, new TimeSpy());

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
                MutantBuilder::withMinimalTestData()->build(),
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

        $mutantExecutionResult = MutantExecutionResultBuilder::withMinimalTestData()
            ->withDetectionStatus(DetectionStatus::ESCAPED)
            ->build();

        $mutantExecutionResultFactoryMock = $this->createMock(TestFrameworkMutantExecutionResultFactory::class);

        $mutantExecutionResultFactoryMock
            ->expects($this->once())
            ->method('createFromProcess')
            ->willReturn($mutantExecutionResult);

        return new MutantProcessContainer(
            new DummyMutantProcess(
                $phpUnitProcessMock,
                MutantBuilder::withMinimalTestData()->build(),
                $mutantExecutionResultFactoryMock,
                false,
            ),
            [
                new class($this->createMock(TestFrameworkMutantExecutionResultFactory::class), $nextProcessMock) implements LazyMutantProcessFactory {
                    public function __construct(
                        private readonly TestFrameworkMutantExecutionResultFactory $mutantExecutionResultFactory,
                        private readonly Process $nextProcessMock,
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
                MutantBuilder::withMinimalTestData()->build(),
                $this->createMock(TestFrameworkMutantExecutionResultFactory::class),
                true,
            ),
            [],
        );
    }
}
