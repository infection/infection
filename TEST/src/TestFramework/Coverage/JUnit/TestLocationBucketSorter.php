<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\TestFramework\Coverage\JUnit;

use _HumbugBox9658796bb9f0\Infection\AbstractTestFramework\Coverage\TestLocation;
use function _HumbugBox9658796bb9f0\Safe\ksort;
final class TestLocationBucketSorter
{
    private const INIT_BUCKETS = [0 => [], 1 => [], 2 => [], 3 => [], 4 => [], 5 => [], 6 => [], 7 => []];
    private function __construct()
    {
    }
    public static function bucketSort(array $uniqueTestLocations) : iterable
    {
        $buckets = self::INIT_BUCKETS;
        foreach ($uniqueTestLocations as $location) {
            $msTime = (int) (($location->getExecutionTime() ?? 0) * 1024) >> 7;
            if ($msTime > 32) {
                $msTime = $msTime >> 5 << 5;
            }
            $buckets[$msTime][] = $location;
        }
        ksort($buckets);
        foreach ($buckets as $bucket) {
            yield from $bucket;
        }
    }
}
