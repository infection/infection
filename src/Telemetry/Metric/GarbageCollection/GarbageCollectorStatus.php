<?php declare(strict_types=1);
namespace Infection\Telemetry\Metric\GarbageCollection;

final readonly class GarbageCollectorStatus
{
    public function __construct(
        public int $runs,
        public int $collected,
        public int $threshold,
        public int $roots,
        // TODO: make it non-nullable when we make Infection require PHP 8.3+.
        //  meanwhile null=info not available.
        public ?float $applicationTime,
        public ?float $collectorTime,
        public ?float $destructorTime,
        public ?float $freeTime,
        public ?bool $running,
        public ?bool $protected,
        public ?bool $full,
        public ?int $bufferSize,
    ) {
    }
}
