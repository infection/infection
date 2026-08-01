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

namespace Infection\TestFramework\Tracing;

use Infection\AbstractTestFramework\Coverage\TestLocation;
use Infection\CannotBeInstantiated;
use function ksort;

/**
 * @internal
 */
final readonly class TestLocationBucketSorter
{
    use CannotBeInstantiated;

    /**
     * Pre-sort first buckets, optimistically assuming that most projects
     * won't have tests longer than a second.
     */
    private const array INIT_BUCKETS = [
        0 => [],
        1 => [],
        2 => [],
        3 => [],
        4 => [],
        5 => [],
        6 => [],
        7 => [],
    ];

    /**
     * Fixed-point scale applied to the execution time (in seconds) before it
     * is reduced to a bucket index, so truncating to int does not throw away
     * sub-eighth-of-a-second precision.
     */
    private const int TIME_TO_FIXED_POINT_SCALE = 1024;

    /**
     * Shifts a TIME_TO_FIXED_POINT_SCALE-scaled time down to an eighth-of-a-second
     * bucket (1024 >> 7 = 8 buckets per second).
     */
    private const int BUCKET_SHIFT = 7;

    /**
     * Bucket index, in eighth-of-a-second units, above which precision drops
     * to whole 4-second buckets: 32 eighths of a second is 4 seconds.
     */
    private const int COARSE_BUCKET_THRESHOLD = 32;

    /**
     * Clears the low bits of a bucket index, widening its granularity from
     * an eighth of a second to 4 seconds (1 << 5 eighths of a second).
     */
    private const int COARSE_BUCKET_SHIFT = 5;

    /**
     * Sorts tests to run the fastest first. Exposed for benchmarking purposes.
     *
     * @param TestLocation[] $uniqueTestLocations
     *
     * @return iterable<TestLocation>
     */
    public static function bucketSort(array $uniqueTestLocations): iterable
    {
        $buckets = self::INIT_BUCKETS;

        foreach ($uniqueTestLocations as $location) {
            // @codeCoverageIgnoreStart
            // This is a very hot path. Factoring here another method just to test this math may not be as good idea.

            // Quick drop off lower bits, reducing precision to 8th of a second
            $msTime = (int) (($location->getExecutionTime() ?? 0) * self::TIME_TO_FIXED_POINT_SCALE) >> self::BUCKET_SHIFT;

            // For anything above 4 seconds reduce precision to 4 seconds
            if ($msTime > self::COARSE_BUCKET_THRESHOLD) {
                $msTime = $msTime >> self::COARSE_BUCKET_SHIFT << self::COARSE_BUCKET_SHIFT;
            }
            // @codeCoverageIgnoreEnd

            $buckets[$msTime][] = $location;
        }

        ksort($buckets);

        foreach ($buckets as $bucket) {
            yield from $bucket;
        }
    }
}
