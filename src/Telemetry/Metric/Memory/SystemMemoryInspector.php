<?php

declare(strict_types=1);

namespace Infection\Telemetry\Metric\Memory;

use function memory_get_peak_usage;
use function memory_get_usage;

final class SystemMemoryInspector implements MemoryInspector
{
    public function readMemoryUsage(): MemoryUsage
    {
        return MemoryUsage::fromBytes(memory_get_usage());
    }

    public function readPeakMemoryUsage(): MemoryUsage
    {
        return MemoryUsage::fromBytes(memory_get_peak_usage());
    }
}