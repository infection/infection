<?php

declare(strict_types=1);

namespace Infection\Tests\Fixtures\Event;

use DomainException;
use Infection\Event\EventDispatcher\EventDispatcher;
use Infection\Event\Subscriber\EventSubscriber;

final class EventDispatcherCollector implements EventDispatcher
{
    private $events = [];

    public function dispatch(object $event): void
    {
        $this->events[] = $event;
    }

    public function getEvents(): array
    {
        return $this->events;
    }

    public function addSubscriber(EventSubscriber $eventSubscriber): void
    {
        throw new DomainException('Cannot add a subscriber');
    }
}
