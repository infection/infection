<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Process\Runner\Parallel;

use Infection\EventDispatcher\EventDispatcherInterface;
use Infection\Events\MutantProcessFinished;
use Infection\Mutant\MutantInterface;
use Infection\Process\MutantProcessInterface;
use Infection\Process\Runner\Parallel\ParallelProcessRunner;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;

/**
 * @internal
 */
final class ParallelProcessRunnerTest extends MockeryTestCase
{
    private function buildEventDispatcherWithEventCount($eventCount): EventDispatcherInterface
    {
        $eventDispatcher = Mockery::mock(EventDispatcherInterface::class);
        $eventDispatcher->shouldReceive('dispatch')->times($eventCount)->with(Mockery::type(MutantProcessFinished::class));

        return $eventDispatcher;
    }

    public function test_it_does_nothing_when_nothing_to_do(): void
    {
        $eventDispatcher = $this->buildEventDispatcherWithEventCount(0);
        $runner = new ParallelProcessRunner($eventDispatcher);
        $runner->run([], 4, 0);
    }

    private function buildUncoveredMutantProcess(): MutantProcessInterface
    {
        $mutant = Mockery::mock(MutantInterface::class);
        $mutant->shouldReceive('isCoveredByTest')->once()->andReturn(false);

        $mutantProcess = Mockery::mock(MutantProcessInterface::class);
        $mutantProcess->shouldReceive('getMutant')->once()->andReturn($mutant);

        return $mutantProcess;
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

    private function buildCoveredMutantProcess(): MutantProcessInterface
    {
        $process = Mockery::mock(Process::class);
        $process->shouldReceive('start')->once();
        $process->shouldReceive('checkTimeout')->once();
        $process->shouldReceive('isRunning')->once()->andReturn(false);

        $mutant = Mockery::mock(MutantInterface::class);
        $mutant->shouldReceive('isCoveredByTest')->once()->andReturn(true);

        $mutantProcess = Mockery::mock(MutantProcessInterface::class);
        $mutantProcess->shouldReceive('getProcess')->twice()->andReturn($process);
        $mutantProcess->shouldReceive('getMutant')->once()->andReturn($mutant);

        return $mutantProcess;
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

    private function buildCoveredMutantProcessWithTimeout(): MutantProcessInterface
    {
        $process = Mockery::mock(Process::class);
        $process->shouldReceive('start')->once();
        $process->shouldReceive('checkTimeout')->once()->andThrow(Mockery::mock(ProcessTimedOutException::class));
        $process->shouldReceive('isRunning')->once()->andReturn(false);

        $mutant = Mockery::mock(MutantInterface::class);
        $mutant->shouldReceive('isCoveredByTest')->once()->andReturn(true);

        $mutantProcess = Mockery::mock(MutantProcessInterface::class);
        $mutantProcess->shouldReceive('getProcess')->twice()->andReturn($process);
        $mutantProcess->shouldReceive('getMutant')->once()->andReturn($mutant);
        $mutantProcess->shouldReceive('markTimeout')->once();

        return $mutantProcess;
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
}
