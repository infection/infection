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

namespace Infection\Tests\Architecture\PHPat\Selector;

use Infection\Engine;
use PHPat\Selector\SelectorInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Filesystem\Filesystem;
use function class_exists;
use function interface_exists;

#[CoversClass(EventArchitecture::class)]
#[CoversClass(EventClassWithoutCorrespondingSubscriber::class)]
#[CoversClass(EventDirectoryClassWithoutExpectedShape::class)]
#[CoversClass(EventSubscriberWithoutCorrespondingEvent::class)]
#[CoversClass(EventSubscriberWithoutExpectedMethod::class)]
final class EventArchitectureTest extends SelectorTestCase
{
    private const string PROJECT_ROOT = __DIR__ . '/../../../../../';

    private const string FIXTURE_DIRECTORY = self::PROJECT_ROOT . '/src/Event/Events/ArchitectureTestFixtures';

    private const string FIXTURE_NAMESPACE = 'Infection\Event\Events\ArchitectureTestFixtures';

    public static function setUpBeforeClass(): void
    {
        self::createFixtures();

        parent::setUpBeforeClass();
    }

    public static function tearDownAfterClass(): void
    {
        self::removeFixtures();
    }

    #[DataProvider('eventArchitectureShapeProvider')]
    public function test_it_detects_event_architecture_shapes(
        string $className,
        bool $expectedInEventDirectory,
        bool $expectedEvent,
        bool $expectedSubscriber,
    ): void {
        $this->assertClassOrInterfaceExists($className);

        $classReflection = $this->createClassReflection($className);

        self::assertSame($expectedInEventDirectory, EventArchitecture::isInEventDirectory($classReflection));
        self::assertSame($expectedEvent, EventArchitecture::isEvent($classReflection));
        self::assertSame($expectedSubscriber, EventArchitecture::isEventSubscriber($classReflection));
    }

    public function test_it_resolves_event_subscriber_names(): void
    {
        $eventClass = self::fixtureClass('CompleteEvent');
        $subscriberClass = self::fixtureClass('CompleteEventSubscriber');

        $this->assertClassOrInterfaceExists($eventClass);
        $this->assertClassOrInterfaceExists($subscriberClass);

        $eventReflection = $this->createClassReflection($eventClass);
        $subscriberReflection = $this->createClassReflection($subscriberClass);

        self::assertSame(
            $subscriberClass,
            EventArchitecture::getEventSubscriberName($eventReflection),
        );
        self::assertSame(
            $eventClass,
            EventArchitecture::getSubscribedEventName($subscriberReflection),
        );
        self::assertSame(
            'onCompleteEvent',
            EventArchitecture::getExpectedSubscriberMethodName($subscriberReflection),
        );
    }

    #[DataProvider('selectorProvider')]
    public function test_it_detects_event_architecture_violations(
        SelectorInterface $selector,
        string $className,
        bool $expected,
    ): void {
        $this->assertClassOrInterfaceExists($className);

        $classReflection = $this->createClassReflection($className);

        self::assertSame($expected, $selector->matches($classReflection));
    }

