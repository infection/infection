<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\EventDispatcher;

use Infection\EventDispatcher\EventDispatcher;
use Infection\Tests\Fixtures\UserEventSubscriber;
use Infection\Tests\Fixtures\UserWasCreated;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class EventDispatcherTest extends TestCase
{
    public function test_event_dispatcher_dispatches_events_correctly(): void
    {
        $userEvent = new UserEventSubscriber();
        $dispatcher = new EventDispatcher();
        $dispatcher->addSubscriber($userEvent);

        //Sanity check
        $this->assertSame(0, $userEvent->count);

        $dispatcher->dispatch(new UserWasCreated());
        $dispatcher->dispatch(new UserWasCreated());

        $this->assertSame(2, $userEvent->count);
    }
}
