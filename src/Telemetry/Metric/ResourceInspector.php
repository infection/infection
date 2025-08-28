<?php

declare(strict_types=1);

namespace Infection\Telemetry\Metric;

use Infection\Telemetry\Metric\GarbageCollection\GarbageCollectorInspector;
use Infection\Telemetry\Metric\Memory\MemoryInspector;
use Infection\Telemetry\Metric\Time\Stopwatch;

final class ResourceInspector
{
    public function __construct(
        private readonly Stopwatch $stopwatch,
        private readonly MemoryInspector $memoryInspector,
        private readonly GarbageCollectorInspector $garbageCollectorInspector,
    ) {
    }

    public function snapshot(): Snapshot
    {
        return new Snapshot(
            $this->stopwatch->current(),
            $this->memoryInspector->readMemoryUsage(),
            $this->memoryInspector->readPeakMemoryUsage(),
            $this->garbageCollectorInspector->readStatus(),
        );
    }
}
