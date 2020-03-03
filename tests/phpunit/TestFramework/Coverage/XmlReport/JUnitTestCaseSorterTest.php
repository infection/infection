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

namespace Infection\Tests\TestFramework\Coverage\XmlReport;

use Infection\AbstractTestFramework\Coverage\CoverageLineData;
use Infection\TestFramework\Coverage\XmlReport\JUnitTestCaseSorter;
use PHPUnit\Framework\TestCase;

final class JUnitTestCaseSorterTest extends TestCase
{
    public function test_it_returns_first_file_name_if_there_is_only_one(): void
    {
        $coverageTestCases = [
            CoverageLineData::with(
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
            CoverageLineData::with(
                'testMethod1',
                '/path/to/test-file-1',
                0.000234
            ),
            CoverageLineData::with(
                'testMethod2',
                '/path/to/test-file-2',
                0.600221
            ),
            CoverageLineData::with(
                'testMethod3_1',
                '/path/to/test-file-3',
                0.000022
            ),
            CoverageLineData::with(
                'testMethod3_2',
                '/path/to/test-file-3',
                0.010022
            ),
        ];

        $sorter = new JUnitTestCaseSorter();

        $uniqueSortedFileNames = iterator_to_array(
            $sorter->getUniqueSortedFileNames($coverageTestCases)
        );

        $this->assertCount(3, $uniqueSortedFileNames);
        $this->assertSame('/path/to/test-file-3', $uniqueSortedFileNames[0]);
    }
}
