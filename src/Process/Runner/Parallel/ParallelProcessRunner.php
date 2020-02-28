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

use function array_shift;
use Closure;
use function count;
use Generator;
use Symfony\Component\Process\Exception\LogicException;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Exception\RuntimeException;
use function usleep;

/**
 * @internal
 * @final
 *
 * This ProcessManager is an elaborate wrapper to enable parallel processing using Symfony Process component
 */
class ParallelProcessRunner
{
    /**
     * If it takes 100000 ms for a process to finish, and 5000 ms to make it,
     * then we can make as much as 20 processes while we wait. But let's not
     * get greedy and settle on a smaller number to make sure we're not stuck
     * making processes where we should be starting them.
     */
    private const MUTATOR_TO_PROCESS_RATIO = 10;

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
    public function run(iterable $processes, int $threadCount, int $poll = 1000): void
    {
        /*
         * It takes about 100000 ms for a mutated process to finish, where it takes
         * about 5000 ms to make it. Therefore instead of just waiting we can produce
         * new processes so that when a process or several finish, we would have
         * additional jobs on hand, without a need to wait for them to be created.
         *
         * For our purposes we need to make sure we only see one process only once.
         */
        $generator = self::toGenerator($processes);

        // Bucket for processes to be executed
        $bucket = [];

        // Load the first process from the queue to buy us some time.
        self::fillBucket($bucket, $generator, 1);

        $threadCount = max(1, $threadCount);

        // start the initial batch of processes
        while ($process = array_shift($bucket)) {
            $this->startProcess($process);

            if (count($this->runningProcesses) >= $threadCount) {
                do {
                    // Now fill the bucket up to the top
                    self::fillBucket($bucket, $generator);
                    usleep($poll);
                } while (!$this->freeTerminatedProcesses());
            }

            // In any case load a least one process to the bucket
            self::fillBucket($bucket, $generator, 1);
        }

        do {
            usleep($poll);
            $this->freeTerminatedProcesses();
            // continue loop while there are processes being executed or waiting for execution
        } while ($this->runningProcesses);
    }

    private function freeTerminatedProcesses(): bool
    {
        // remove any finished process from the stack
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

    private function startProcess(ProcessBearer $processBearer): void
    {
        $processBearer->getProcess()->start();

        $this->runningProcesses[] = $processBearer;
    }

    /**
     * @param ProcessBearer[] $bucket
     * @param Generator|ProcessBearer[] $input
     */
    private static function fillBucket(array &$bucket, Generator $input, int $level = self::MUTATOR_TO_PROCESS_RATIO): void
    {
        if (count($bucket) === $level) {
            return;
        }

        while ($input->valid() && count($bucket) < $level) {
            $bucket[] = $input->current();
            $input->next();
        }
    }

    /**
     * @param iterable|ProcessBearer[] $input
     *
     * @return Generator|ProcessBearer[]
     */
    private static function toGenerator(iterable &$input): Generator
    {
        yield from $input;
    }
}
