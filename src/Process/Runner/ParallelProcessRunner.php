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

namespace Infection\Process\Runner;

use function array_shift;
use function count;
use Generator;
use function max;
use function microtime;
use function range;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use function usleep;
use Webmozart\Assert\Assert;

/**
 * @internal
 *
 * This ProcessManager is an elaborate wrapper to enable parallel processing using Symfony Process component
 */
final class ParallelProcessRunner implements ProcessRunner
{
    private const POLL_WAIT_IN_MS = 1000;

    private const NANO_SECONDS_IN_MILLI_SECOND = 1_000_000;

    /**
     * @var array<int, IndexedProcessBearer>
     */
    private array $runningProcesses = [];
    /**
     * @var array<int, int>
     */
    private array $availableThreadIndexes = [];

    private bool $shouldStop = false;

    /**
     * @param int $poll Delay (in milliseconds) to wait in-between two polls
     */
    public function __construct(private readonly int $threadCount, private readonly int $poll = self::POLL_WAIT_IN_MS)
    {
    }

    public function stop(): void
    {
        $this->shouldStop = true;
    }

    public function run(iterable $processes): void
    {
        /*
         * It takes about 100000 ms for a mutated process to finish, where it takes
         * about 5000 ms to make it. Therefore instead of just waiting we can produce
         * new processes so that when a process or several finish, we would have
         * additional jobs on hand, without a need to wait for them to be created.
         *
         * For our purposes we need to make sure we only see one process only once. Thus,
         * we use a generator here which is both non-rewindable, and will fail loudly if tried.
         */
        $generator = self::toGenerator($processes);

        // Bucket for processes to be executed
        $bucket = [];

        // Load the first process from the queue to buy us some time.
        self::fillBucketOnce($bucket, $generator, 1);

        $threadCount = max(1, $this->threadCount);
        $this->availableThreadIndexes = range(1, $threadCount);

        // start the initial batch of processes
        while ($process = array_shift($bucket)) {
            if ($this->shouldStop) {
                break;
            }

            $threadIndex = array_shift($this->availableThreadIndexes);

            Assert::integer($threadIndex, 'Thread index can not be null.');

            $this->startProcess($process, $threadIndex);

            if (count($this->runningProcesses) >= $threadCount) {
                do {
                    // While we wait, try fetch a good amount of next processes from the queue,
                    // reducing the poll delay with each loaded process
                    usleep(max(0, $this->poll - self::fillBucketOnce($bucket, $generator, $threadCount)));
                } while (!$this->freeTerminatedProcesses());
            }

            // In any case try to load at least one process to the bucket
            self::fillBucketOnce($bucket, $generator, 1);
        }

        do {
            usleep($this->poll);
            $this->freeTerminatedProcesses();
            // continue loop while there are processes being executed or waiting for execution
        } while ($this->runningProcesses);
    }

    private function freeTerminatedProcesses(): bool
    {
        // remove any finished process from the stack
        foreach ($this->runningProcesses as $index => $indexedProcessBearer) {
            $processBearer = $indexedProcessBearer->processBearer;
            $process = $processBearer->getProcess();

            try {
                $process->checkTimeout();
            } catch (ProcessTimedOutException) {
                $processBearer->markAsTimedOut();
            }

            if (!$process->isRunning()) {
                $processBearer->terminateProcess();

                $this->availableThreadIndexes[] = $indexedProcessBearer->threadIndex;

                unset($this->runningProcesses[$index]->processBearer);
                unset($this->runningProcesses[$index]);

                return true;
            }
        }

        return false;
    }

    private function startProcess(ProcessBearer $processBearer, int $threadIndex): void
    {
        $processBearer->getProcess()->start(null, [
            'INFECTION' => '1',
            'TEST_TOKEN' => $threadIndex,
        ]);

        $this->runningProcesses[] = new IndexedProcessBearer($threadIndex, $processBearer);
    }

    /**
     * @param ProcessBearer[] $bucket
     * @param Generator<ProcessBearer> $input
     */
    private static function fillBucketOnce(array &$bucket, Generator $input, int $threadCount): int
    {
        if (count($bucket) >= $threadCount || !$input->valid()) {
            return 0;
        }

        $start = microtime(true);

        $bucket[] = $input->current();
        $input->next();

        return (int) (microtime(true) - $start) * self::NANO_SECONDS_IN_MILLI_SECOND; // ns to ms
    }

    /**
     * @param iterable<ProcessBearer> $input
     *
     * @return Generator<ProcessBearer>
     */
    private static function toGenerator(iterable &$input): Generator
    {
        yield from $input;
    }
}