    /**
     * @return iterable<string, array{string, bool, bool, bool}>
     */
    public static function eventArchitectureShapeProvider(): iterable
    {
        yield 'event class' => [
            self::fixtureClass('CompleteEvent'),
            true,
            true,
            false,
        ];

        yield 'event subscriber interface' => [
            self::fixtureClass('CompleteEventSubscriber'),
            true,
            false,
            true,
        ];

        yield 'subscriber-suffixed class' => [
            self::fixtureClass('NotAnEventSubscriber'),
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

    /**
     * @return iterable<string, array{SelectorInterface, string, bool}>
     */
    public static function selectorProvider(): iterable
    {
        yield 'event without subscriber' => [
            new EventClassWithoutCorrespondingSubscriber(),
            self::fixtureClass('MissingSubscriberEvent'),
            true,
        ];

        yield 'event with subscriber' => [
            new EventClassWithoutCorrespondingSubscriber(),
            self::fixtureClass('CompleteEvent'),
            false,
        ];

        yield 'subscriber without event' => [
            new EventSubscriberWithoutCorrespondingEvent(),
            self::fixtureClass('OrphanEventSubscriber'),
            true,
        ];

        yield 'subscriber with event' => [
            new EventSubscriberWithoutCorrespondingEvent(),
            self::fixtureClass('CompleteEventSubscriber'),
            false,
        ];

        yield 'subscriber with unexpected method' => [
            new EventSubscriberWithoutExpectedMethod(),
            self::fixtureClass('InvalidMethodEventSubscriber'),
            true,
        ];

        yield 'subscriber with expected method' => [
            new EventSubscriberWithoutExpectedMethod(),
            self::fixtureClass('CompleteEventSubscriber'),
            false,
        ];

        yield 'unexpected event directory class' => [
            new EventDirectoryClassWithoutExpectedShape(),
            self::fixtureClass('NotAnEventSubscriber'),
            true,
        ];

        yield 'expected event directory event' => [
            new EventDirectoryClassWithoutExpectedShape(),
            self::fixtureClass('CompleteEvent'),
            false,
        ];

        yield 'expected event directory subscriber' => [
            new EventDirectoryClassWithoutExpectedShape(),
            self::fixtureClass('CompleteEventSubscriber'),
            false,
        ];
    }

    private static function fixtureClass(string $shortName): string
    {
        return self::FIXTURE_NAMESPACE . '\\' . $shortName;
    }

    /**
     * @phpstan-assert class-string $className
     */
    private function assertClassOrInterfaceExists(string $className): void
    {
        self::assertTrue(
            class_exists($className) || interface_exists($className),
            sprintf('Expected class or interface "%s" to exist.', $className),
        );
    }

    private static function createFixtures(): void
    {
        self::removeFixtures();

        $filesystem = new Filesystem();

        $filesystem->mkdir(self::FIXTURE_DIRECTORY);

        $filesystem->dumpFile(
            self::FIXTURE_DIRECTORY . '/CompleteEvent.php',
            <<<'PHP'
<?php
declare(strict_types=1);

namespace Infection\Event\Events\ArchitectureTestFixtures;

final readonly class CompleteEvent
{
}
PHP,
        );

        $filesystem->dumpFile(
            self::FIXTURE_DIRECTORY . '/CompleteEventSubscriber.php',
            <<<'PHP'
<?php
declare(strict_types=1);

namespace Infection\Event\Events\ArchitectureTestFixtures;

use Infection\Event\Subscriber\EventSubscriber;

interface CompleteEventSubscriber extends EventSubscriber
{
    public function onCompleteEvent(CompleteEvent $event): void;
}
PHP,
        );

        $filesystem->dumpFile(
            self::FIXTURE_DIRECTORY . '/MissingSubscriberEvent.php',
            <<<'PHP'
<?php
declare(strict_types=1);

namespace Infection\Event\Events\ArchitectureTestFixtures;

final readonly class MissingSubscriberEvent
{
}
PHP,
        );

        $filesystem->dumpFile(
            self::FIXTURE_DIRECTORY . '/OrphanEventSubscriber.php',
            <<<'PHP'
<?php
declare(strict_types=1);

namespace Infection\Event\Events\ArchitectureTestFixtures;

use Infection\Event\Subscriber\EventSubscriber;

interface OrphanEventSubscriber extends EventSubscriber
{
    public function onOrphanEvent(OrphanEvent $event): void;
}
PHP,
        );

        $filesystem->dumpFile(
            self::FIXTURE_DIRECTORY . '/InvalidMethodEvent.php',
            <<<'PHP'
<?php
declare(strict_types=1);

namespace Infection\Event\Events\ArchitectureTestFixtures;

final readonly class InvalidMethodEvent
{
}
PHP,
        );

        $filesystem->dumpFile(
            self::FIXTURE_DIRECTORY . '/InvalidMethodEventSubscriber.php',
            <<<'PHP'
<?php
declare(strict_types=1);

namespace Infection\Event\Events\ArchitectureTestFixtures;

use Infection\Event\Subscriber\EventSubscriber;

interface InvalidMethodEventSubscriber extends EventSubscriber
{
    public function onAnotherEvent(InvalidMethodEvent $event): void;
}
PHP,
        );

        $filesystem->dumpFile(
            self::FIXTURE_DIRECTORY . '/NotAnEventSubscriber.php',
            <<<'PHP'
<?php
declare(strict_types=1);

namespace Infection\Event\Events\ArchitectureTestFixtures;

final readonly class NotAnEventSubscriber
{
}
PHP,
        );
    }

    private static function removeFixtures(): void
    {
        (new Filesystem())->remove(self::FIXTURE_DIRECTORY);
    }
}
