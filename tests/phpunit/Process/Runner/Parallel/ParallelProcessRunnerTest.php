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

namespace Infection\Tests\Process\Runner\Parallel;

use Closure;
use Infection\Event\EventDispatcher\EventDispatcher;
use Infection\Event\MutantProcessWasFinished;
use Infection\Mutant\MutantExecutionResult;
use Infection\Process\Runner\Parallel\ParallelProcessRunner;
use Infection\Process\Runner\Parallel\ProcessBearer;
use Infection\Tests\Fixtures\Process\DummyProcessBearer;
use const PHP_INT_MAX;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;

final class ParallelProcessRunnerTest extends TestCase
{
    public function test_it_does_nothing_when_no_process_is_given(): void
    {
        $eventDispatcher = $this->createEventDispatcherWithEventCount(0);

        $runner = new ParallelProcessRunner(
            $this->createProcessHandler($eventDispatcher),
            4,
            0
        );

        $runner->run([]);
    }

    public function test_it_starts_the_given_processes(): void
    {
        $processes = (function (): iterable {
            for ($i = 0; $i < 10; ++$i) {
                yield $this->createProcessBearer();
            }
        })();

        $eventDispatcher = $this->createEventDispatcherWithEventCount(10);

        $runner = new ParallelProcessRunner(
            $this->createProcessHandler($eventDispatcher),
            4,
            0
        );

        $runner->run($processes);
    }

    public function test_it_checks_if_the_executed_processes_time_out(): void
    {
        $processes = (function (): iterable {
            for ($i = 0; $i < 10; ++$i) {
                yield $this->createTimeOutProcessBearer();
            }
        })();

        $eventDispatcher = $this->createEventDispatcherWithEventCount(10);

        $runner = new ParallelProcessRunner(
            $this->createProcessHandler($eventDispatcher),
            4,
            0
        );

        $runner->run($processes);
    }

    /**
     * @dataProvider threadCountProvider
     */
    public function test_it_handles_all_kids_of_processes_with_infinite_threads(int $threadCount): void
    {
        $this->runWithAllKindsOfProcesses($threadCount);
    }

    public function threadCountProvider(): iterable
    {
        yield 'no threads' => [0];

        yield 'one thread' => [1];

        yield 'invalid thread' => [-1];

        yield 'nominal' => [4];

        yield 'infinite' => [PHP_INT_MAX];
    }

    /**
     * @return EventDispatcher|MockObject
     */
    private function createEventDispatcherWithEventCount(int $eventCount): EventDispatcher
    {
        $eventDispatcherMock = $this->createMock(EventDispatcher::class);
        $eventDispatcherMock
            ->expects($this->exactly($eventCount))
            ->method('dispatch')
            ->with(new MutantProcessWasFinished(
                $this->createMock(MutantExecutionResult::class))
            )
        ;

        return $eventDispatcherMock;
    }

    private function createProcessBearer(): ProcessBearer
    {
        $processMock = $this->createMock(Process::class);
        $processMock
            ->expects($this->once())
            ->method('start')
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

        return new DummyProcessBearer($processMock, false);
    }

    private function createTimeOutProcessBearer(): ProcessBearer
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

        return new DummyProcessBearer($processMock, true);
    }

    private function runWithAllKindsOfProcesses(int $threadCount): void
    {
        $processes = (function (): iterable {
            for ($i = 0; $i < 5; ++$i) {
                yield $this->createProcessBearer();

                yield $this->createTimeOutProcessBearer();
            }
        })();

        $eventDispatcher = $this->createEventDispatcherWithEventCount(10);

        $runner = new ParallelProcessRunner(
            $this->createProcessHandler($eventDispatcher),
            $threadCount,
            0
        );

        $runner->run($processes);
    }

    private function createProcessHandler(EventDispatcher $eventDispatcher): Closure
    {
        return function (ProcessBearer $processBearer) use ($eventDispatcher): void {
            $eventDispatcher->dispatch(new MutantProcessWasFinished(
                // We're not testing MutantExecutionResult::createFromProcess() here
                $this->createMock(MutantExecutionResult::class)
            ));
        };
    }
}
