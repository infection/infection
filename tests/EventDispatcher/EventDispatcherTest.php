<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

namespace Infection\Tests\EventDispatcher;

use Infection\EventDispatcher\EventDispatcher;
use Infection\Tests\Fixtures\UserEventSubscriber;
use Infection\Tests\Fixtures\UserWasCreated;
use PHPUnit\Framework\TestCase;

class EventDispatcherTest extends TestCase
{
    /**
     * @var EventDispatcher
     */
    private $eventDispatcher;

    /**
     * @var string
     */
    private $eventName;

    /**
     * @var callable
     */
    private $listener;

    /**
     * @var int
     */
    private $listenerCallsCount = 0;

    public function setUp()
    {
        $this->eventName = UserWasCreated::class;

        $this->listener = function () {
            ++$this->listenerCallsCount;
        };

        $this->eventDispatcher = new EventDispatcher();
    }

    /**
     * @test
     */
    public function it_adds_event_listeners()
    {
        $this->eventDispatcher->addListener($this->eventName, $this->listener);

        $this->assertTrue($this->eventDispatcher->hasListeners($this->eventName));
        $this->assertEquals($this->listener, $this->eventDispatcher->getListeners($this->eventName)[0]);
    }

    /**
     * @test
     */
    public function it_return_empty_array_when_event_does_not_have_listeners()
    {
        $this->assertEquals([], $this->eventDispatcher->getListeners('test'));
    }

    /**
     * @test
     */
    public function it_calls_all_listeners_during_dispatch()
    {
        $this->eventDispatcher->addListener($this->eventName, $this->listener);
        $this->eventDispatcher->addListener($this->eventName, $this->listener);

        $this->eventDispatcher->dispatch(new UserWasCreated());

        $this->assertEquals(2, $this->listenerCallsCount);
    }

    /**
     * @test
     */
    public function it_adds_event_listeners_from_subscriber()
    {
        $this->eventDispatcher->addSubscriber(new UserEventSubscriber());

        $this->assertTrue($this->eventDispatcher->hasListeners($this->eventName));
    }
}
