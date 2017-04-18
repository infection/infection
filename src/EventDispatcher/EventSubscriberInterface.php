<?php

declare(strict_types=1);

namespace Infection\EventDispatcher;

interface EventSubscriberInterface
{
    /**
     * @return array
     */
    public function getSubscribedEvents();
}
