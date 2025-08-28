<?php declare(strict_types=1);
namespace Infection\Telemetry\GarbageCollection;

namespace Infection\Telemetry\Metric\GarbageCollection;

final class Php83GarbageCollectorInspector implements GarbageCollectorInspector
{
    public function readStatus(): GarbageCollectorStatus
    {
        $status = gc_status();

        return new GarbageCollectorStatus(
            $status['runs'],
            $status['collected'],
            $status['threshold'],
            $status['roots'],
            $status['application_time'],
            $status['collector_time'],
            $status['destructor_time'],
            $status['free_time'],
            $status['running'],
            $status['protected'],
            $status['full'],
            $status['buffer_size'],
        );
    }
}
