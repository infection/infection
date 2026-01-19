<?php

declare(strict_types=1);

namespace Infection\Event\Events\Tracing;

use Infection\Event\Subscriber\EventSubscriber;

/**
 * @internal
 */
interface SourceTracingFinishedSubscriber extends EventSubscriber
{
    public function onSourceTracingFinished(SourceTracingFinished $event): void;
}
