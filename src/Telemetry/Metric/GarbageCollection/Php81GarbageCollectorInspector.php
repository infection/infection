<?php declare(strict_types=1);
namespace Infection\Telemetry\Metric\GarbageCollection;

use function gc_status;

final class Php81GarbageCollectorInspector implements GarbageCollectorInspector
{
    public function readStatus(): GarbageCollectorStatus
    {
        $status = gc_status();

        return new GarbageCollectorStatus(
            $status['runs'],
            $status['collected'],
            $status['threshold'],
            $status['roots'],
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
        );
    }
}
