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

namespace Infection\Tests\Architecture\PHPat\Selector\Support\EventArchitectureTest;

use function class_exists;
use Infection\Engine;
use Infection\Tests\Architecture\PHPat\Selector\SelectorTestCase;
use Infection\Tests\Architecture\PHPat\Selector\Support\EventArchitecture;
use Infection\Tests\Architecture\PHPat\Selector\Support\EventArchitectureTest\Fixtures\CompleteEvent;
use Infection\Tests\Architecture\PHPat\Selector\Support\EventArchitectureTest\Fixtures\CompleteEventSubscriber;
use Infection\Tests\Architecture\PHPat\Selector\Support\EventArchitectureTest\Fixtures\NotAnEventSubscriber;
use function interface_exists;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use function sprintf;

#[CoversClass(EventArchitecture::class)]
final class EventArchitectureTest extends SelectorTestCase
{
    private const string PROJECT_ROOT = __DIR__ . '/../../../../../../../';

    private const string FIXTURE_DIRECTORY = 'tests/phpunit/Architecture/PHPat/Selector/Support/EventArchitectureTest/Fixtures';

    private EventArchitecture $eventArchitecture;

    protected function setUp(): void
    {
        $this->eventArchitecture = new EventArchitecture(
            self::PROJECT_ROOT,
            self::FIXTURE_DIRECTORY,
        );
    }

    public function test_it_resolves_single_event_subscriber_names(): void
    {
        $eventClass = CompleteEvent::class;
        $subscriberClass = CompleteEventSubscriber::class;

        $this->assertClassExists($eventClass);
        $this->assertInterfaceExists($subscriberClass);

        $eventReflection = $this->createClassReflection($eventClass);
        $subscriberReflection = $this->createClassReflection($subscriberClass);
        $eventArchitecture = $this->eventArchitecture;

        $this->assertSame(
            $subscriberClass,
            $eventArchitecture->getSingleEventSubscriberName($eventReflection),
        );
        $this->assertSame(
            $eventClass,
            $eventArchitecture->getSubscribedEventName($subscriberReflection),
        );
        $this->assertSame(
            'onCompleteEvent',
            $eventArchitecture->getExpectedSingleEventSubscriberMethodName($subscriberReflection),
        );
    }

    #[DataProvider('eventArchitectureShapeProvider')]
    public function test_it_detects_event_architecture_shapes(
        string $className,
        bool $expectedInEventDirectory,
        bool $expectedIsEvent,
        bool $expectedIsSingleEventSubscriber,
    ): void {
        $this->assertClassOrInterfaceExists($className);

        $classReflection = $this->createClassReflection($className);

        $actualInEventDirectory = $this->eventArchitecture->isInEventDirectory($classReflection);
        $actualIsEvent = $this->eventArchitecture->isEvent($classReflection);
        $actualIsSingleEventSubscriber = $this->eventArchitecture->isSingleEventSubscriber($classReflection);

        $this->assertSame($expectedInEventDirectory, $actualInEventDirectory);
        $this->assertSame($expectedIsEvent, $actualIsEvent);
        $this->assertSame($expectedIsSingleEventSubscriber, $actualIsSingleEventSubscriber);
    }

    public static function eventArchitectureShapeProvider(): iterable
    {
        yield 'event class' => [
            CompleteEvent::class,
            true,
            true,
            false,
        ];

        yield 'single-event subscriber interface' => [
            CompleteEventSubscriber::class,
            true,
            false,
            true,
        ];

        yield 'subscriber-suffixed class' => [
            NotAnEventSubscriber::class,
            true,
            false,
            false,
        ];

        yield 'source class outside event directory' => [
            Engine::class,
            false,
            false,
            false,
        ];
    }

    private function assertClassExists(string $className): void
    {
        $this->assertTrue(
            class_exists($className),
            sprintf(
                'Expected class "%s" to exist.',
                $className,
            ),
        );
    }

    private function assertInterfaceExists(string $className): void
    {
        $this->assertTrue(
            interface_exists($className),
            sprintf(
                'Expected interface "%s" to exist.',
                $className,
            ),
        );
    }

    /**
     * @phpstan-assert class-string $className
     */
    private function assertClassOrInterfaceExists(string $className): void
    {
        $this->assertTrue(
            class_exists($className) || interface_exists($className),
            sprintf('Expected class or interface "%s" to exist.', $className),
        );
    }
}
