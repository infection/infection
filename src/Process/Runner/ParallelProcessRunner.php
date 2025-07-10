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

use function count;
use Infection\Process\RunnableProcess;
use Iterator;
use function max;
use SplQueue;
use Tumblr\Chorus\TimeKeeper;
use Webmozart\Assert\Assert;

/**
 * @internal
 *
 * This ParallelProcessRunner is an elaborate wrapper to enable parallel processing using Symfony Process component
 * @template T of RunnableProcess
 */
final class ParallelProcessRunner implements ProcessRunner
{
    private const POLL_WAIT_IN_MS = 1000;

    private const NANO_SECONDS_IN_MILLI_SECOND = 1_000_000;

    /**
     * @var array<int, T>
     */
    private array $runningProcesses = [];

    private bool $shouldStop = false;

    /**
     * @var SplQueue<T>
     */
    private SplQueue $bucket;

    /**
     * @param positive-int $threadCount
     * @param Iterator<T> $input
     * @param int $poll Delay (in milliseconds) to wait in-between two polls
     */
    public function __construct(
        private readonly int $threadCount,
        private readonly Iterator $input,
        private readonly int $poll = self::POLL_WAIT_IN_MS,
        private readonly TimeKeeper $timeKeeper = new TimeKeeper(),
    ) {
        $this->bucket = new SplQueue();
    }

    public function stop(): void
    {
        $this->shouldStop = true;
    }

    /**
     * It takes about 100000 ms for a mutated process to finish, where it takes
     * about 5000 ms to make it. Therefore instead of just waiting we can produce
     * new processes so that when a process or several finish, we would have
     * additional jobs on hand, without a need to wait for them to be created.
     */
    public function run(): iterable
    {
        // Load the first process from the queue to buy us some time.
        $this->fillBucketOnce();

        // start the initial batch of processes
        while ($this->hasMoreWorkQueued() || $this->hasRunningProcesses()) {
            if ($this->shouldStop) {
                break;
            }

            if ($this->hasMoreWorkQueued() && $this->hasThreadsAvailable()) {
                $this->startProcess();
                continue;
            }

            // At this point, we are running as many processes as we should
            // I.e. count($this->runningProcesses) >= $this->threadCount
            // We can now start to remove finished processes
            yield from $this->tryToFreeNotRunningProcess();

            // While we wait, try fetch a good amount of next processes from the queue,
            // Assuming it takes less time to generate a mutant than finish one process
            $loadTime = $this->fillBucketUpTo(desiredDepth: $this->threadCount);

            // ...and reducing the poll delay with each loaded process
            $this->poll(correction: $loadTime);
        }

        // We finished all queued work, let's just wait for all process to finish
        do {
            $this->poll();

            // yield back so that we can work on process result it afterwords
            yield from $this->tryToFreeNotRunningProcess();
        } while ($this->hasRunningProcesses());
    }

    /**
     * @param int $correction The amount of time spent doing other work, that needs to be substracted from the poll delay
     */
    private function poll(int $correction = 0): void
    {
        $this->timeKeeper->usleep(
            max(0, $this->poll - $correction),
        );
    }

    /**
     * @return iterable<RunnableProcess>
     */
    private function tryToFreeNotRunningProcess(): iterable
    {
        // remove any finished process from the stack, and pass them along
        foreach ($this->runningProcesses as $index => $mutantProcess) {
            if ($mutantProcess->isRunning()) {
                continue;
            }

            unset($this->runningProcesses[$index]);

            yield $mutantProcess;
        }
    }

    private function hasMoreWorkQueued(): bool
    {
        return !$this->bucket->isEmpty();
    }

    private function hasRunningProcesses(): bool
    {
        return $this->runningProcesses !== [];
    }

    private function hasThreadsAvailable(): bool
    {
        return count($this->runningProcesses) < $this->threadCount;
    }

    private function startProcess(): void
    {
        $mutantProcess = $this->bucket->dequeue();
        $mutantProcess->startProcess();

        $this->runningProcesses[] = $mutantProcess;
    }

    /**
     * @param positive-int $desiredDepth
     * @return non-negative-int The time it took to create a new process
     */
    private function fillBucketUpTo(int $desiredDepth): int
    {
        if ($this->bucket->count() >= $desiredDepth || !$this->input->valid()) {
            return 0;
        }

        $start = $this->timeKeeper->getCurrentTimeAsFloat();

        while ($this->bucket->count() < $desiredDepth && $this->input->valid()) {
            $this->bucket->enqueue($this->input->current());
            $this->input->next();
        }

        return (int) (($this->timeKeeper->getCurrentTimeAsFloat() - $start) * self::NANO_SECONDS_IN_MILLI_SECOND);
    }

    private function fillBucketOnce(): void
    {
        $this->fillBucketUpTo(1);
    }
}
