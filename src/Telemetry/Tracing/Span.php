<?php

declare(strict_types=1);

namespace Infection\Telemetry\Tracing;

use Infection\Telemetry\Metric\Memory\MemoryUsage;
use Infection\Telemetry\Metric\Snapshot;
use Infection\Telemetry\Metric\Time\Duration;

final readonly class Span
{
    public function __construct(
        public string $id,
        public string $scopeId,
        public string $scope,
        public Snapshot $start,
        public Snapshot $end,
        public array $children,
    ) {
    }

    public function getDuration(): Duration
    {
        return $this->end->time->getDuration(
            $this->start->time,
        );
    }

    public function getMemoryUsage(): MemoryUsage
    {
        return $this->end->memoryUsage->diff(
            $this->start->memoryUsage,
        );
    }
}
