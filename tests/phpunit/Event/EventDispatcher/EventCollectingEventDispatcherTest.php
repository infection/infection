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

use Infection\Event\EventDispatcher\EventCollectingEventDispatcher;
use Infection\Event\EventDispatcher\EventDispatcher;
use Infection\Event\EventDispatcher\SyncEventDispatcher;
use Infection\Event\Events\Application\ApplicationExecutionWasStarted;
use Infection\Event\Events\ArtefactCollection\InitialTestExecution\InitialTestSuiteWasFinished;
use Infection\Tests\Fixtures\Event\UserEventSubscriber;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use stdClass;

#[CoversClass(EventCollectingEventDispatcher::class)]
final class EventCollectingEventDispatcherTest extends TestCase
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

        $dispatcher = new EventCollectingEventDispatcher(
            $inner,
        );

        $dispatcher->addSubscriber($subscriber);
    }

    public function test_it_records_the_event_before_a_subscriber_fails(): void
    {
        $event = new ApplicationExecutionWasStarted();
        $inner = $this->createMock(EventDispatcher::class);
        $dispatcher = new EventCollectingEventDispatcher($inner);
        $inner
            ->expects($this->once())
            ->method('dispatch')
            ->with($event)
            ->willReturnCallback(function () use ($dispatcher, $event): never {
                $this->assertSame(
                    [$event],
                    $dispatcher->getEvents(),
                    'The trace must record the event before invoking subscribers.',
                );

                throw new RuntimeException('Subscriber failed');
            })
        ;

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Subscriber failed');

        $dispatcher->dispatch($event);
    }

    public function test_it_collects_the_original_events_in_dispatch_order(): void
    {
        $dispatcher = new EventCollectingEventDispatcher(
            new SyncEventDispatcher(),
        );

        $this->assertSame(
            [],
            $dispatcher->getEvents(),
            'The collecting dispatcher must start with no events.',
        );

        $started = new ApplicationExecutionWasStarted();
        $finished = new InitialTestSuiteWasFinished('Test output');
        $custom = new stdClass();
        $custom->payload = 'Custom event payload';

        $dispatcher->dispatch($started);
        $dispatcher->dispatch($finished);
        $dispatcher->dispatch($custom);
        $dispatcher->dispatch($started);

        $this->assertSame(
            [$started, $finished, $custom, $started],
            $dispatcher->getEvents(),
            'The collecting dispatcher must preserve event identity, dispatch order, and repeated dispatches.',
        );
    }
}
