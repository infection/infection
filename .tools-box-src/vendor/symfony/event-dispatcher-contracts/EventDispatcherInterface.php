<?php

namespace _HumbugBoxb47773b41c19\Symfony\Contracts\EventDispatcher;

use _HumbugBoxb47773b41c19\Psr\EventDispatcher\EventDispatcherInterface as PsrEventDispatcherInterface;
interface EventDispatcherInterface extends PsrEventDispatcherInterface
{
    /**
    @template
    */
    public function dispatch(object $event, string $eventName = null) : object;
}
