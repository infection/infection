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

use function count;
use function current;
use Infection\AbstractTestFramework\Coverage\TestLocation;
use function usort;

/**
 * @internal
 */
final class JUnitTestCaseSorter
{
    /**
     * Expected average number of buckets. Exposed for testing purposes.
     *
     * @var int
     */
    public const BUCKETS_COUNT = 25;

    /**
     * For 25 buckets QS becomes theoretically less efficient on average at and after 15 elements.
     * Exposed for testing purposes.
     */
    public const USE_BUCKET_SORT_AFTER = 15;

    /**
     * @param TestLocation[] $tests
     *
     * @return iterable<string>
     */
    public function getUniqueSortedFileNames(array $tests): iterable
    {
        $uniqueTestLocations = $this->uniqueByTestFile($tests);

        $numberOfTestLocation = count($uniqueTestLocations);

        if ($numberOfTestLocation === 1) {
            // Around 5% speed up compared to when without this optimization.
            /** @var TestLocation $testLocation */
            $testLocation = current($uniqueTestLocations);

            /*
             * TestLocation gets its file path and timings from TestFileTimeData.
             * Path for TestFileTimeData is not optional. It is never a null.
             * Therefore we don't need to make any type checks here.
             */

            /** @var string $filePath */
            $filePath = $testLocation->getFilePath();

            return [$filePath];
        }

        /*
         * We need to sort tests to run the fastest first.
         *
         * Two tests per file are also very frequent. Yet it doesn't make sense
         * to sort them by hand: usort does that just as good.
         */

        if ($numberOfTestLocation < self::USE_BUCKET_SORT_AFTER) {
            usort(
                $uniqueTestLocations,
                static fn (TestLocation $a, TestLocation $b): int => $a->getExecutionTime() <=> $b->getExecutionTime(),
            );

            return self::sortedLocationsGenerator($uniqueTestLocations);
        }

        /*
         * For large number of tests use a more efficient algorithm.
         */
        return self::sortedLocationsGenerator(
            TestLocationBucketSorter::bucketSort($uniqueTestLocations),
        );
    }

    /**
     * @param iterable<TestLocation> $sortedTestLocations
     *
     * @return iterable<string>
     */
    private static function sortedLocationsGenerator(iterable $sortedTestLocations): iterable
    {
        foreach ($sortedTestLocations as $testLocation) {
            /** @var string $filePath */
            $filePath = $testLocation->getFilePath();

            yield $filePath;
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
            /** @var string $filePath */
            $filePath = $testLocation->getFilePath();

            // isset() is 20% faster than array_key_exists() as of PHP 7.3
            if (!isset($usedFileNames[$filePath])) {
                $uniqueTests[] = $testLocation;
                $usedFileNames[$filePath] = true;
            }
        }

        return $uniqueTests;
    }
}
