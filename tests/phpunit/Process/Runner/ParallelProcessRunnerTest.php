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

use function count;
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
use function iterator_to_array;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use SplQueue;
use stdClass;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;
use Tumblr\Chorus\FakeTimeKeeper;
use Tumblr\Chorus\TimeKeeper;

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

        $bucket = new SplQueue();

        $iterator = $this->createMock(Iterator::class);
        $iterator->expects($this->once())
            ->method('valid')
            ->willReturn(false);

        $iterator->expects($this->never())
            ->method('current');

        $reflection = new ReflectionClass($runner);
        $fillBucketOnceMethod = $reflection->getMethod('fillBucketOnce');
        $result = $fillBucketOnceMethod->invokeArgs($runner, [$bucket, $iterator, 1]);

        // Should return 0 immediately when generator is not valid
        $this->assertSame(0, $result);
    }

    public function test_initial_fill_bucket_once_called_with_one_using_mock(): void
    {
        // This test uses a partial mock to verify fillBucketOnce is called with 1
        // This will kill the IncrementInteger mutation on line 115

        $runner = $this->getMockBuilder(ParallelProcessRunner::class)
            ->setConstructorArgs([4, 0, new FakeTimeKeeper()])
            ->onlyMethods(['fillBucketOnce'])
            ->getMock();

        // Create one process
        $process = $this->createMock(Process::class);
        $process->expects($this->once())->method('start');
        $process->expects($this->any())->method('isRunning')->willReturn(false);

        $mutantProcess = new DummyMutantProcess(
            $process,
            $this->createMock(Mutant::class),
            $this->createMock(TestFrameworkMutantExecutionResultFactory::class),
            false,
        );

        $container = new MutantProcessContainer($mutantProcess, []);

        // Expect fillBucketOnce to be called with exactly 1 as the third parameter
        $runner->expects($this->atLeastOnce())
            ->method('fillBucketOnce')
            ->with(
                $this->isInstanceOf(SplQueue::class),
                $this->isInstanceOf(Iterator::class),
                $this->identicalTo(1), // Must be exactly 1, not 2
            )
            ->willReturnCallback(static function ($bucket, $iterator, $threadCount) {
                if ($iterator->valid() && count($bucket) < $threadCount) {
                    $bucket->enqueue($iterator->current());
                    $iterator->next();
                }

                return 0;
            });

        // Run the process
        iterator_to_array($runner->run([$container]));
    }

    public function test_fill_bucket_once_at_line_151_called_with_one_using_mock(): void
    {
        // This test verifies the fillBucketOnce call at line 151 uses hardcoded 1
        // This will kill the IncrementInteger mutation on line 151

        $runner = $this->getMockBuilder(ParallelProcessRunner::class)
            ->setConstructorArgs([2, 0, new FakeTimeKeeper()])
            ->onlyMethods(['fillBucketOnce'])
            ->getMock();

        // Create multiple processes
        $processes = [];

        for ($i = 0; $i < 3; ++$i) {
            $process = $this->createMock(Process::class);
            $process->expects($this->once())->method('start');
            $process->expects($this->any())->method('isRunning')->willReturn(false);

            $mutantProcess = new DummyMutantProcess(
                $process,
                $this->createMock(Mutant::class),
                $this->createMock(TestFrameworkMutantExecutionResultFactory::class),
                false,
            );

            $processes[] = new MutantProcessContainer($mutantProcess, []);
        }

        $callCount = 0;

        // Expect all fillBucketOnce calls to use 1 as the third parameter
        $runner->expects($this->atLeastOnce())
            ->method('fillBucketOnce')
            ->with(
                $this->isInstanceOf(SplQueue::class),
                $this->isInstanceOf(Iterator::class),
                $this->identicalTo(1), // Must be exactly 1, not 2
            )
            ->willReturnCallback(static function ($bucket, $iterator, $threadCount) use (&$callCount) {
                ++$callCount;

                if ($iterator->valid() && count($bucket) < $threadCount) {
                    $bucket->enqueue($iterator->current());
                    $iterator->next();

                    return 1000; // Some time taken
                }

                return 0;
            });

        // Run the processes
        iterator_to_array($runner->run($processes));

        // Verify fillBucketOnce was called multiple times with 1
        $this->assertGreaterThan(1, $callCount, 'fillBucketOnce should be called multiple times');
    }

    public function test_initial_fill_bucket_once_method_call_is_required(): void
    {
        // This test uses a partial mock to verify the initial fillBucketOnce is called
        // before any other operations in the run() method.

        $runner = $this->getMockBuilder(ParallelProcessRunner::class)
            ->setConstructorArgs([1, 0, new FakeTimeKeeper()])
            ->onlyMethods(['fillBucketOnce', 'hasProcessesThatCouldBeFreed'])
            ->getMock();

        // Track the sequence of method calls
        $callSequence = [];

        // fillBucketOnce should be called at least twice:
        // 1. Initial call before the loop (line 115)
        // 2. Inside the loop (line 151)
        $runner->expects($this->atLeast(2))
            ->method('fillBucketOnce')
            ->willReturnCallback(static function ($bucket, $generator, $threadCount) use (&$callSequence) {
                $callSequence[] = ['method' => 'fillBucketOnce', 'threadCount' => $threadCount];

                // Simulate the real behavior
                if ($generator->valid() && count($bucket) < $threadCount) {
                    $bucket->enqueue($generator->current());
                    $generator->next();
                }

                return 0;
            });

        // hasProcessesThatCouldBeFreed is called after processes start
        $runner->expects($this->any())
            ->method('hasProcessesThatCouldBeFreed')
            ->willReturnCallback(static function () use (&$callSequence) {
                $callSequence[] = ['method' => 'hasProcessesThatCouldBeFreed'];

                return false; // Return false to avoid the while loop
            });

        // Create a single process
        $process = $this->createMock(Process::class);
        $process->expects($this->once())->method('start')
            ->willReturnCallback(static function () use (&$callSequence): void {
                $callSequence[] = ['method' => 'process.start'];
            });
        $process->expects($this->any())->method('checkTimeout');
        $process->expects($this->any())->method('isRunning')->willReturn(false);

        $mutant = new DummyMutantProcess(
            $process,
            $this->createMock(Mutant::class),
            $this->createMock(TestFrameworkMutantExecutionResultFactory::class),
            false,
        );

        $container = new MutantProcessContainer($mutant, []);
        iterator_to_array($runner->run([$container]));

        // Verify the sequence:
        // 1. First fillBucketOnce MUST happen before process.start
        $this->assertNotEmpty($callSequence, 'Call sequence must be tracked');
        $this->assertSame('fillBucketOnce', $callSequence[0]['method'], 'First call must be fillBucketOnce');
        $this->assertSame(1, $callSequence[0]['threadCount'], 'Initial fillBucketOnce must use threadCount=1');

        // Find process.start in the sequence
        $processStartIndex = null;

        foreach ($callSequence as $index => $call) {
            if ($call['method'] === 'process.start') {
                $processStartIndex = $index;

                break;
            }
        }

        $this->assertNotNull($processStartIndex, 'Process must start');
        $this->assertGreaterThan(0, $processStartIndex, 'Process must start AFTER initial fillBucketOnce');

        // This test fails if the initial fillBucketOnce is removed because:
        // - The bucket would be empty at the start of the loop
        // - The process wouldn't start until after the loop fills the bucket at line 151
        // - This would change the call sequence
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
        // This test kills the GreaterThanOrEqualTo mutation on line 161
        // Original: count($this->runningProcessContainers) >= $threadCount
        // Mutated: count($this->runningProcessContainers) > $threadCount

        $runner = new ParallelProcessRunner(2, 0, new FakeTimeKeeper());

        $reflection = new ReflectionClass($runner);

        // Set up runningProcessContainers with exactly 2 items
        $runningProcessContainers = $reflection->getProperty('runningProcessContainers');
        $runningProcessContainers->setValue($runner, [
            'dummy1' => new stdClass(),
            'dummy2' => new stdClass(),
        ]);

        $method = $reflection->getMethod('hasProcessesThatCouldBeFreed');

        // When count == threadCount, >= returns true, > returns false
        $this->assertTrue($method->invokeArgs($runner, [2]), 'Should return true when count equals threadCount');

        // When count > threadCount, both >= and > return true
        $runningProcessContainers->setValue($runner, [
            'dummy1' => new stdClass(),
            'dummy2' => new stdClass(),
            'dummy3' => new stdClass(),
        ]);

        $this->assertTrue($method->invokeArgs($runner, [2]), 'Should return true when count > threadCount');

        // When count < threadCount, both >= and > return false
        $runningProcessContainers->setValue($runner, [
            'dummy1' => new stdClass(),
        ]);

        $this->assertFalse($method->invokeArgs($runner, [2]), 'Should return false when count < threadCount');
    }

    public function test_fill_bucket_once_greater_than_or_equal_to_behavior(): void
    {
        // This test kills the GreaterThanOrEqualTo mutation on line 174
        // Original: count($bucket) >= $threadCount
        // Mutated: count($bucket) > $threadCount

        $runner = new ParallelProcessRunner(2, 0, new FakeTimeKeeper());

        $reflection = new ReflectionClass($runner);
        $method = $reflection->getMethod('fillBucketOnce');

        // Test when bucket count equals threadCount
        $bucket = new SplQueue();
        $bucket->enqueue('item1');
        $bucket->enqueue('item2');

        $iterator = $this->createMock(Iterator::class);
        $iterator->expects($this->never())->method('valid'); // Should not be called when bucket >= threadCount
        $iterator->expects($this->never())->method('current'); // Should not be called

        $result = $method->invokeArgs($runner, [$bucket, $iterator, 2]);

        // Should return 0 immediately when bucket count >= threadCount
        $this->assertSame(0, $result, 'Should return 0 when bucket count equals threadCount');

        // Test when bucket count > threadCount
        $bucket->enqueue('item3');

        $iterator2 = $this->createMock(Iterator::class);
        $iterator2->expects($this->never())->method('valid'); // Should not be called when bucket > threadCount
        $iterator2->expects($this->never())->method('current'); // Should not be called

        $result2 = $method->invokeArgs($runner, [$bucket, $iterator2, 2]);

        // Should return 0 immediately when bucket count > threadCount
        $this->assertSame(0, $result2, 'Should return 0 when bucket count > threadCount');
    }

    public function test_fill_bucket_once_time_calculation_with_minus_mutation(): void
    {
        // This test kills the Minus mutation on line 186
        // Original: ($this->timeKeeper->getCurrentTimeAsFloat() - $start)
        // Mutated: ($this->timeKeeper->getCurrentTimeAsFloat() + $start)

        $timeKeeperMock = $this->createMock(TimeKeeper::class);
        $runner = new ParallelProcessRunner(2, 0, $timeKeeperMock);

        $reflection = new ReflectionClass($runner);
        $method = $reflection->getMethod('fillBucketOnce');

        $bucket = new SplQueue();

        $iterator = $this->createMock(Iterator::class);
        $iterator->expects($this->once())->method('valid')->willReturn(true);
        $iterator->expects($this->once())->method('current')->willReturn('item');
        $iterator->expects($this->once())->method('next');

        // Mock two sequential calls to getCurrentTimeAsFloat()
        $timeKeeperMock->expects($this->exactly(2))
            ->method('getCurrentTimeAsFloat')
            ->willReturnOnConsecutiveCalls(1000.0, 1001.0); // start = 1000.0, end = 1001.0 (1 second difference)

        $result = $method->invokeArgs($runner, [$bucket, $iterator, 2]);

        // With original code: (1001.0 - 1000.0) * 1000000 = 1000000
        // With mutated code: (1001.0 + 1000.0) * 1000000 = 2001000000
        $this->assertSame(1000000, $result, 'Time calculation should use subtraction, not addition');
    }

    public function test_wait_method_with_decrement_integer_mutation(): void
    {
        // This test kills the DecrementInteger mutation on line 246
        // Original: max(0, $this->poll - $timeSpentDoingWork)
        // Mutated: max(-1, $this->poll - $timeSpentDoingWork)

        $timeKeeperMock = $this->createMock(TimeKeeper::class);
        $runner = new ParallelProcessRunner(2, 10000, $timeKeeperMock); // 10ms poll time

        $reflection = new ReflectionClass($runner);
        $method = $reflection->getMethod('wait');

        // Test scenario where poll - timeSpentDoingWork would be negative
        $timeSpentDoingWork = 15000; // 15ms, more than poll time

        // With original code: max(0, 10000 - 15000) = max(0, -5000) = 0
        // With mutated code: max(-1, 10000 - 15000) = max(-1, -5000) = -1
        $timeKeeperMock->expects($this->once())
            ->method('usleep')
            ->with($this->identicalTo(0)); // Should be 0, not -1

        $method->invokeArgs($runner, [$timeSpentDoingWork]);
    }

    public function test_wait_method_with_increment_integer_mutation(): void
    {
        // This test kills the IncrementInteger mutation on line 246
        // Original: max(0, $this->poll - $timeSpentDoingWork)
        // Mutated: max(1, $this->poll - $timeSpentDoingWork)

        $timeKeeperMock = $this->createMock(TimeKeeper::class);
        $runner = new ParallelProcessRunner(2, 10000, $timeKeeperMock); // 10ms poll time

        $reflection = new ReflectionClass($runner);
        $method = $reflection->getMethod('wait');

        // Test scenario where poll - timeSpentDoingWork would be exactly 0
        $timeSpentDoingWork = 10000; // Exactly poll time

        // With original code: max(0, 10000 - 10000) = max(0, 0) = 0
        // With mutated code: max(1, 10000 - 10000) = max(1, 0) = 1
        $timeKeeperMock->expects($this->once())
            ->method('usleep')
            ->with($this->identicalTo(0)); // Should be 0, not 1

        $method->invokeArgs($runner, [$timeSpentDoingWork]);
    }

    public function test_wait_method_with_minus_mutation(): void
    {
        // This test kills the Minus mutation on line 246
        // Original: max(0, $this->poll - $timeSpentDoingWork)
        // Mutated: max(0, $this->poll + $timeSpentDoingWork)

        $timeKeeperMock = $this->createMock(TimeKeeper::class);
        $runner = new ParallelProcessRunner(2, 5000, $timeKeeperMock); // 5ms poll time

        $reflection = new ReflectionClass($runner);
        $method = $reflection->getMethod('wait');

        $timeSpentDoingWork = 2000; // 2ms

        // With original code: max(0, 5000 - 2000) = max(0, 3000) = 3000
        // With mutated code: max(0, 5000 + 2000) = max(0, 7000) = 7000
        $timeKeeperMock->expects($this->once())
            ->method('usleep')
            ->with($this->identicalTo(3000)); // Should be 3000, not 7000

        $method->invokeArgs($runner, [$timeSpentDoingWork]);
    }

    public function test_wait_method_call_removal_mutation(): void
    {
        // This test kills the MethodCallRemoval mutation on line 246
        // Original: $this->timeKeeper->usleep(max(0, $this->poll - $timeSpentDoingWork));
        // Mutated: (empty)

        $timeKeeperMock = $this->createMock(TimeKeeper::class);
        $runner = new ParallelProcessRunner(2, 5000, $timeKeeperMock); // 5ms poll time

        $reflection = new ReflectionClass($runner);
        $method = $reflection->getMethod('wait');

        $timeSpentDoingWork = 1000; // 1ms

        // usleep must be called
        $timeKeeperMock->expects($this->once())
            ->method('usleep')
            ->with($this->identicalTo(4000)); // Should be 4000

        $method->invokeArgs($runner, [$timeSpentDoingWork]);
    }

    public function test_mark_as_timed_out_method_call_removal_mutation(): void
    {
        // This test kills the MethodCallRemoval mutation on line 204
        // Original: $mutantProcess->markAsTimedOut();
        // Mutated: (empty)

        $runner = new ParallelProcessRunner(1, 0, new FakeTimeKeeper());

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

        $container = new stdClass();
        $container->mutantProcessContainer = $mutantProcessContainerMock;
        $container->threadIndex = 0;

        // Set up runningProcessContainers
        $runningProcessContainers = $reflection->getProperty('runningProcessContainers');
        $runningProcessContainers->setValue($runner, [0 => $container]);

        $availableThreadIndexes = $reflection->getProperty('availableThreadIndexes');
        $availableThreadIndexes->setValue($runner, []);

        $method = $reflection->getMethod('tryToFreeNotRunningProcess');
        $bucket = new SplQueue();

        // This should call markAsTimedOut on the mutant process
        iterator_to_array($method->invokeArgs($runner, [$bucket]));
    }

    public function test_mark_as_finished_method_call_removal_mutation(): void
    {
        // This test kills the MethodCallRemoval mutation on line 211
        // Original: $mutantProcess->markAsFinished();
        // Mutated: (empty)

        $runner = new ParallelProcessRunner(1, 0, new FakeTimeKeeper());

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

        $container = new stdClass();
        $container->mutantProcessContainer = $mutantProcessContainerMock;
        $container->threadIndex = 0;

        // Set up runningProcessContainers
        $runningProcessContainers = $reflection->getProperty('runningProcessContainers');
        $runningProcessContainers->setValue($runner, [0 => $container]);

        $availableThreadIndexes = $reflection->getProperty('availableThreadIndexes');
        $availableThreadIndexes->setValue($runner, []);

        $method = $reflection->getMethod('tryToFreeNotRunningProcess');
        $bucket = new SplQueue();

        // This should call markAsFinished on the mutant process
        iterator_to_array($method->invokeArgs($runner, [$bucket]));
    }

    public function test_while_loop_condition_with_while_mutation(): void
    {
        // This test kills the While_ mutation on line 135
        // Original: while ($this->hasProcessesThatCouldBeFreed($threadCount))
        // Mutated: while (false)

        $runner = $this->getMockBuilder(ParallelProcessRunner::class)
            ->setConstructorArgs([2, 0, new FakeTimeKeeper()])
            ->onlyMethods(['hasProcessesThatCouldBeFreed', 'fillBucketOnce'])
            ->getMock();

        $callCount = 0;

        // hasProcessesThatCouldBeFreed should be called and return true at least once
        $runner->expects($this->atLeastOnce())
            ->method('hasProcessesThatCouldBeFreed')
            ->willReturnCallback(static function () use (&$callCount) {
                ++$callCount;

                return $callCount <= 2; // Return true twice, then false
            });

        // fillBucketOnce should be called in the while loop
        $runner->expects($this->atLeastOnce())
            ->method('fillBucketOnce')
            ->willReturnCallback(static function ($bucket, $iterator, $threadCount) {
                // Simulate adding processes to bucket
                if ($iterator->valid() && count($bucket) < $threadCount) {
                    $bucket->enqueue($iterator->current());
                    $iterator->next();
                }

                return 0;
            });

        // Create processes
        $processes = [];

        for ($i = 0; $i < 3; ++$i) {
            $process = $this->createMock(Process::class);
            $process->expects($this->once())->method('start');
            $process->expects($this->any())->method('isRunning')->willReturn(false);

            $mutantProcess = new DummyMutantProcess(
                $process,
                $this->createMock(Mutant::class),
                $this->createMock(TestFrameworkMutantExecutionResultFactory::class),
                false,
            );

            $processes[] = new MutantProcessContainer($mutantProcess, []);
        }

        // Run the processes - the while loop should execute because hasProcessesThatCouldBeFreed returns true
        iterator_to_array($runner->run($processes));

        // If the while condition was mutated to false, hasProcessesThatCouldBeFreed would never be called
        $this->assertGreaterThan(0, $callCount, 'hasProcessesThatCouldBeFreed should be called in while loop');
    }

    public function test_while_loop_wait_call_with_method_call_removal_mutation(): void
    {
        // This test kills the MethodCallRemoval mutation on line 138
        // Original: $this->wait($this->fillBucketOnce($bucket, $generator, $threadCount));
        // Mutated: (empty)

        $runner = $this->getMockBuilder(ParallelProcessRunner::class)
            ->setConstructorArgs([2, 0, new FakeTimeKeeper()])
            ->onlyMethods(['hasProcessesThatCouldBeFreed', 'fillBucketOnce', 'wait'])
            ->getMock();

        $callCount = 0;

        // hasProcessesThatCouldBeFreed should return true once to enter the loop
        $runner->expects($this->atLeastOnce())
            ->method('hasProcessesThatCouldBeFreed')
            ->willReturnCallback(static function () use (&$callCount) {
                ++$callCount;

                return $callCount <= 1; // Return true once, then false
            });

        // fillBucketOnce should be called and return some time value
        $runner->expects($this->atLeastOnce())
            ->method('fillBucketOnce')
            ->willReturn(1000); // Return 1000 microseconds

        // wait should be called with the return value from fillBucketOnce
        $runner->expects($this->atLeastOnce())
            ->method('wait')
            ->with($this->identicalTo(1000)); // Must be called with the fillBucketOnce return value

        // Create processes
        $processes = [];

        for ($i = 0; $i < 2; ++$i) {
            $process = $this->createMock(Process::class);
            $process->expects($this->any())->method('start'); // May or may not be called depending on mock behavior
            $process->expects($this->any())->method('isRunning')->willReturn(false);

            $mutantProcess = new DummyMutantProcess(
                $process,
                $this->createMock(Mutant::class),
                $this->createMock(TestFrameworkMutantExecutionResultFactory::class),
                false,
            );

            $processes[] = new MutantProcessContainer($mutantProcess, []);
        }

        // Run the processes - wait should be called with the fillBucketOnce return value
        iterator_to_array($runner->run($processes));
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
}
