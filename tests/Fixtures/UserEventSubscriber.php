<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

namespace Infection\Tests\Fixtures;

use Infection\EventDispatcher\EventSubscriberInterface;

class UserEventSubscriber implements EventSubscriberInterface
{
    public $count = 0;

    public function getSubscribedEvents(): array
    {
        return [
            UserWasCreated::class => [$this, '__invoke'],
        ];
    }

    public function __invoke()
    {
        ++$this->count;
    }
}
