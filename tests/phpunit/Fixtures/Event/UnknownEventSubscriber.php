<?php

declare(strict_types=1);

namespace Infection\Tests\Fixtures\Event;

use Infection\Event\EventDispatcher\EventSubscriber;
use LogicException;

final class UnknownEventSubscriber implements EventSubscriber
{
    public function getSubscribedEvents(): array
    {
        return ['Unknown' => static function () { throw new LogicException();}];
    }
}
