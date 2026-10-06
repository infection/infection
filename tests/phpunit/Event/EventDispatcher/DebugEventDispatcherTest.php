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

namespace Infection\Tests\Event\EventDispatcher;

use Infection\Event\EventDispatcher\DebugEventDispatcher;
use Infection\Event\EventDispatcher\EventDispatcher;
use Infection\Event\Events\Application\ApplicationExecutionWasStarted;
use Infection\Report\DebugEventsDataProducer;
use Infection\Reporter\Reporter;
use Infection\Tests\Fixtures\Event\UserEventSubscriber;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use RuntimeException;

#[CoversClass(DebugEventDispatcher::class)]
final class DebugEventDispatcherTest extends TestCase
{
    public function test_it_forwards_subscriber_registration(): void
    {
        $subscriber = new UserEventSubscriber();
        $inner = $this->createMock(EventDispatcher::class);
        $inner
            ->expects($this->once())
            ->method('addSubscriber')
            ->with($subscriber)
        ;

        $dispatcher = new DebugEventDispatcher(
            $inner,
            new DebugEventsDataProducer(),
            $this->createStub(Reporter::class),
        );

        $dispatcher->addSubscriber($subscriber);
    }

    public function test_it_records_the_event_before_a_subscriber_fails(): void
    {
        $event = new ApplicationExecutionWasStarted();
        $recorded = false;
        $dataProducer = new DebugEventsDataProducer();
        $reporter = $this->createMock(Reporter::class);
        $reporter
            ->expects($this->once())
            ->method('report')
            ->willReturnCallback(function () use ($dataProducer, &$recorded): void {
                $this->assertSame(
                    '{"sequence":1,"event":"Infection\\\\Event\\\\Events\\\\Application\\\\ApplicationExecutionWasStarted","data":{}}' . "\n",
                    $dataProducer->produce(),
                    'The event must be recorded before the reporter publishes it.',
                );
                $recorded = true;
            })
        ;

        $inner = $this->createMock(EventDispatcher::class);
        $inner
            ->expects($this->once())
            ->method('dispatch')
            ->with($event)
            ->willReturnCallback(function () use (&$recorded): never {
                $this->assertTrue(
                    $recorded,
                    'The trace must record the event before invoking subscribers.',
                );

                throw new RuntimeException('Subscriber failed');
            })
        ;

        $dispatcher = new DebugEventDispatcher(
            $inner,
            $dataProducer,
            $reporter,
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Subscriber failed');

        $dispatcher->dispatch($event);
    }
}
