<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Event\EventDispatcher;

use _HumbugBox9658796bb9f0\Infection\Event\Subscriber\EventSubscriber;
interface EventDispatcher
{
    public function dispatch(object $event) : void;
    public function addSubscriber(EventSubscriber $eventSubscriber) : void;
}
