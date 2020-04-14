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

namespace Infection\Tests\Event\Subscriber;

use Infection\Event\Subscriber\ChainSubscriberFactory;
use Infection\Event\Subscriber\NullSubscriber;
use Infection\Event\Subscriber\SubscriberRegisterer;
use Infection\Tests\Fixtures\Console\FakeOutput;
use Infection\Tests\Fixtures\Event\DummySubscriberFactory;
use Infection\Tests\Fixtures\Event\SubscriberCollectEventDispatcher;
use PHPUnit\Framework\TestCase;

final class SubscriberRegistererTest extends TestCase
{
    /**
     * @var SubscriberCollectEventDispatcher
     */
    private $eventDispatcher;

    protected function setUp(): void
    {
        $this->eventDispatcher = new SubscriberCollectEventDispatcher();
    }

    public function test_it_creates_and_register_all_the_created_subscribers(): void
    {
        $subscriber0 = new NullSubscriber();
        $subscriber1 = new NullSubscriber();
        $subscriber2 = new NullSubscriber();

        $factory = new ChainSubscriberFactory(
            new DummySubscriberFactory($subscriber0),
            new DummySubscriberFactory($subscriber1),
            new DummySubscriberFactory($subscriber2)
        );

        $registerer = new SubscriberRegisterer($this->eventDispatcher, $factory);

        // Sanity check
        $this->assertCount(0, $this->eventDispatcher->getSubscribers());

        $registerer->registerSubscribers(new FakeOutput());

        $this->assertSame(
            [
                $subscriber0,
                $subscriber1,
                $subscriber2,
            ],
            $this->eventDispatcher->getSubscribers()
        );
    }

    public function test_it_registers_no_subscriber_if_there_is_no_subscribers(): void
    {
        $registerer = new SubscriberRegisterer(
            $this->eventDispatcher,
            new ChainSubscriberFactory()
        );

        // Sanity check
        $this->assertCount(0, $this->eventDispatcher->getSubscribers());

        $registerer->registerSubscribers(new FakeOutput());

        $this->assertCount(0, $this->eventDispatcher->getSubscribers());
    }
}
