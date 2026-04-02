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
use function log;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use function Pipeline\take;

#[CoversClass(JUnitTestCaseSorter::class)]
final class JUnitTestCaseSorterTest extends TestCase
{
    /**
     * @param TestLocation[] $testLocations
     * @param string[] $expected
     */
    #[DataProvider('scenarioProvider')]
    public function test_it_resolves_the_unique_test_locations_ordered_from_fastest_to_slowest(
        array $testLocations,
        array $expected,
    ): void {
        $sorter = new JUnitTestCaseSorter();

        $actual = take($sorter->getUniqueSortedFileNames($testLocations))->toList();

        $this->assertSame($expected, $actual);
    }

    public static function scenarioProvider(): iterable
    {
        yield [
            [
                new TestLocation(
                    'testMethod1',
                    '/path/to/test-file-1',
                    0.000234,
                ),
            ],
            ['/path/to/test-file-1'],
        ];

        yield [
            [
                new TestLocation(
                    'testMethod1',
                    '/path/to/test-file-1',
                    0.500234,
                ),
                new TestLocation(
                    'testMethod2',
                    '/path/to/test-file-2',
                    0.900221,
                ),
                new TestLocation(
                    'testMethod3_1',
                    '/path/to/test-file-3',
                    0.000022,
                ),
                new TestLocation(
                    'testMethod3_2',
                    '/path/to/test-file-4',
                    0.210022,
                ),
            ],
            [
                '/path/to/test-file-3',
                '/path/to/test-file-4',
                '/path/to/test-file-1',
                '/path/to/test-file-2',
            ],
        ];
    }

    public function test_it_has_correct_constants_for_bucket_sort(): void
    {
        $this->assertLessThan(
            // Quicksort's average O(n log n)
            JUnitTestCaseSorter::USE_BUCKET_SORT_AFTER * log(JUnitTestCaseSorter::USE_BUCKET_SORT_AFTER),
            // Bucket Sort's average O(n + k)
            JUnitTestCaseSorter::USE_BUCKET_SORT_AFTER + JUnitTestCaseSorter::BUCKETS_COUNT,
        );
    }
}
