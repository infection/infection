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

namespace Infection\Performance\Listener;

use Infection\EventDispatcher\EventSubscriberInterface;
use Infection\Events\ApplicationExecutionFinished;
use Infection\Events\ApplicationExecutionStarted;
use Infection\Performance\Memory\MemoryFormatter;
use Infection\Performance\Time\TimeFormatter;
use Infection\Performance\Time\Timer;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal
 */
final class PerformanceLoggerSubscriber implements EventSubscriberInterface
{
    /**
     * @var Timer
     */
    private $timer;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var TimeFormatter
     */
    private $timeFormatter;

    /**
     * @var MemoryFormatter
     */
    private $memoryFormatter;

    public function __construct(
        Timer $timer,
        TimeFormatter $timeFormatter,
        MemoryFormatter $memoryFormatter,
        OutputInterface $output
    ) {
        $this->timer = $timer;
        $this->timeFormatter = $timeFormatter;
        $this->output = $output;
        $this->memoryFormatter = $memoryFormatter;
    }

    public function getSubscribedEvents(): array
    {
        return [
            ApplicationExecutionStarted::class => [$this, 'onApplicationExecutionStarted'],
            ApplicationExecutionFinished::class => [$this, 'onApplicationExecutionFinished'],
        ];
    }

    public function onApplicationExecutionStarted(): void
    {
        $this->timer->start();
    }

    public function onApplicationExecutionFinished(): void
    {
        $time = $this->timer->stop();

        $this->output->writeln([
            '',
            sprintf(
                'Time: %s. Memory: %s',
                $this->timeFormatter->toHumanReadableString($time),
                $this->memoryFormatter->toHumanReadableString(memory_get_peak_usage(true))
            ),
        ]);
    }
}
