<?php

declare(strict_types=1);

namespace Infection\Tests\Fixtures\Event;

use Infection\Event\Subscriber\EventSubscriber;

final class NullSubscriber implements EventSubscriber
{
    public $count = 0;

    public function __construct(UserWasCreated $event)
    {
        ++$this->count; // Should never be called more than once
    }

}
