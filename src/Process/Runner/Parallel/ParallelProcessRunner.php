<?php

declare(strict_types=1);

namespace Infection\Process\Runner\Parallel;

use Infection\EventDispatcher\EventDispatcherInterface;
use Infection\Events\MutantProcessFinished;
use Infection\Process\MutantProcess;
use Symfony\Component\Process\Exception\LogicException;
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
    public function runParallel(array $processes, int $threadCount, int $poll = 1000)
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
            foreach ($currentProcesses as $index => $process) {
                if (!$process->getProcess()->isRunning()) {
                    $this->eventDispatcher->dispatch(new MutantProcessFinished($process));

                    unset($currentProcesses[$index]);

                    // directly add and start new process after the previous finished
                    if (count($processesQueue) > 0) {
                        $nextProcess = array_shift($processesQueue);
                        $nextProcess->getProcess()->start();
                        $currentProcesses[] = $nextProcess;
                    }
                }
            }
            // continue loop while there are processes being executed or waiting for execution
        } while (count($processesQueue) > 0 || count($currentProcesses) > 0);
    }
}