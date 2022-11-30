<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Metrics;

use function count;
use _HumbugBox9658796bb9f0\Infection\Mutant\DetectionStatus;
final class FilteringResultsCollectorFactory
{
    public function __construct(private TargetDetectionStatusesProvider $statusesProvider)
    {
    }
    public function create(Collector $targetCollector) : ?Collector
    {
        $targetDetectionStatuses = $this->statusesProvider->get();
        if ($targetDetectionStatuses === []) {
            return null;
        }
        if (count($targetDetectionStatuses) === count(DetectionStatus::ALL)) {
            return $targetCollector;
        }
        return new FilteringResultsCollector($targetCollector, $targetDetectionStatuses);
    }
}
