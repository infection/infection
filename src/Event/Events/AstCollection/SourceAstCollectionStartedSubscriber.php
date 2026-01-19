<?php

declare(strict_types=1);

namespace Infection\Event\Events\AstCollection;

use Infection\Event\Subscriber\EventSubscriber;

/**
 * @internal
 */
interface SourceAstCollectionStartedSubscriber extends EventSubscriber
{
    public function onSourceAstCollectionStarted(SourceAstCollectionStarted $event): void;
}
