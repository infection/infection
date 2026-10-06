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

namespace Infection\Tests\Container\Builder;

use Infection\Container\Builder\EventDispatcherBuilder;
use Infection\Event\EventDispatcher\SyncEventDispatcher;
use Infection\Event\Events\Application\ApplicationExecutionWasStarted;
use Infection\FileSystem\FileSystem;
use Infection\Tests\Configuration\ConfigurationBuilder;
use Infection\Tests\Configuration\Entry\LogsBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(EventDispatcherBuilder::class)]
final class EventDispatcherBuilderTest extends TestCase
{
    public function test_it_returns_the_standard_dispatcher_when_disabled(): void
    {
        $fileSystem = $this->createMock(FileSystem::class);
        $fileSystem
            ->expects($this->never())
            ->method('dumpFile')
        ;
        $fileSystem
            ->expects($this->never())
            ->method('appendToFile')
        ;

        $builder = new EventDispatcherBuilder(
            ConfigurationBuilder::withMinimalTestData()->build(),
            $fileSystem,
        );
        $dispatcher = $builder->build();

        $this->assertInstanceOf(
            SyncEventDispatcher::class,
            $dispatcher,
            'Disabled tracing must use the standard event dispatcher.',
        );

        $dispatcher->dispatch(new ApplicationExecutionWasStarted());
    }

    public function test_it_enables_tracing_regardless_of_log_verbosity(): void
    {
        $configuration = ConfigurationBuilder::withMinimalTestData()
            ->withLogs(
                LogsBuilder::withMinimalTestData()
                    ->withDebugEventsLogFilePath('/events.jsonl')
                    ->build(),
            )
            ->build()
        ;
        $fileSystem = $this->createMock(FileSystem::class);
        $fileSystem
            ->expects($this->once())
            ->method('dumpFile')
            ->with(
                '/events.jsonl',
                '{"sequence":1,"event":"Infection\\\\Event\\\\Events\\\\Application\\\\ApplicationExecutionWasStarted","data":{}}' . "\n",
            )
        ;

        $builder = new EventDispatcherBuilder(
            $configuration,
            $fileSystem,
        );

        $dispatcher = $builder->build();

        $dispatcher->dispatch(new ApplicationExecutionWasStarted());
    }
}
