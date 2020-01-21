<?php

declare(strict_types=1);

namespace Infection\Tests\Fixtures\Event;

use Infection\Event\EventDispatcher\EventSubscriberInterface;

final class NullSubscriber implements EventSubscriberInterface
{

    /**
     * @return array<string, callable>
     */
    public function getSubscribedEvents(): array
    {
        return [];
    }
}
