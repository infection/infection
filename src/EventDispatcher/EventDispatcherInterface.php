<?php

declare(strict_types=1);

namespace Infection\EventDispatcher;

interface EventDispatcherInterface
{
    /**
     * Dispatches an event
     *
     * @param mixed $event
     */
    public function dispatch($event);
}