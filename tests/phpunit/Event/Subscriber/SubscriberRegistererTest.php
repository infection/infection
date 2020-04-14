<?php

declare(strict_types=1);

namespace Infection\Tests\Event\Subscriber;

use Infection\Event\Subscriber\NullSubscriber;
use Infection\Event\Subscriber\SubscriberFactoryRegistry;
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

        $registry = new SubscriberFactoryRegistry(
            new DummySubscriberFactory($subscriber0),
            new DummySubscriberFactory($subscriber1),
            new DummySubscriberFactory($subscriber2)
        );

        $registerer = new SubscriberRegisterer($this->eventDispatcher, $registry);

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
            new SubscriberFactoryRegistry()
        );

        // Sanity check
        $this->assertCount(0, $this->eventDispatcher->getSubscribers());

        $registerer->registerSubscribers(new FakeOutput());

        $this->assertCount(0, $this->eventDispatcher->getSubscribers());
    }
}
