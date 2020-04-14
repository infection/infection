<?php

declare(strict_types=1);

namespace Infection\Tests\Event\Subscriber;

use Infection\Event\Subscriber\NullSubscriber;
use Infection\Event\Subscriber\SubscriberFactoryRegistry;
use Infection\Tests\Fixtures\Console\FakeOutput;
use Infection\Tests\Fixtures\Event\IONullSubscriber;
use Infection\Tests\Fixtures\Event\DummySubscriberFactory;
use PHPUnit\Framework\TestCase;
use Traversable;
use function iterator_to_array;

final class SubscriberFactoryRegistryTest extends TestCase
{
    public function test_it_does_not_create_any_subscriber_if_no_factory_was_given(): void
    {
        $registry = new SubscriberFactoryRegistry();

        $subscribers = $registry->create(new FakeOutput());

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

        $registry = new SubscriberFactoryRegistry(
            new DummySubscriberFactory($subscriber1),
            new DummySubscriberFactory($subscriber2),
            new DummySubscriberFactory($subscriber3)
        );

        $subscribers = $registry->create($output);

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
