<?php

declare(strict_types=1);

namespace Infection\Event\Events\Tracing;

use Infection\Event\Subscriber\EventSubscriber;

/**
 * @internal
 */
interface SourceTracingStartedSubscriber extends EventSubscriber
{
    public function onSourceTracingStarted(SourceTracingStarted $event): void;
}
