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

use function abs;
use function array_map;
use function array_reverse;
use function array_slice;
use ArrayIterator;
use function extension_loaded;
use Infection\AbstractTestFramework\Coverage\TestLocation;
use Infection\TestFramework\Coverage\JUnit\JUnitTestCaseSorter;
use Infection\TestFramework\Coverage\JUnit\TestLocationBucketSorter;
use Infection\Tests\Fixtures\TestFramework\PhpUnit\Coverage\JUnitTimes;
use function iterator_to_array;
use function log;
use function microtime;
use const PHP_SAPI;
use PHPUnit\Framework\TestCase;
use function Safe\usort;

/**
 * Tagged as integration because it can be quite slow.
 *
 * @group integration
 */
final class TestLocationBucketSorterTest extends TestCase
{
    /**
     * Used for floating point comparisons.
     *
     * @var float
     */
    private const EPSILON = 0.001;

    public function test_it_sorts(): void
    {
        $testLocation = new TestLocation('', '', 0.0);

        $sortedTestLocations = iterator_to_array(
            TestLocationBucketSorter::bucketSort([$testLocation]),
            false
        );

        $this->assertSame([$testLocation], $sortedTestLocations);
    }

    public function test_it_detects_precision_boundary(): void
    {
        $testLocations = [
            new TestLocation('', '', 0.124),
            new TestLocation('', '', 0.125),
            new TestLocation('', '', 0.499),
            new TestLocation('', '', 0.500),
            new TestLocation('', '', 1.499),
            new TestLocation('', '', 1.500),
            new TestLocation('', '', 3.999),
            new TestLocation('', '', 4.000),
        ];

        $sortedTestLocations = iterator_to_array(
            TestLocationBucketSorter::bucketSort(array_reverse($testLocations)),
            false
        );

        $this->assertSame($testLocations, $sortedTestLocations);
    }

    public function test_it_detects_second_precision_boundary(): void
    {
        $testLocation1 = new TestLocation('', '', 0.124);
        $testLocation2 = new TestLocation('', '', 0.125);

        $sortedTestLocations = iterator_to_array(
            TestLocationBucketSorter::bucketSort([$testLocation2, $testLocation1]),
            false
            );

        $this->assertSame([$testLocation1, $testLocation2], $sortedTestLocations);
    }

    /**
     * @dataProvider locationsArrayProvider
     *
     * @param ArrayIterator<TestLocation> $uniqueTestLocations
     */
    public function test_it_sorts_correctly(ArrayIterator $uniqueTestLocations): void
    {
        $uniqueTestLocations = $uniqueTestLocations->getArrayCopy();

        $sortedTestLocations = iterator_to_array(
            TestLocationBucketSorter::bucketSort($uniqueTestLocations),
            false
        );

        $this->assertTrue(
            self::areConstraintsOrderValid($sortedTestLocations),
            'Bucket sort failed order check'
        );
    }

    /**
     * Sanity check
     *
     * @dataProvider locationsArrayProvider
     *
     * @param ArrayIterator<TestLocation> $uniqueTestLocations
     */
    public function test_quicksort_sorts_correctly(ArrayIterator $uniqueTestLocations): void
    {
        $uniqueTestLocations = $uniqueTestLocations->getArrayCopy();

        self::quicksort($uniqueTestLocations);

        $this->assertTrue(
            self::areConstraintsOrderValid($uniqueTestLocations),
            'Quicksort failed order check'
        );
    }

    /**
     * @dataProvider locationsArrayProvider
     *
     * @param ArrayIterator<TestLocation> $uniqueTestLocations
     */
    public function test_it_sorts_faster_than_quicksort(ArrayIterator $uniqueTestLocations): void
    {
        if (extension_loaded('xdebug') || PHP_SAPI === 'phpdbg') {
            $this->markTestSkipped('Benchmarks under xdebug or phpdbg are brittle');
        }

        $uniqueTestLocations = $uniqueTestLocations->getArrayCopy();

        if (self::areConstraintsOrderValid($uniqueTestLocations)) {
            // Ignore silently as to not pollute to the log.
            $this->addToAssertionCount(1);

            return;
        }

        $tries = 100;

        // Benchmark bucket sort
        $totalBucketSort = 0;

        for ($i = 0; $i < $tries; ++$i) {
            $start = microtime(true);
            $dummy = iterator_to_array(
                TestLocationBucketSorter::bucketSort($uniqueTestLocations),
                false
            );
            $totalBucketSort += microtime(true) - $start;
        }

        // Benchmark quicksort
        $totalQuickSort = 0;

        for ($i = 0; $i < $tries; ++$i) {
            $start = microtime(true);
            // Updates by reference
            $locationsCopy = $uniqueTestLocations;
            self::quicksort($locationsCopy);
            $totalQuickSort += microtime(true) - $start;
        }

        $this->assertGreaterThanOrEqual(self::EPSILON, self::getRelativeError($totalQuickSort, $totalBucketSort));
    }

    public static function locationsArrayProvider(): iterable
    {
        $locations = array_map(
            static function (float $executionTime): TestLocation {
                return new TestLocation('', '', $executionTime);
            },
            JUnitTimes::JUNIT_TIMES
        );

        yield 'Ten times the minimal amount of locations' => [new ArrayIterator(array_slice($locations, 0, JUnitTestCaseSorter::USE_BUCKET_SORT_AFTER * 10))];

        yield 'All locations' => [new ArrayIterator($locations)];
    }

    /**
     * Finds relative error.
     *
     * @see https://floating-point-gui.de/errors/comparison/
     * @see https://stackoverflow.com/questions/4915462/how-should-i-do-floating-point-comparison
     */
    private static function getRelativeError(float $a, float $b): float
    {
        // We do not expect A or B to be extremely small or large: these are edge cases,
        // and they will need special handling which we avoid simplicity sake.
        self::assertGreaterThan(self::EPSILON, abs($a));
        self::assertGreaterThan(self::EPSILON, abs($b));

        return abs($a - $b) / (abs($a) + abs($b));
    }

    private static function quicksort(&$uniqueTestLocations): void
    {
        usort(
            $uniqueTestLocations,
            static function (TestLocation $a, TestLocation $b): int {
                return $a->getExecutionTime() <=> $b->getExecutionTime();
            }
        );
    }

    /**
     * We assume locations should be ordered within an order of magnitude.
     *
     * @param TestLocation[] $sortedTestLocations
     */
    private static function areConstraintsOrderValid(array $sortedTestLocations): bool
    {
        // Minimal precision: there's no sort below this number
        $minimalPrecisionTime = 0.125;
        $lastSeenTime = null;

        foreach ($sortedTestLocations as $location) {
            if ($lastSeenTime === null) {
                // Don't enable checks unless a to-be-sorted value is seen
                if ($location->getExecutionTime() > $minimalPrecisionTime) {
                    $lastSeenTime = $location->getExecutionTime();
                }

                continue;
            }

            // Previously seen time must not be 10 times as large as current
            if ($lastSeenTime / $location->getExecutionTime() > 10.) {
                return false;
            }

            $lastSeenTime = $location->getExecutionTime();
        }

        return true;
    }
}
