<?php

declare(strict_types=1);

namespace Infection\Event\Events\AstCollection;

use Infection\Event\Subscriber\EventSubscriber;

/**
 * @internal
 */
interface SourceAstCollectionFinishedSubscriber extends EventSubscriber
{
    public function onSourceAstCollectionFinished(SourceAstCollectionFinished $event): void;
}
