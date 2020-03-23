<?php

declare(strict_types=1);

namespace Infection\Tests\Fixtures\Event;

use Infection\Event\Subscriber\EventSubscriber;
use Infection\Tests\UnsupportedMethod;

final class UnknownEventSubscriber implements EventSubscriber
{
    public function onUnknownEventSubscriber(UnknownEventSubscriber $event): void
    {
        throw UnsupportedMethod::method(__CLASS__, __FUNCTION__);
    }
}
