<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Psr\EventDispatcher;

interface ListenerProviderInterface
{
    public function getListenersForEvent(object $event) : iterable;
}
