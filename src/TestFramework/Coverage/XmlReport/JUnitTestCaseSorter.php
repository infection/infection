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

namespace Infection\TestFramework\Coverage\XmlReport;

use function assert;
use function in_array;
use Infection\TestFramework\Coverage\CoverageLineData;
use function is_string;

/**
 * @internal
 */
final class JUnitTestCaseSorter
{
    /**
     * @param CoverageLineData[] $coverageTestCases
     *
     * @return string[]
     */
    public function getUniqueSortedFileNames(array $coverageTestCases): array
    {
        $uniqueCoverageTests = $this->uniqueByTestFile($coverageTestCases);

        // sort tests to run the fastest first
        usort(
            $uniqueCoverageTests,
            static function (CoverageLineData $a, CoverageLineData $b) {
                return $a->time <=> $b->time;
            }
        );

        return array_map(
            static function (CoverageLineData $coverageLineData): string {
                assert(is_string($coverageLineData->testFilePath));

                return $coverageLineData->testFilePath;
            },
            $uniqueCoverageTests
        );
    }

    /**
     * @param CoverageLineData[] $coverageTestCases
     *
     * @return CoverageLineData[]
     */
    private function uniqueByTestFile(array $coverageTestCases): array
    {
        $usedFileNames = [];
        $uniqueTests = [];

        foreach ($coverageTestCases as $coverageTestCase) {
            if (!in_array($coverageTestCase->testFilePath, $usedFileNames, true)) {
                $uniqueTests[] = $coverageTestCase;
                $usedFileNames[] = $coverageTestCase->testFilePath;
            }
        }

        return $uniqueTests;
    }
}
