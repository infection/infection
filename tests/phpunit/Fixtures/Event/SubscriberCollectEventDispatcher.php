<?php

declare(strict_types=1);

namespace Infection\Tests\Fixtures\Event;

use Infection\Event\EventDispatcher\EventDispatcher;
use Infection\Event\Subscriber\EventSubscriber;
use Infection\Tests\UnsupportedMethod;

final class SubscriberCollectEventDispatcher implements EventDispatcher
{
    /**
     * @var EventSubscriber[]
     */
    private $subscribers = [];

    public function dispatch(object $event): void
    {
        throw UnsupportedMethod::method(__CLASS__, __FUNCTION__);
    }

    public function addSubscriber(EventSubscriber $eventSubscriber): void
    {
        $this->subscribers[] = $eventSubscriber;
    }

    /**
     * @return EventSubscriber[]
     */
    public function getSubscribers(): array
    {
        return $this->subscribers;
    }
}
