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
use Composer\XdebugHandler\Process;
use function count;
use DuoClock\DuoClock;
use Generator;
use Infection\Process\MutantProcessContainer;
use Iterator;
use function max;
use function range;
use SplQueue;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Webmozart\Assert\Assert;

/**
 * @internal
 *
 * This ProcessManager is an elaborate wrapper to enable parallel processing using Symfony Process component
 * @final
 */
class ParallelProcessRunner implements ProcessRunner
{
    private const POLL_WAIT_IN_MS = 1000;

    private const NANO_SECONDS_IN_MILLI_SECOND = 1_000_000;

    /**
     * @var array<int, IndexedMutantProcessContainer>
     */
    private array $runningProcessContainers = [];

    /**
     * @var array<int, int>
     */
    private array $availableThreadIndexes = [];

    private bool $shouldStop = false;

    /**
     * @param int $poll Delay (in milliseconds) to wait in-between two polls
     */
    public function __construct(
        private readonly int $threadCount,
        private readonly int $poll = self::POLL_WAIT_IN_MS,
        private readonly DuoClock $clock = new DuoClock(),
    ) {
    }

    public function stop(): void
    {
        $this->shouldStop = true;
    }

    /**
     * @param iterable<MutantProcessContainer> $processContainers
     * @return iterable<MutantProcessContainer>
     */
    public function run(iterable $processContainers): iterable
    {
        /*
         * It takes about 100000 ms for a mutated process to finish, where it takes
         * about 5000 ms to make it. Therefore instead of just waiting we can produce
         * new processes so that when a process or several finish, we would have
         * additional jobs on hand, without a need to wait for them to be created.
         *
         * For our purposes we need to make sure we only see one process only once. Thus,
         * we use a generator here which is both non-rewindable, and will fail loudly if tried.
         * That said, we permit iterators for testing purposes, as they can be mocked.
         */
        $generator = $processContainers instanceof Iterator
            ? $processContainers
            : self::toGenerator($processContainers);

        // Bucket for processes to be executed
        /** @var SplQueue<MutantProcessContainer> $bucket */
        $bucket = new SplQueue();

        // Load the first process from the queue to buy us some time.
        $this->fillBucketOnce($bucket, $generator, 1);

        $threadCount = max(1, $this->threadCount);
        $this->availableThreadIndexes = range(1, $threadCount);

        // start the initial batch of processes
        do {
            if ($this->shouldStop) {
                break;
            }

            if (!$bucket->isEmpty()) {
                $mutantProcessContainer = $bucket->dequeue();
                $threadIndex = array_shift($this->availableThreadIndexes);

                Assert::integer($threadIndex, 'Thread index can not be null.');

                $this->startProcess($mutantProcessContainer, $threadIndex);
            }

            while ($this->hasProcessesThatCouldBeFreed($threadCount)) {
                // While we wait, try fetch a good amount of next processes from the queue,
                // reducing the poll delay with each loaded process
                $this->wait($this->fillBucketOnce($bucket, $generator, $threadCount));

                // yield back so that we can work on process result
                yield from $this->tryToFreeNotRunningProcess($bucket);

                // Continue if we still have too many running processes and no processes were terminated
            }

            // this termination is added for the case when there are few processes than threads, and we don't fill/free processes above
            // yield back so that we can work on process result
            yield from $this->tryToFreeNotRunningProcess($bucket);

            // In any case try to load at least one process to the bucket
            $this->fillBucketOnce($bucket, $generator, 1);
        } while (!$bucket->isEmpty() || $this->runningProcessContainers !== []);
    }

    /**
     * This method checks if we have enough processes running that could be freed.
     * Left as protected to allow spying off a mock in tests.
     */
    protected function hasProcessesThatCouldBeFreed(int $threadCount): bool
    {
        return count($this->runningProcessContainers) >= $threadCount;
    }

    /**
     * This fills the bucket from the input stream of processes containers (original mutant processes)
     *
     * @param SplQueue<MutantProcessContainer> $bucket
     * @param Iterator<MutantProcessContainer> $input
     */
    protected function fillBucketOnce(SplQueue $bucket, Iterator $input, int $threadCount): int
    {
        Assert::greaterThan($threadCount, 0, 'Thread count must be positive.');

        if (count($bucket) >= $threadCount || !$input->valid()) {
            return 0;
        }

        $start = $this->clock->microtime();

        $current = $input->current();
        Assert::notNull($current);

        $bucket->enqueue($current);
        $input->next();

        return (int) ($this->clock->microtime() - $start) * self::NANO_SECONDS_IN_MILLI_SECOND; // ns to ms
    }

    /**
     * @param int $timeSpentDoingWork Time to subtract from the poll time when we did some work in between polls
     */
    protected function wait(int $timeSpentDoingWork): void
    {
        $this->clock->usleep(max(0, $this->poll - $timeSpentDoingWork));
    }

    /**
     * @param SplQueue<MutantProcessContainer> $bucket
     * @return iterable<MutantProcessContainer>
     */
    private function tryToFreeNotRunningProcess(SplQueue $bucket): iterable
    {
        // remove any finished process from the stack
        foreach ($this->runningProcessContainers as $index => $indexedMutantProcess) {
            $mutantProcessContainer = $indexedMutantProcess->mutantProcessContainer;
            $mutantProcess = $mutantProcessContainer->getCurrent();
            $process = $mutantProcess->getProcess();

            try {
                $process->checkTimeout();
            } catch (ProcessTimedOutException) {
                $mutantProcess->markAsTimedOut();
            }

            if ($process->isRunning()) {
                continue;
            }

            $mutantProcess->markAsFinished();

            $this->availableThreadIndexes[] = $indexedMutantProcess->threadIndex;

            unset($this->runningProcessContainers[$index]->mutantProcessContainer);
            unset($this->runningProcessContainers[$index]);

            if ($mutantProcessContainer->hasNext()) {
                $mutantProcessContainer->createNext();

                // Enqueue the needed static analysis run
                $bucket->enqueue($mutantProcessContainer);

                return;
            }

            yield $mutantProcessContainer;
        }
    }

    private function startProcess(MutantProcessContainer $mutantProcessContainer, int $threadIndex): void
    {
        $mutantProcessContainer->getCurrent()->getProcess()->start(null, [
            'INFECTION' => '1',
            'TEST_TOKEN' => $threadIndex,
        ]);

        $this->runningProcessContainers[] = new IndexedMutantProcessContainer($threadIndex, $mutantProcessContainer);
    }

    /**
     * @param iterable<MutantProcessContainer> $input
     *
     * @return Generator<MutantProcessContainer>
     */
    private static function toGenerator(iterable &$input): Generator
    {
        yield from $input;
    }
}
