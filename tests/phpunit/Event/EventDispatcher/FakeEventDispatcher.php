<?php

declare(strict_types=1);

namespace Infection\Tests\Event\EventDispatcher;

use Infection\Event\EventDispatcher\EventDispatcher;
use Infection\Event\Subscriber\EventSubscriber;
use Infection\Tests\UnsupportedMethod;

final class FakeEventDispatcher implements EventDispatcher
{
    public function dispatch(object $event): void
    {
        throw UnsupportedMethod::method(self::class, __FUNCTION__);
    }

    public function addSubscriber(EventSubscriber $eventSubscriber): void
    {
        throw UnsupportedMethod::method(self::class, __FUNCTION__);
    }
}