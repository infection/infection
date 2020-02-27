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

use Closure;
use function count;
use function max;
use Symfony\Component\Process\Exception\LogicException;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Exception\RuntimeException;
use function usleep;

/**
 * @internal
 * @final
 *
 * This ProcessManager is a simple wrapper to enable parallel processing using Symfony Process component
 */
class ParallelProcessRunner
{
    private $processHandler;

    /**
     * @var ProcessBearer[]
     */
    private $runningProcesses = [];

    public function __construct(Closure $processHandler)
    {
        $this->processHandler = $processHandler;
    }

    /**
     * @param ProcessBearer[] $processes
     *
     * @throws RuntimeException
     * @throws LogicException
     */
    public function run(iterable $processes, int $threadCount, int $poll = 100): void
    {
        $threadCount = max(1, $threadCount);

        // Start the initial batch of processes
        foreach ($processes as $process) {
            $this->startProcess($process);

            if (count($this->runningProcesses) >= $threadCount) {
                do {
                    usleep($poll);
                } while (!$this->freeTerminatedProcesses());
            }
        }

        do {
            usleep($poll);

            $this->freeTerminatedProcesses();
            // Continue loop while there are processes being executed or waiting for execution
        } while (count($this->runningProcesses) !== 0);
    }

    private function startProcess(ProcessBearer $processBearer): void
    {
        $processBearer->getProcess()->start();

        $this->runningProcesses[] = $processBearer;
    }

    private function freeTerminatedProcesses(): bool
    {
        // Remove any finished process from the stack
        foreach ($this->runningProcesses as $index => $processBearer) {
            $process = $processBearer->getProcess();

            try {
                $process->checkTimeout();
            } catch (ProcessTimedOutException $exception) {
                $processBearer->markTimeout();
            }

            if (!$process->isRunning()) {
                ($this->processHandler)($processBearer);

                unset($this->runningProcesses[$index]);

                return true;
            }
        }

        return false;
    }
}
