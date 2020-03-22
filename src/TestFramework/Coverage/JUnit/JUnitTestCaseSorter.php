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
use function current;
use Infection\AbstractTestFramework\Coverage\TestLocation;
use function Safe\usort;

/**
 * @internal
 */
final class JUnitTestCaseSorter
{
    /**
     * @param TestLocation[] $tests
     *
     * @return iterable<string>
     */
    public function getUniqueSortedFileNames(array $tests): iterable
    {
        $uniqueTestLocations = $this->uniqueByTestFile($tests);

        if (count($uniqueTestLocations) === 1) {
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

            yield $filePath;

            return;
        }

        /*
         * Two tests per file are also very frequent. Yet it doesn't make sense
         * to sort them by hand: usort does that just as good.
         */

        // Sort tests to run the fastest first.
        usort(
            $uniqueTestLocations,
            static function (TestLocation $a, TestLocation $b) {
                return $a->getExecutionTime() <=> $b->getExecutionTime();
            }
        );

        foreach ($uniqueTestLocations as $testLocation) {
            yield $testLocation->getFilePath();
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

            // isset() is 20% faster than array_key_exists() as of PHP 7.3
            if (!isset($usedFileNames[$filePath])) {
                $uniqueTests[] = $testLocation;
                $usedFileNames[$filePath] = true;
            }
        }

        return $uniqueTests;
    }
}
