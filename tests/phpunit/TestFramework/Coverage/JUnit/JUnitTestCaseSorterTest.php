<?php
/**
 * This code is licensed under the BSD 3-Clause License.
 *
 * Copyright (c) 2017, Maks Rafalko
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * * Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 *
 * * Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 * * Neither the name of the copyright holder nor the names of its
 *   contributors may be used to endorse or promote products derived from
 *   this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

declare(strict_types=1);

namespace Infection\Tests\TestFramework\Coverage\JUnit;

use Infection\AbstractTestFramework\Coverage\TestLocation;
use Infection\TestFramework\Coverage\JUnit\JUnitTestCaseSorter;
use Infection\Tests\Fixtures\TestFramework\PhpUnit\Coverage\JUnitTimes;
use function iterator_to_array;
use function log;
use function microtime;
use PHPUnit\Framework\TestCase;
use function Safe\usort;

final class JUnitTestCaseSorterTest extends TestCase
{
    public function test_it_returns_first_file_name_if_there_is_only_one(): void
    {
        $coverageTestCases = [
            new TestLocation(
                'testMethod1',
                '/path/to/test-file-1',
                0.000234
            ),
        ];

        $sorter = new JUnitTestCaseSorter();

        $uniqueSortedFileNames = iterator_to_array(
            $sorter->getUniqueSortedFileNames($coverageTestCases)
        );

        $this->assertCount(1, $uniqueSortedFileNames);
        $this->assertSame('/path/to/test-file-1', $uniqueSortedFileNames[0]);
    }

    public function test_it_returns_unique_and_sorted_by_time_test_cases(): void
    {
        $coverageTestCases = [
            new TestLocation(
                'testMethod1',
                '/path/to/test-file-1',
                0.000234
            ),
            new TestLocation(
                'testMethod2',
                '/path/to/test-file-2',
                0.600221
            ),
            new TestLocation(
                'testMethod3_1',
                '/path/to/test-file-3',
                0.000022
            ),
            new TestLocation(
                'testMethod3_2',
                '/path/to/test-file-3',
                0.010022
            ),
        ];

        $sorter = new JUnitTestCaseSorter();

        $uniqueSortedFileNames = iterator_to_array(
            $sorter->getUniqueSortedFileNames($coverageTestCases),
            true
        );

        $this->assertCount(3, $uniqueSortedFileNames);
        $this->assertSame('/path/to/test-file-3', $uniqueSortedFileNames[0]);
    }

    public function test_it_has_correct_constants_for_bucket_sort(): void
    {
        $this->assertLessThan(
            // Quicksort's average O(n log n)
            JUnitTestCaseSorter::USE_BUCKET_SORT_AFTER * log(JUnitTestCaseSorter::USE_BUCKET_SORT_AFTER),
            // Bucket Sort's average O(n + k)
            JUnitTestCaseSorter::USE_BUCKET_SORT_AFTER + JUnitTestCaseSorter::BUCKETS_COUNT
        );
    }

    public function test_it_sorts_correctly(): void
    {
        $uniqueTestLocations = self::makeTestLocationsArray();

        // Sanity check
        $this->assertNotTrue(self::orderConstraintsValid($uniqueTestLocations));

        $sortedTestLocations = iterator_to_array(JUnitTestCaseSorter::sort($uniqueTestLocations));
        $this->assertTrue(self::orderConstraintsValid($sortedTestLocations), 'Bucket sort failed order check');

        // Another sanity check
        $sortedTestLocations = self::quicksort($uniqueTestLocations);
        $this->assertTrue(self::orderConstraintsValid($uniqueTestLocations), 'Quicksort failed order check');
    }

    public function test_it_sorts_faster_than_quicksort(): void
    {
        $uniqueTestLocations = self::makeTestLocationsArray();

        // Sanity check
        $this->assertNotTrue(self::orderConstraintsValid($uniqueTestLocations));

        $tries = 100;

        // Benchmark bucket sort
        $totalBucketSort = 0;

        for ($i = 0; $i < $tries; ++$i) {
            $start = microtime(true);
            iterator_to_array(JUnitTestCaseSorter::sort($uniqueTestLocations));
            $totalBucketSort += microtime(true) - $start;
        }

        // Benchmark quicksort
        $totalQuickSort = 0;

        for ($i = 0; $i < $tries; ++$i) {
            $start = microtime(true);
            self::quicksort($uniqueTestLocations);
            $totalQuickSort += microtime(true) - $start;
        }

        $this->assertLessThan($totalQuickSort, $totalBucketSort);
    }

    private static function makeTestLocationsArray(): array
    {
        return array_map(
            static function (float $executionTime): TestLocation {
                return new TestLocation('', '', $executionTime);
            },
            JUnitTimes::JUNIT_TIMES
        );
    }

    private static function quicksort($uniqueTestLocations): array
    {
        usort(
            $uniqueTestLocations,
            static function (TestLocation $a, TestLocation $b) {
                return $a->getExecutionTime() <=> $b->getExecutionTime();
            }
        );

        return $uniqueTestLocations;
    }

    /**
     * We assume locations should be ordered within an order of magnitude.
     *
     * @return bool
     */
    private static function orderConstraintsValid(array $sortedTestLocations)
    {
        // Minimal precision: there's no sort below this number
        $minimalPrecisionTime = 0.125;
        $lastSeenTime = null;

        foreach ($sortedTestLocations as $location) {
            /* @var TestLocation $location */
            if ($lastSeenTime === null) {
                // Don't enable checks unless a to-be-sorted value is seen
                if ($location->getExecutionTime() > $minimalPrecisionTime) {
                    $lastSeenTime = $location->getExecutionTime();
                }

                continue;
            }

            if ($lastSeenTime / $location->getExecutionTime() > 10.) {
                return false;
            }

            $lastSeenTime = $location->getExecutionTime();
        }

        return true;
    }
}
