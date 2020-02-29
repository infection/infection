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

use Infection\Event\EventDispatcher\EventDispatcher;
use Infection\Event\MutantProcessWasFinished;
use Infection\Mutant\Mutant;
use Infection\Mutation\Mutation;
use Infection\Mutator\ZeroIteration\For_;
use Infection\Process\MutantProcess;
use Infection\Process\Runner\Parallel\ParallelProcessRunner;
use Infection\Tests\Mutator\MutatorName;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;

final class ParallelProcessRunnerTest extends TestCase
{
    public function test_it_does_nothing_when_nothing_to_do(): void
    {
        $eventDispatcher = $this->buildEventDispatcherWithEventCount(0);
        $runner = new ParallelProcessRunner($eventDispatcher);
        $runner->run([], 4, 0);
    }

    public function test_it_does_not_start_processes_for_uncovered_mutants(): void
    {
        $processes = [];

        for ($i = 0; $i < 10; ++$i) {
            $processes[] = $this->buildUncoveredMutantProcess();
        }

        $eventDispatcher = $this->buildEventDispatcherWithEventCount(10);
        $runner = new ParallelProcessRunner($eventDispatcher);
        $runner->run($processes, 4, 0);
    }

    public function test_it_starts_processes_for_covered_mutants(): void
    {
        $processes = [];

        for ($i = 0; $i < 10; ++$i) {
            $processes[] = $this->buildCoveredMutantProcess();
        }

        $eventDispatcher = $this->buildEventDispatcherWithEventCount(10);
        $runner = new ParallelProcessRunner($eventDispatcher);
        $runner->run($processes, 4, 0);
    }

    public function test_it_checks_for_timeout(): void
    {
        $processes = [];

        for ($i = 0; $i < 10; ++$i) {
            $processes[] = $this->buildCoveredMutantProcessWithTimeout();
        }

        $eventDispatcher = $this->buildEventDispatcherWithEventCount(10);
        $runner = new ParallelProcessRunner($eventDispatcher);
        $runner->run($processes, 4, 0);
    }

    public function test_it_handles_all_kids_of_processes_with_infinite_threads(): void
    {
        $this->runWithAllKindsOfProcesses(PHP_INT_MAX);
    }

    public function test_it_handles_all_kids_of_processes(): void
    {
        $this->runWithAllKindsOfProcesses(4);
    }

    public function test_it_handles_all_kids_of_processes_in_one_thread(): void
    {
        $this->runWithAllKindsOfProcesses(1);
    }

    public function test_it_still_runs_with_zero_threads(): void
    {
        $this->runWithAllKindsOfProcesses(0);
    }

    public function test_it_still_runs_with_negative_thread_count(): void
    {
        $this->runWithAllKindsOfProcesses(-1);
    }

    private function buildEventDispatcherWithEventCount(int $eventCount)
    {
        $eventDispatcher = $this->createMock(EventDispatcher::class);
        $eventDispatcher
            ->expects($this->exactly($eventCount))
            ->method('dispatch')
            ->with($this->isInstanceOf(MutantProcessWasFinished::class))
        ;

        return $eventDispatcher;
    }

    private function buildUncoveredMutantProcess(): MutantProcess
    {
        $processMock = $this->createMock(Process::class);
        $processMock
            ->method('getCommandLine')
            ->willReturn('bin/phpunit path/to/FooTest.php')
        ;
        $processMock
            ->method('getOutput')
            ->willReturn('')
        ;

        $mutationMock = $this->createMock(Mutation::class);
        $mutationMock
            ->method('getMutatorName')
            ->willReturn(MutatorName::getName(For_::class))
        ;

        $mutantMock = $this->createMock(Mutant::class);
        $mutantMock
            ->expects($this->once())
            ->method('isCoveredByTest')
            ->willReturn(false)
        ;
        $mutantMock
            ->method('getMutation')
            ->willReturn($mutationMock)
        ;

        $mutantProcessMock = $this->createMock(MutantProcess::class);
        $mutantProcessMock
            ->expects($this->atLeastOnce())
            ->method('getProcess')
            ->willReturn($processMock)
        ;
        $mutantProcessMock
            ->expects($this->atLeastOnce())
            ->method('getMutant')
            ->willReturn($mutantMock)
        ;

        return $mutantProcessMock;
    }

    private function buildCoveredMutantProcess(): MutantProcess
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
        $processMock
            ->method('getCommandLine')
            ->willReturn('bin/phpunit path/to/FooTest.php')
        ;
        $processMock
            ->method('getOutput')
            ->willReturn('Failed!')
        ;

        $mutationMock = $this->createMock(Mutation::class);
        $mutationMock
            ->method('getMutatorName')
            ->willReturn(MutatorName::getName(For_::class))
        ;

        $mutantMock = $this->createMock(Mutant::class);
        $mutantMock
            ->expects($this->once())
            ->method('isCoveredByTest')
            ->willReturn(true)
        ;
        $mutantMock
            ->method('getMutation')
            ->willReturn($mutationMock)
        ;

        $mutantProcessMock = $this->createMock(MutantProcess::class);
        $mutantProcessMock
            ->expects($this->atLeastOnce())
            ->method('getProcess')
            ->willReturn($processMock)
        ;
        $mutantProcessMock
            ->expects($this->atLeastOnce())
            ->method('getMutant')
            ->willReturn($mutantMock)
        ;

        return $mutantProcessMock;
    }

    private function buildCoveredMutantProcessWithTimeout(): MutantProcess
    {
        $processMock = $this->createMock(Process::class);
        $processMock
            ->expects($this->once())
            ->method('start')
        ;
        $processMock
            ->expects($this->once())
            ->method('checkTimeout')
            ->will($this->throwException(new ProcessTimedOutException($processMock, 1)))
        ;
        $processMock
            ->expects($this->once())
            ->method('isRunning')
            ->willReturn(false)
        ;
        $processMock
            ->method('getCommandLine')
            ->willReturn('bin/phpunit path/to/FooTest.php')
        ;
        $processMock
            ->method('getOutput')
            ->willReturn('Terminated')
        ;

        $mutationMock = $this->createMock(Mutation::class);
        $mutationMock
            ->method('getMutatorName')
            ->willReturn(MutatorName::getName(For_::class))
        ;

        $mutantMock = $this->createMock(Mutant::class);
        $mutantMock
            ->expects($this->once())
            ->method('isCoveredByTest')
            ->willReturn(true)
        ;
        $mutantMock
            ->method('getMutation')
            ->willReturn($mutationMock)
        ;

        $mutantProcessMock = $this->createMock(MutantProcess::class);
        $mutantProcessMock
            ->expects($this->atLeastOnce())
            ->method('getProcess')
            ->willReturn($processMock);
        $mutantProcessMock
            ->expects($this->atLeastOnce())
            ->method('getMutant')
            ->willReturn($mutantMock)
        ;
        $mutantProcessMock
            ->expects($this->once())
            ->method('markTimeout')
        ;

        return $mutantProcessMock;
    }

    private function runWithAllKindsOfProcesses(int $threadCount): void
    {
        $processes = [];

        for ($i = 0; $i < 4; ++$i) {
            $processes[] = $this->buildUncoveredMutantProcess();
            $processes[] = $this->buildCoveredMutantProcess();
            $processes[] = $this->buildCoveredMutantProcessWithTimeout();
        }

        $eventDispatcher = $this->buildEventDispatcherWithEventCount(12);
        $runner = new ParallelProcessRunner($eventDispatcher);
        $runner->run($processes, $threadCount, 0);
    }
}
