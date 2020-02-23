<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

namespace Infection\Tests\Fixtures\Event;

use Infection\Event\Subscriber\EventSubscriber;

class UserEventSubscriber implements EventSubscriber
{
    public $count = 0;

    public function onUserWasCreated(UserWasCreated $event): void
    {
        ++$this->count;
    }
}
