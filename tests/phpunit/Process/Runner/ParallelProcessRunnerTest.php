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
use Infection\Process\Runner\IndexedMutantProcessContainer;
use Infection\Process\Runner\ParallelProcessRunner;
use Infection\Tests\Fixtures\Process\DummyMutantProcess;
use InvalidArgumentException;
use Iterator;
use function iterator_count;
use function iterator_to_array;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use RuntimeException;
use SplQueue;
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

        $runner = new ParallelProcessRunner(4, 0);

        $executedProcesses = $runner->run($processes);

        $this->assertCount(10, iterator_to_array($executedProcesses, true));
    }

    #[DataProvider('threadCountProvider')]
    public function test_it_adds_next_processes_if_mutant_is_escaped(int $threadCount): void
    {
        $processes = (function () use ($threadCount): iterable {
            yield $this->createMutantProcessContainerWithNextMutantProcess($threadCount);
        })();

        $runner = new ParallelProcessRunner($threadCount, 0);

        $executedProcesses = $runner->run($processes);

        $this->assertCount(1, iterator_to_array($executedProcesses, true));
    }

    #[DataProvider('threadCountProvider')]
    public function test_it_handles_all_kids_of_processes_with_infinite_threads(int $threadCount): void
    {
        $this->runWithAllKindsOfProcesses($threadCount);
    }

    public function test_fill_bucket_once_with_exhausted_generator_does_not_continue(): void
    {
        $runner = new ParallelProcessRunner(1, 0);

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

    private function runWithAllKindsOfProcesses(int $threadCount): void
    {
        $processes = (function () use ($threadCount): iterable {
            for ($i = 0; $i < 5; ++$i) {
                $threadIndex = $threadCount === 0 ? 1 : ($i * 2 % $threadCount) + 1;

                yield $this->createMutantProcessContainer($threadIndex);

                yield $this->createTimeOutMutantProcessContainer();
            }
        })();

        $runner = new ParallelProcessRunner($threadCount, 0);

        $executedProcesses = $runner->run($processes);

        $this->assertCount(10, iterator_to_array($executedProcesses, true));
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
