<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\EventDispatcher;

class EventDispatcher implements EventDispatcherInterface, ContainsListenersInterface
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

    /**
     * {@inheritdoc}
     */
    public function addListener($eventName, callable $listener)
    {
        $this->listeners[$eventName][] = $listener;
    }

    /**
     * {@inheritdoc}
     */
    public function getListeners($eventName)
    {
        if (!$this->hasListeners($eventName)) {
            return [];
        }

        return $this->listeners[$eventName];
    }

    /**
     * {@inheritdoc}
     */
    public function hasListeners($eventName)
    {
        return isset($this->listeners[$eventName]);
    }

    /**
     * {@inheritdoc}
     */
    public function addSubscriber(EventSubscriberInterface $eventSubscriber)
    {
        foreach ($eventSubscriber->getSubscribedEvents() as $eventName => $listener) {
            $this->addListener($eventName, $listener);
        }
    }
}
