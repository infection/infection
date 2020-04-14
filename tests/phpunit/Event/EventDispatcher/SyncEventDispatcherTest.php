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

use Infection\Event\EventDispatcher\SyncEventDispatcher;
use Infection\Tests\Fixtures\Event\UserWasCreatedCounterSubscriber;
use Infection\Tests\Fixtures\Event\UnknownEventSubscriber;
use Infection\Tests\Fixtures\Event\UserEventSubscriber;
use Infection\Tests\Fixtures\Event\UserWasCreated;
use PHPUnit\Framework\TestCase;

final class SyncEventDispatcherTest extends TestCase
{
    public function test_it_triggers_the_subscribers_registered_to_the_event_when_dispatcher_an_event(): void
    {
        $userSubscriber = new UserEventSubscriber();
        $userWasAddedCounterSubscriber = new UserWasCreatedCounterSubscriber(new UserWasCreated());

        $dispatcher = new SyncEventDispatcher();
        $dispatcher->addSubscriber($userSubscriber);
        $dispatcher->addSubscriber($userWasAddedCounterSubscriber);
        $dispatcher->addSubscriber(new UnknownEventSubscriber());

        // Sanity check
        $this->assertSame(0, $userSubscriber->count);
        $this->assertSame(1, $userWasAddedCounterSubscriber->getCount());

        $dispatcher->dispatch(new UserWasCreated());
        $dispatcher->dispatch(new UserWasCreated());

        $this->assertSame(2, $userSubscriber->count);
        $this->assertSame(1, $userWasAddedCounterSubscriber->getCount());
    }
}
