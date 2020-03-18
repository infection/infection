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

use function array_key_exists;
use function count;
use function current;
use Infection\AbstractTestFramework\Coverage\TestLocation;
use function Safe\ksort;
use function Safe\usort;

/**
 * @internal
 */
final class JUnitTestCaseSorter
{
    /**
     * Expected average number of buckets. Exposed for testing purposes.
     *
     * @var int[]
     */
    public const BUCKETS_COUNT = 25;

    /**
     * For 25 buckets QS becomes theoretically less efficient on average at and after 15 elements.
     * Exposed for testing purposes.
     */
    public const USE_BUCKET_SORT_AFTER = 15;

    /**
     * Milliseconds in a second. То stop Infection from mutating a constant;
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
            /** @var string $filePath */
            $filePath = $testLocation->getFilePath();

            yield $filePath;
        }
    }

    /**
     * Sorts tests to run the fastest first. Exposed for benchmarking purposes.
     *
     * @param TestLocation[] $uniqueTestLocations
     *
     * @return iterable<TestLocation>
     */
    public static function sort(array $uniqueTestLocations): iterable
    {
        // Pre-sort first buckets, optimistically assuming that
        // most projects won't have tests longer than a second
        $buckets = [
            0 => [],
            1 => [],
            2 => [],
            3 => [],
            4 => [],
            5 => [],
            6 => [],
            7 => [],
        ];

        foreach ($uniqueTestLocations as $location) {
            // Quicky drop off lower bits, reducing precision to 8th of a second
            $msTime = $location->getExecutionTime() * 1024 >> 7; // * 1024 / 128

            // For anything above 4 seconds reduce precision to 4 seconds
            if ($msTime > 32) {
                $msTime = $msTime >> 5 << 5; // 7 + 5 = 12 bits
            }

            $buckets[$msTime][] = $location;
        }

        ksort($buckets);

        foreach ($buckets as $bucket) {
            foreach ($bucket as $value) {
                yield $value;
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
            /** @var string $filePath */
            $filePath = $testLocation->getFilePath();

            // isset() is 20% faster than array_key_exists()
            if (!isset($usedFileNames[$filePath])) {
                $uniqueTests[] = $testLocation;
                $usedFileNames[$filePath] = true;
            }
        }

        return $uniqueTests;
    }
}
