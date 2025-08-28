<?php

declare(strict_types=1);

namespace Infection\Telemetry\Tracing;

use Infection\Telemetry\Metric\Snapshot;

final readonly class Span
{
    public function __construct(
        public string $id,
        public string $scope,
        public Snapshot $start,
        public Snapshot $end,
        public array $children,
    ) {
    }
}
