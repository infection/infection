<?php

declare(strict_types=1);

namespace Infection\Event\Events\SourceCollection;

use Infection\Event\Subscriber\EventSubscriber;

/**
 * @internal
 */
interface SourceCollectionFinishedSubscriber extends EventSubscriber
{
    public function onSourceCollectionFinished(SourceCollectionFinished $event): void;
}
