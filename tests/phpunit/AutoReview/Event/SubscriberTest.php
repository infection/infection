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

namespace Infection\Tests\AutoReview\Event;

use Infection\Event\Subscriber\EventSubscriber;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use function Safe\sprintf;

final class SubscriberTest extends TestCase
{
    /**
     * @dataProvider \Infection\Tests\AutoReview\Event\SubscriberProvider::subscriberClassesProvider
     *
     * @param class-string $subscriberClass
     */
    public function test_subscription_methods_match_their_event_names(string $subscriberClass): void
    {
        $subscriberMethods = (new ReflectionClass($subscriberClass))->getMethods();

        foreach ($subscriberMethods as $method) {
            if (!$this->isSubscriptionMethod($method)) {
                continue;
            }

            $this->assertIsSubscriptionMethod($subscriberClass, $method);
        }
    }

    /**
     * @dataProvider \Infection\Tests\AutoReview\Event\SubscriberProvider::subscriberClassesProvider
     *
     * @param class-string $subscriberClass
     */
    public function test_the_subscribed_methods_can_all_be_collected(string $subscriberClass): void
    {
        /** @var EventSubscriber $subscriber */
        $subscriber = (new ReflectionClass($subscriberClass))->newInstanceWithoutConstructor();

        $subscriber->getSubscribedEvents();

        $this->addToAssertionCount(1);
    }

    private function isSubscriptionMethod(ReflectionMethod $method): bool
    {
        return !$method->isConstructor()
            && $method->isPublic()
            && $method->getName() !== 'getSubscribedEvents'
        ;
    }

    /**
     * @param class-string $subscriberClass
     */
    private function assertIsSubscriptionMethod(string $subscriberClass, ReflectionMethod $method): void
    {
        $this->assertSame(
            1,
            $method->getNumberOfParameters(),
            sprintf(
                'Expected a subscription method "%s::%s" to have exactly one parameter: the'
                . ' event. Got "%d"',
                $subscriberClass,
                $method->getName(),
                $method->getNumberOfParameters()
            )
        );

        $eventParameter = $method->getParameters()[0];
        $eventType = $eventParameter->getType();

        $this->assertNotNull(
            $eventType,
            sprintf(
                'Expected the parameter "%s" to have a type',
                $eventParameter->getName()
            )
        );
        $this->assertInstanceOf(
            ReflectionNamedType::class,
            $eventType,
            sprintf(
                'Expected the parameter "%s" to have the type against its class',
                $eventParameter->getName()
            )
        );

        $expectedSubscriptionMethodName = 'on' . (new ReflectionClass($eventType->getName()))->getShortName();

        $this->assertSame(
            $expectedSubscriptionMethodName,
            $method->getName(),
            'Expected the subscription method to follow the project naming convention'
        );
    }
}
