<?php

declare(strict_types=1);

namespace Infection\Tests\Fixtures\Event;

use Infection\Event\Subscriber\EventSubscriber;

final class NullSubscriber implements EventSubscriber
{

    /**
     * @return array<string, callable>
     */
    public function getSubscribedEvents(): array
    {
        return [];
    }
}
