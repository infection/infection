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

namespace Infection\TestFramework\Coverage\JUnit;

use function array_fill;
use function array_key_exists;
use function array_key_last;
use function count;
use function current;
use Infection\AbstractTestFramework\Coverage\TestLocation;
use function Safe\array_combine;
use function Safe\usort;

/**
 * @internal
 */
final class JUnitTestCaseSorter
{
    /**
     * Millisecond buckets. Exposed for testing purposes.
     *
     * @var int[]
     */
    public const BUCKETS = [
        4, // e^(x/C) basically
        5,
        7,
        10,
        14,
        20,
        28,
        39,
        55,
        76,
        106,
        148,
        207,
        289,
        403,
        563,
        786,
        1097,
        1530,
        2136,
        2981,
        4160,
        5806, // five seconds, give or take
    ];

    /**
     * For 23 buckets QS becomes theoretically less efficient on average at and after 15 elements.
     * Exposed for testing purposes.
     */
    public const USE_BUCKET_SORT_AFTER = 15;

    /**
     * Milliseconds in a second. То stop Infection mutating a constant;
     */
    private const MS_IN_S = 1000;

    /**
     * @param TestLocation[] $tests
     *
     * @return string[]
     */
    public function getUniqueSortedFileNames(array $tests): iterable
    {
        $uniqueTestLocations = $this->uniqueByTestFile($tests);

        if (count($uniqueTestLocations) === 1) {
            // Around 5% speed up compared to when without this optimization.
            /** @var TestLocation $testLocation */
            $testLocation = current($uniqueTestLocations);

            /** @var string $filePath */
            $filePath = $testLocation->getFilePath();

            yield $filePath;

            return;
        }

        /*
         * Two tests per file are also very frequent. Yet it doesn't make sense
         * to sort them by hand: usort does that just as good.
         */

        // sort tests to run the fastest first
        foreach (self::sort($uniqueTestLocations) as $testLocation) {
            $filePath = $testLocation->getFilePath();

            if ($filePath !== null) {
                yield $filePath;
            }
        }
    }

    /**
     * Sorts tests to run the fastest first. Exposed for benchmarking purposes.
     *
     * @param TestLocation[] $uniqueTestLocations
     *
     * @return iterable<TestLocation>
     */
    public static function sort(array &$uniqueTestLocations): iterable
    {
        if (count($uniqueTestLocations) < self::USE_BUCKET_SORT_AFTER) {
            usort(
                $uniqueTestLocations,
                static function (TestLocation $a, TestLocation $b) {
                    return $a->getExecutionTime() <=> $b->getExecutionTime();
                }
            );

            return $uniqueTestLocations;
        }

        return self::bucketSort($uniqueTestLocations);
    }

    /**
     * @param TestLocation[] $uniqueTestLocations
     *
     * @return iterable<TestLocation>
     */
    private static function bucketSort(array $uniqueTestLocations): iterable
    {
        $buckets = array_combine(self::BUCKETS, array_fill(0, count(self::BUCKETS), []));
        $catchAllBucket = array_key_last($buckets);

        foreach ($uniqueTestLocations as $location) {
            /** @var TestLocation $location */
            $msTime = (int) $location->getExecutionTime() * self::MS_IN_S;

            foreach (self::BUCKETS as $bucketTime) {
                if ($msTime < $bucketTime) {
                    $buckets[$bucketTime][] = $location;

                    continue 2;
                }
            }

            $buckets[$catchAllBucket][] = $location;
        }

        // Sort the slowest tests, if any
        if ($buckets[$catchAllBucket] !== []) {
            usort(
                $buckets[$catchAllBucket],
                static function (TestLocation $a, TestLocation $b) {
                    return $a->getExecutionTime() <=> $b->getExecutionTime();
                }
            );
        }

        foreach ($buckets as $bucket) {
            if ($bucket !== []) {
                yield from $bucket;
            }
        }
    }

    /**
     * @param TestLocation[] $testLocations
     *
     * @return TestLocation[]
     */
    private function uniqueByTestFile(array $testLocations): array
    {
        // It is faster to have two arrays, and discard one later.
        $usedFileNames = [];
        $uniqueTests = [];

        foreach ($testLocations as $testLocation) {
            $filePath = $testLocation->getFilePath();

            // To skip this check later on
            if ($filePath === null) {
                continue;
            }

            // isset() is 20% faster than array_key_exists()
            if (!isset($usedFileNames[$filePath])) {
                $uniqueTests[] = $testLocation;
                $usedFileNames[$filePath] = true;
            }
        }

        return $uniqueTests;
    }
}
