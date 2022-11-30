<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\TestFramework\Codeception\Coverage;

use function array_map;
use function assert;
use function in_array;
use _HumbugBox9658796bb9f0\Infection\AbstractTestFramework\Coverage\TestLocation;
use function is_string;
use function usort;
final class JUnitTestCaseSorter
{
    public function getUniqueSortedFileNames(array $tests) : array
    {
        $uniqueCoverageTests = $this->uniqueByTestFile($tests);
        usort($uniqueCoverageTests, static function (TestLocation $a, TestLocation $b) : int {
            return $a->getExecutionTime() <=> $b->getExecutionTime();
        });
        return array_map(static function (TestLocation $coverageLineData) : string {
            $filePath = $coverageLineData->getFilePath();
            assert(is_string($filePath));
            return $filePath;
        }, $uniqueCoverageTests);
    }
    private function uniqueByTestFile(array $tests) : array
    {
        $usedFileNames = [];
        $uniqueTests = [];
        foreach ($tests as $test) {
            $filePath = $test->getFilePath();
            if (!in_array($filePath, $usedFileNames, \true)) {
                $uniqueTests[] = $test;
                $usedFileNames[] = $filePath;
            }
        }
        return $uniqueTests;
    }
}
