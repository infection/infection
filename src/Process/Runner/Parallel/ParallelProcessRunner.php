<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Process\Runner\Parallel;

use Infection\EventDispatcher\EventDispatcherInterface;
use Infection\Events\MutantProcessFinished;
use Infection\Process\MutantProcess;
use Infection\Process\MutantProcessInterface;
use Symfony\Component\Process\Exception\LogicException;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Exception\RuntimeException;

/**
 * @internal
 *
 * This ProcessManager is a simple wrapper to enable parallel processing using Symfony Process component
 */
final class ParallelProcessRunner
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var MutantProcessInterface[]
     */
    private $processesQueue;

    /**
     * @var MutantProcessInterface[]
     */
    private $currentProcesses = [];

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param MutantProcessInterface[] $processes
     * @param int $threadCount
     * @param int $poll
     *
     * @throws RuntimeException
     * @throws LogicException
     */
    public function run(array $processes, int $threadCount, int $poll = 1000): void
    {
        if (!$this->processesQueue = $processes) {
            // nothing to do here
            return;
        }

        // fix maxParallel to be max the number of processes or positive
        $maxParallel = min(max($threadCount, 1), \count($this->processesQueue));

        // start the initial batch of processes
        do {
            $this->startProcess();
        } while ($this->processesQueue && \count($this->currentProcesses) < $maxParallel);

        do {
            usleep($poll);

            // remove all finished processes from the stack
            foreach ($this->currentProcesses as $index => $mutantProcess) {
                /** @var MutantProcess $mutantProcess */
                $process = $mutantProcess->getProcess();

                try {
                    $process->checkTimeout();
                } catch (ProcessTimedOutException $e) {
                    $mutantProcess->markTimeout();
                }

                if (!$process->isRunning()) {
                    $this->eventDispatcher->dispatch(new MutantProcessFinished($mutantProcess));

                    unset($this->currentProcesses[$index]);

                    // directly add and start a new process after the previous finished
                    while ($this->processesQueue) {
                        if ($this->startProcess()) {
                            break;
                        }
                    }
                }
            }
            // continue loop while there are processes being executed or waiting for execution
        } while ($this->processesQueue || $this->currentProcesses);
    }

    private function startProcess(): bool
    {
        $mutantProcess = array_shift($this->processesQueue);
        \assert($mutantProcess instanceof MutantProcessInterface);

        $mutant = $mutantProcess->getMutant();

        if (!$mutant->isCoveredByTest()) {
            $this->eventDispatcher->dispatch(new MutantProcessFinished($mutantProcess));

            return false;
        }

        $mutantProcess->getProcess()->start();

        $this->currentProcesses[] = $mutantProcess;

        return true;
    }
}
