<?php

declare(strict_types=1);

namespace Infection\Tests\Fixtures\Event;

use Infection\Event\Subscriber\EventSubscriber;
use Infection\Event\Subscriber\SubscriberFactory;
use Symfony\Component\Console\Output\OutputInterface;

final class DummySubscriberFactory implements SubscriberFactory
{
    public function __construct(private readonly EventSubscriber $subscriber)
    {
    }

    public function create(OutputInterface $output): EventSubscriber
    {
        return $this->subscriber;
    }
}
