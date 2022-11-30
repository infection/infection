<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\TestFramework\Coverage\JUnit;

use function array_key_exists;
use function count;
use function current;
use _HumbugBox9658796bb9f0\Infection\AbstractTestFramework\Coverage\TestLocation;
use function _HumbugBox9658796bb9f0\Safe\usort;
final class JUnitTestCaseSorter
{
    public const BUCKETS_COUNT = 25;
    public const USE_BUCKET_SORT_AFTER = 15;
    public function getUniqueSortedFileNames(array $tests) : iterable
    {
        $uniqueTestLocations = $this->uniqueByTestFile($tests);
        $numberOfTestLocation = count($uniqueTestLocations);
        if ($numberOfTestLocation === 1) {
            $testLocation = current($uniqueTestLocations);
            $filePath = $testLocation->getFilePath();
            return [$filePath];
        }
        if ($numberOfTestLocation < self::USE_BUCKET_SORT_AFTER) {
            usort($uniqueTestLocations, static fn(TestLocation $a, TestLocation $b): int => $a->getExecutionTime() <=> $b->getExecutionTime());
            return self::sortedLocationsGenerator($uniqueTestLocations);
        }
        return self::sortedLocationsGenerator(TestLocationBucketSorter::bucketSort($uniqueTestLocations));
    }
    private static function sortedLocationsGenerator(iterable $sortedTestLocations) : iterable
    {
        foreach ($sortedTestLocations as $testLocation) {
            $filePath = $testLocation->getFilePath();
            (yield $filePath);
        }
    }
    private function uniqueByTestFile(array $testLocations) : array
    {
        $usedFileNames = [];
        $uniqueTests = [];
        foreach ($testLocations as $testLocation) {
            $filePath = $testLocation->getFilePath();
            if (!isset($usedFileNames[$filePath])) {
                $uniqueTests[] = $testLocation;
                $usedFileNames[$filePath] = \true;
            }
        }
        return $uniqueTests;
    }
}
