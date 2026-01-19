<?php

declare(strict_types=1);

namespace Infection\Event\Events\SourceCollection;

use Infection\Event\Subscriber\EventSubscriber;

/**
 * @internal
 */
interface SourceCollectionStartedSubscriber extends EventSubscriber
{
    public function onSourceCollectionStarted(SourceCollectionStarted $event): void;
}
