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
final class EventDispatcher implements EventDispatcherInterface
{
    /**
     * @var callable[][]
     */
    private $listeners = [];

    /**
     * {@inheritdoc}
     */
    public function dispatch($event): void
    {
        $name = \get_class($event);

        foreach ($this->getListeners($name) as $listener) {
            $listener($event);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addSubscriber(EventSubscriberInterface $eventSubscriber): void
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
    private function getListeners(string $eventName): array
    {
        return $this->listeners[$eventName] ?? [];
    }
}
