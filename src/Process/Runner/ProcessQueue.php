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
use DuoClock\DuoClock;
use Infection\Process\MutantProcessContainer;
use Iterator;
use SplQueue;
use Webmozart\Assert\Assert;

/**
 * @final
 * @internal
 */
class ProcessQueue
{
    private const MINIMAL_DEPTH = 1;

    private const NANO_SECONDS_IN_MILLI_SECOND = 1_000_000;

    /**
     * @var SplQueue<MutantProcessContainer>
     */
    private readonly SplQueue $bucket;

    public function __construct(
        private readonly DuoClock $clock = new DuoClock(),
    ) {
        $this->bucket = new SplQueue();
    }

    /**
     * This fills the bucket from the input stream of processes containers (original mutant processes)
     *
     * @param Iterator<MutantProcessContainer> $input
     * @param positive-int $maxQueueDepth
     * @return int Microseconds spent doing work to enqueue a process
     */
    public function enqueueFrom(Iterator $input, int $maxQueueDepth = self::MINIMAL_DEPTH): int
    {
        Assert::greaterThan($maxQueueDepth, 0, 'Max queue depth must be positive.');

        if (!$this->hasCapacityFor($maxQueueDepth) || !$input->valid()) {
            return 0;
        }

        $start = $this->clock->microtime();

        $current = $input->current();
        Assert::notNull($current, 'Input iterator must not produce null values.');

        $this->bucket->enqueue($current);
        $input->next();

        return self::ns2ms($this->clock->microtime() - $start);
    }

    public function enqueue(MutantProcessContainer $container): void
    {
        $this->bucket->enqueue($container);
    }

    public function dequeue(): MutantProcessContainer
    {
        return $this->bucket->dequeue();
    }

    public function isEmpty(): bool
    {
        return $this->bucket->isEmpty();
    }

    /**
     * Check if the queue has capacity for additional items.
     *
     * @param positive-int $requiredCapacity Maximum items before queue is considered full
     */
    private function hasCapacityFor(int $requiredCapacity): bool
    {
        return count($this->bucket) < $requiredCapacity;
    }

    private static function ns2ms(float $time): int
    {
        return (int) ($time * self::NANO_SECONDS_IN_MILLI_SECOND);
    }
}
