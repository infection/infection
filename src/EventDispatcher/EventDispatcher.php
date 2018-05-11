<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\EventDispatcher;

/**
 * @internal
 */
class EventDispatcher implements EventDispatcherInterface
{
    /**
     * @var callable[][]
     */
    private $listeners = [];

    /**
     * {@inheritdoc}
     */
    public function dispatch($event)
    {
        $name = get_class($event);

        foreach ($this->getListeners($name) as $listener) {
            $listener($event);
        }
    }

    public function addSubscriber(EventSubscriberInterface $eventSubscriber)
    {
        foreach ($eventSubscriber->getSubscribedEvents() as $eventName => $listener) {
            $this->listeners[$eventName][] = $listener;
        }
    }

    /**
     * @param string $eventName
     *
     * @return callable[]
     */
    private function getListeners($eventName)
    {
        if (!isset($this->listeners[$eventName])) {
            return [];
        }

        return $this->listeners[$eventName];
    }
}
