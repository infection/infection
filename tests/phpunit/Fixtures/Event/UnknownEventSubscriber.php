<?php

declare(strict_types=1);

namespace Infection\Tests\Fixtures\Event;

use Infection\Event\Subscriber\EventSubscriber;
use LogicException;

final class UnknownEventSubscriber implements EventSubscriber
{
    public function onUnknownEventSubscriber(UnknownEventSubscriber $event): void
    {
        throw new LogicException();
    }
}
