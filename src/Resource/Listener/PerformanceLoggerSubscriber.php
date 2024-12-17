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

namespace Infection\Resource\Listener;

use Infection\Event\ApplicationExecutionWasFinished;
use Infection\Event\ApplicationExecutionWasStarted;
use Infection\Event\Subscriber\EventSubscriber;
use Infection\Resource\Memory\MemoryFormatter;
use Infection\Resource\Time\Stopwatch;
use Infection\Resource\Time\TimeFormatter;
use function memory_get_peak_usage;
use function sprintf;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal
 */
final readonly class PerformanceLoggerSubscriber implements EventSubscriber
{
    public function __construct(
        private Stopwatch $stopwatch,
        private TimeFormatter $timeFormatter,
        private MemoryFormatter $memoryFormatter,
        private int $threadCount,
        private OutputInterface $output)
    {
    }

    public function onApplicationExecutionWasStarted(ApplicationExecutionWasStarted $event): void
    {
        $this->stopwatch->start();
    }

    public function onApplicationExecutionWasFinished(ApplicationExecutionWasFinished $event): void
    {
        $time = $this->stopwatch->stop();

        $this->output->writeln([
            '',
            sprintf(
                'Time: %s. Memory: %s. Threads: %s',
                $this->timeFormatter->toHumanReadableString($time),
                $this->memoryFormatter->toHumanReadableString(memory_get_peak_usage(true)),
                $this->threadCount,
            ),
        ]);
    }
}
