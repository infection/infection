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
use function array_sum;
use Infection\AbstractTestFramework\Coverage\TestLocation;
use function strpos;
use function substr;

/**
 * @internal
 */
final readonly class JUnitTestCaseTimeAdder
{
    /**
     * @param TestLocation[] $tests
     */
    public function __construct(private array $tests)
    {
    }

    public function getTotalTestTime(): float
    {
        return array_sum(
            $this->uniqueTestLocations(),
        );
    }

    /**
     * Returns unique'd test cases with timings. Timings are per test suite, not per test, therefore we have to unique by test suite name.
     *
     * @return array<float|null>
     */
    private function uniqueTestLocations(): array
    {
        $seenTestSuites = [];

        foreach ($this->tests as $testLocation) {
            $methodName = $testLocation->getMethod();
            $methodSeparatorPos = strpos($methodName, '::');

            if ($methodSeparatorPos === false) {
                // Just for the off case where we have rubbish in the test method name
                continue;
            }

            // For each test we discard method name, and return a single timing for an entire suite
            $testSuiteName = substr($methodName, 0, $methodSeparatorPos);

            if (array_key_exists($testSuiteName, $seenTestSuites)) {
                continue;
            }

            $seenTestSuites[$testSuiteName] = $testLocation->getExecutionTime();
        }

        return $seenTestSuites;
    }
}
