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
use Symfony\Component\Process\Exception\LogicException;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Exception\RuntimeException;

/**
 * This ProcessManager is a simple wrapper to enable parallel processing using Symfony Process component.
 */
class ParallelProcessRunner
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param MutantProcess[] $processes
     * @param int $threadCount
     * @param int $poll
     *
     * @throws RuntimeException
     * @throws LogicException
     */
    public function run(array $processes, int $threadCount, int $poll = 1000)
    {
        $threadCount = $threadCount <= 0 ? 1 : $threadCount;
        // do not modify the object pointers in the argument, copy to local working variable
        $processesQueue = $processes;

        // fix maxParallel to be max the number of processes or positive
        $maxParallel = min(abs($threadCount), count($processesQueue));

        // get the first stack of processes to start at the same time
        /** @var MutantProcess[] $currentProcesses */
        $currentProcesses = array_splice($processesQueue, 0, $maxParallel);

        // start the initial stack of processes
        foreach ($currentProcesses as $process) {
            $process->getProcess()->start();
        }

        do {
            usleep($poll);

            // remove all finished processes from the stack
            foreach ($currentProcesses as $index => $mutantProcess) {
                $process = $mutantProcess->getProcess();

                try {
                    $process->checkTimeout();
                } catch (ProcessTimedOutException $e) {
                    $mutantProcess->markTimeout();
                }

                if (!$process->isRunning()) {
                    $this->eventDispatcher->dispatch(new MutantProcessFinished($mutantProcess));

                    unset($currentProcesses[$index]);

                    // directly add and start new process after the previous finished
                    if (count($processesQueue) > 0) {
                        $nextProcessFound = false;

                        do {
                            $nextProcess = array_shift($processesQueue);
                            $mutant = $nextProcess->getMutant();

                            if ($mutant->isCoveredByTest()) {
                                $nextProcess->getProcess()->start();
                                $nextProcessFound = true;
                                $currentProcesses[] = $nextProcess;
                            } else {
                                $this->eventDispatcher->dispatch(new MutantProcessFinished($nextProcess));
                            }
                        } while (!$nextProcessFound && count($processesQueue) > 0);
                    }
                }
            }
            // continue loop while there are processes being executed or waiting for execution
        } while (count($processesQueue) > 0 || count($currentProcesses) > 0);
    }
}
