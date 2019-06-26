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

use Infection\EventDispatcher\EventDispatcherInterface;
use Infection\Events\MutantProcessFinished;
use Infection\Mutant\MutantInterface;
use Infection\Process\MutantProcessInterface;
use Infection\Process\Runner\Parallel\ParallelProcessRunner;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;

/**
 * @internal
 */
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

    private function buildEventDispatcherWithEventCount($eventCount): EventDispatcherInterface
    {
        /** @var MockObject|EventDispatcherInterface $eventDispatcher */
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects($this->exactly($eventCount))
            ->method('dispatch')
            ->with(new MutantProcessFinished($this->createMock(MutantProcessInterface::class)));

        return $eventDispatcher;
    }

    private function buildUncoveredMutantProcess(): MutantProcessInterface
    {
        $mutant = $this->createMock(MutantInterface::class);
        $mutant->expects($this->once())
            ->method('isCoveredByTest')
            ->willReturn(false);

        /** @var MockObject|MutantProcessInterface $mutantProcess */
        $mutantProcess = $this->createMock(MutantProcessInterface::class);
        $mutantProcess->expects($this->once())
            ->method('getMutant')
            ->willReturn($mutant);

        return $mutantProcess;
    }

    private function buildCoveredMutantProcess(): MutantProcessInterface
    {
        $process = $this->createMock(Process::class);
        $process->expects($this->once())
            ->method('start');
        $process->expects($this->once())
            ->method('checkTimeout');
        $process->expects($this->once())
            ->method('isRunning')
            ->willReturn(false);

        $mutant = $this->createMock(MutantInterface::class);
        $mutant->expects($this->once())
            ->method('isCoveredByTest')
            ->willReturn(true);

        /** @var MockObject|MutantProcessInterface $mutantProcess */
        $mutantProcess = $this->createMock(MutantProcessInterface::class);
        $mutantProcess->expects($this->exactly(2))
            ->method('getProcess')
            ->willReturn($process);
        $mutantProcess->expects($this->once())
            ->method('getMutant')
            ->willReturn($mutant);

        return $mutantProcess;
    }

    private function buildCoveredMutantProcessWithTimeout(): MutantProcessInterface
    {
        $process = $this->createMock(Process::class);
        $process->expects($this->once())
            ->method('start');
        $process->expects($this->once())
            ->method('checkTimeout')
            ->will($this->throwException(new ProcessTimedOutException($process, 1)));
        $process->expects($this->once())
            ->method('isRunning')
            ->willReturn(false);

        $mutant = $this->createMock(MutantInterface::class);
        $mutant->expects($this->once())
            ->method('isCoveredByTest')
            ->willReturn(true);

        /** @var MockObject|MutantProcessInterface $mutantProcess */
        $mutantProcess = $this->createMock(MutantProcessInterface::class);
        $mutantProcess->expects($this->exactly(2))
            ->method('getProcess')
            ->willReturn($process);
        $mutantProcess->expects($this->once())
            ->method('getMutant')
            ->willReturn($mutant);
        $mutantProcess->expects($this->once())
            ->method('markTimeout');

        return $mutantProcess;
    }

    private function runWithAllKindsOfProcesses($threadCount): void
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
