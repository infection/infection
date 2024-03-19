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

use function explode;
use Infection\AbstractTestFramework\Coverage\TestLocation;
use Infection\AbstractTestFramework\TestFrameworkAdapter;
use Infection\TestFramework\Coverage\Trace;

/**
 * Adds test execution info to selected covered file data object.
 *
 * @internal
 * @final
 */
class JUnitTestExecutionInfoAdder
{
    private const MAX_EXPLODE_PARTS = 2;

    public function __construct(private readonly TestFrameworkAdapter $adapter, private readonly TestFileDataProvider $testFileDataProvider)
    {
    }

    /**
     * @param iterable<Trace> $traces
     *
     * @return iterable<Trace>
     */
    public function addTestExecutionInfo(iterable $traces): iterable
    {
        if (!$this->adapter->hasJUnitReport()) {
            return $traces;
        }

        return $this->testExecutionInfoAdder($traces);
    }

    /**
     * @param iterable<Trace> $traces
     *
     * @return iterable<Trace>
     */
    private function testExecutionInfoAdder(iterable $traces): iterable
    {
        /** @var Trace $trace */
        foreach ($traces as $trace) {
            $tests = $trace->getTests();

            if ($tests === null) {
                continue;
            }

            foreach ($tests->getTestsLocationsBySourceLine() as &$testsLocations) {
                foreach ($testsLocations as $line => $test) {
                    $testsLocations[$line] = $this->createCompleteTestLocation($test);
                }
            }
            unset($testsLocations);

            yield $trace;
        }
    }

    private function createCompleteTestLocation(TestLocation $test): TestLocation
    {
        $class = explode(':', $test->getMethod(), self::MAX_EXPLODE_PARTS)[0];

        $testFileData = $this->testFileDataProvider->getTestFileInfo($class);

        return new TestLocation(
            $test->getMethod(),
            $testFileData->path,
            $testFileData->time,
        );
    }
}
