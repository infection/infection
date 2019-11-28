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

namespace Infection\Tests\Performance\Listener;

use Infection\EventDispatcher\EventDispatcher;
use Infection\Events\ApplicationExecutionFinished;
use Infection\Events\ApplicationExecutionStarted;
use Infection\Performance\Listener\PerformanceLoggerSubscriber;
use Infection\Performance\Memory\MemoryFormatter;
use Infection\Performance\Time\TimeFormatter;
use Infection\Performance\Time\Timer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;

final class PerformanceLoggerSubscriberTest extends TestCase
{
    /**
     * @var OutputInterface|MockObject
     */
    private $output;

    protected function setUp(): void
    {
        $this->output = $this->createMock(OutputInterface::class);
    }

    public function test_it_reacts_on_application_execution_events(): void
    {
        $this->output->expects($this->once())
            ->method('writeln')
            ->with($this->callback(static function ($parameter) {
                return \is_array($parameter) && '' === $parameter[0] && 0 === strpos($parameter[1], 'Time:');
            }));

        $dispatcher = new EventDispatcher();
        $dispatcher->addSubscriber(new PerformanceLoggerSubscriber(
            new Timer(),
            new TimeFormatter(),
            new MemoryFormatter(),
            $this->output
        ));

        $dispatcher->dispatch(new ApplicationExecutionStarted());
        $dispatcher->dispatch(new ApplicationExecutionFinished());
    }
}
