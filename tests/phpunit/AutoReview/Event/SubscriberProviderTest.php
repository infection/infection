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

use function in_array;
use Infection\Event\Subscriber\EventSubscriber;
use PHPUnit\Framework\TestCase;
use function Safe\class_implements;
use function Safe\sprintf;

/**
 * @covers \Infection\Tests\AutoReview\Event\SubscriberProvider
 */
final class SubscriberProviderTest extends TestCase
{
    /**
     * @dataProvider \Infection\Tests\AutoReview\Event\SubscriberProvider::subscriberClassesProvider()
     */
    public function test_subscriber_class_provider_is_valid(string $className): void
    {
        $this->assertTrue(
            class_exists($className, true)
            && in_array(EventSubscriber::class, class_implements($className), true),
            sprintf(
                'The "%s" class was expected to be an event subscriber, but it is not a ' .
                '"%s".',
                $className,
                EventSubscriber::class
            )
        );
    }

    /**
     * @dataProvider \Infection\Tests\AutoReview\Event\SubscriberProvider::subscriberSubscriptionMethodsProvider()
     *
     * @param class-string $className
     * @param string[] $subscriptionMethods
     */
    public function test_subscriber_subscription_methods_provider_is_valid(
        string $className,
        array $subscriptionMethods
    ): void {
        foreach ($subscriptionMethods as $subscriptionMethod) {
            $this->assertIsString($subscriptionMethod);
        }
    }
}
