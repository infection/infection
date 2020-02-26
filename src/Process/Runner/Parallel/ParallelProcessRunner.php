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

namespace Infection\Process\Runner\Parallel;

use function count;
use Infection\Event\EventDispatcher\EventDispatcher;
use Infection\Event\MutantProcessWasFinished;
use Infection\Process\MutantProcess;
use Symfony\Component\Process\Exception\LogicException;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Exception\RuntimeException;

/**
 * @internal
 * @final
 *
 * This ProcessManager is a simple wrapper to enable parallel processing using Symfony Process component
 */
class ParallelProcessRunner
{
    private $eventDispatcher;

    /**
     * @var MutantProcess[]
     */
    private $processesQueue;

    /**
     * @var MutantProcess[]
     */
    private $currentProcesses = [];

    public function __construct(EventDispatcher $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param MutantProcess[] $processes
     *
     * @throws RuntimeException
     * @throws LogicException
     */
    public function run(iterable $processes, int $threadCount, int $poll = 1000): void
    {
        $threadCount = max(1, $threadCount);

        // start the initial batch of processes
        foreach ($processes as $process) {
            $this->startProcess($process);

            if (count($this->currentProcesses) >= $threadCount) {
                do {
                    usleep($poll);
                } while (!$this->cleanFinished());
            }
        }

        do {
            usleep($poll);
            $this->cleanFinished();
            // continue loop while there are processes being executed or waiting for execution
        } while ($this->currentProcesses);
    }

    private function cleanFinished(): bool
    {
        // remove any finished process from the stack
        foreach ($this->currentProcesses as $index => $mutantProcess) {
            /** @var MutantProcess $mutantProcess */
            $process = $mutantProcess->getProcess();

            try {
                $process->checkTimeout();
            } catch (ProcessTimedOutException $e) {
                $mutantProcess->markTimeout();
            }

            if (!$process->isRunning()) {
                $this->eventDispatcher->dispatch(new MutantProcessWasFinished($mutantProcess));

                unset($this->currentProcesses[$index]);

                return true;
            }
        }

        return false;
    }

    private function startProcess(MutantProcess $mutantProcess): bool
    {
        $mutant = $mutantProcess->getMutant();

        if (!$mutant->isCoveredByTest()) {
            $this->eventDispatcher->dispatch(new MutantProcessWasFinished($mutantProcess));

            return false;
        }

        $mutantProcess->getProcess()->start();

        $this->currentProcesses[] = $mutantProcess;

        return true;
    }
}
