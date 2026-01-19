<?php

declare(strict_types=1);

namespace Infection\TestFramework\Tracing;

use Infection\Event\EventDispatcher\EventDispatcher;
use Infection\Event\Events\Tracing\SourceTracingFinished;
use Infection\Event\Events\Tracing\SourceTracingStarted;
use Infection\TestFramework\Tracing\Trace\Trace;
use SplFileInfo;

/**
 * @internal
 */
final readonly class EventEmitterTracer implements Tracer
{
    public function __construct(
        private Tracer $decoratedTracer,
        private EventDispatcher $eventDispatcher,
    ) {
    }

    public function trace(SplFileInfo $fileInfo): Trace
    {
        $this->eventDispatcher->dispatch(new SourceTracingStarted());

        $trace = $this->decoratedTracer->trace($fileInfo);

        $this->eventDispatcher->dispatch(new SourceTracingFinished());

        return $trace;
    }
}