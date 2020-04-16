<?php

declare(strict_types=1);

namespace Infection\Tests\Fixtures\Event;

use Infection\Event\Subscriber\EventSubscriber;
use Infection\Event\Subscriber\SubscriberFactory;
use Symfony\Component\Console\Output\OutputInterface;

final class DummySubscriberFactory implements SubscriberFactory
{
    private $subscriber;

    public function __construct(EventSubscriber $subscriber)
    {
        $this->subscriber = $subscriber;
    }

    public function create(OutputInterface $output): EventSubscriber
    {
        return $this->subscriber;
    }
}
