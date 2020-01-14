<?php

declare(strict_types=1);

namespace Infection\Tests\Fixtures\Process;

use DomainException;
use Infection\EventDispatcher\EventDispatcherInterface;
use Infection\EventDispatcher\EventSubscriberInterface;

final class EventDispatcherCollector implements EventDispatcherInterface
{
    private $events = [];

    /**
     * Dispatches an event
     */
    public function dispatch($event)
    {
        $this->events[] = $event;
    }

    public function getEvents(): array
    {
        return $this->events;
    }

    public function addSubscriber(EventSubscriberInterface $eventSubscriber)
    {
        throw new DomainException('Cannot add a subscriber');
    }
}
