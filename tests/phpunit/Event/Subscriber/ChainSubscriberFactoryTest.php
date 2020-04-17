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
use Infection\Tests\Fixtures\Console\FakeOutput;
use Infection\Tests\Fixtures\Event\DummySubscriberFactory;
use Infection\Tests\Fixtures\Event\IONullSubscriber;
use function iterator_to_array;
use PHPUnit\Framework\TestCase;
use Traversable;

final class ChainSubscriberFactoryTest extends TestCase
{
    public function test_it_does_not_create_any_subscriber_if_no_factory_was_given(): void
    {
        $factory = new ChainSubscriberFactory();

        $subscribers = $factory->create(new FakeOutput());

        $this->assertCount(
            0,
            $subscribers instanceof Traversable
                ? iterator_to_array($subscribers, false)
                : $subscribers
        );
    }

    public function test_it_creates_subscribers_from_each_factory_given(): void
    {
        $output = new FakeOutput();

        $subscriber1 = new IONullSubscriber($output);
        $subscriber2 = new IONullSubscriber($output);
        $subscriber3 = new IONullSubscriber($output);

        $factory = new ChainSubscriberFactory(
            new DummySubscriberFactory($subscriber1),
            new DummySubscriberFactory($subscriber2),
            new DummySubscriberFactory($subscriber3)
        );

        $subscribers = $factory->create($output);

        if ($subscribers instanceof Traversable) {
            $subscribers = iterator_to_array($subscribers, false);
        }

        $this->assertSame(
            [
                $subscriber1,
                $subscriber2,
                $subscriber3,
            ],
            $subscribers
        );
    }
}
