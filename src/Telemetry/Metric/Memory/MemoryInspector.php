<?php

declare(strict_types=1);

namespace Infection\Telemetry\Metric\Memory;

interface MemoryInspector
{
    public function readMemoryUsage(): MemoryUsage;

    public function readPeakMemoryUsage(): MemoryUsage;
}
