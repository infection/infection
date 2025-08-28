<?php declare(strict_types=1);
namespace Infection\Telemetry\Metric\GarbageCollection;

interface GarbageCollectorInspector
{
    public function readStatus(): GarbageCollectorStatus;
}
