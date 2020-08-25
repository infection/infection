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
use Infection\TestFramework\Coverage\JUnit\JUnitTestCaseTimeAdder;
use PHPUnit\Framework\TestCase;

final class JUnitTestCaseTimeAdderTest extends TestCase
{
    public function test_it_returns_time_for_the_only_test(): void
    {
        $coverageTestCases = [
            new TestLocation(
                'Test::testMethod1',
                '/path/to/test-file-1',
                0.000234
            ),
        ];

        $adder = new JUnitTestCaseTimeAdder($coverageTestCases);

        $total = $adder->getTotalTestTime();

        $this->assertSame(0.000234, $total);
    }

    public function test_it_ignores_tests_without_valid_suite_name(): void
    {
        $coverageTestCases = [
            new TestLocation(
                'rubbish',
                '/path/to/test-file-1',
                0.000234
            ),
        ];

        $adder = new JUnitTestCaseTimeAdder($coverageTestCases);

        $total = $adder->getTotalTestTime();

        $this->assertSame(0.0, $total);
    }

    public function test_it_returns_sum_for_uniqued_test_cases(): void
    {
        $coverageTestCases = [
            new TestLocation(
                'FooTest::testMethod1',
                '/path/to/test-file-1',
                0.9
            ),
            new TestLocation(
                'FooTest::testMethod2',
                '/path/to/test-file-2',
                0.9
            ),
            new TestLocation(
                'BarTest::testMethod3_1',
                '/path/to/test-file-3',
                0.2
            ),
            new TestLocation(
                'BarTest::testMethod3_2',
                '/path/to/test-file-4',
                0.2
            ),
        ];

        $adder = new JUnitTestCaseTimeAdder($coverageTestCases);

        $total = $adder->getTotalTestTime();

        $this->assertSame(0.9 + 0.2, $total);
    }
}
