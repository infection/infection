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

namespace Infection\Tests\TestFramework\Coverage\JUnit\JUnitTestFileDataProvider;

use Infection\CannotBeInstantiated;
use Infection\TestFramework\Coverage\JUnit\TestFileNameNotFoundException;
use Infection\TestFramework\Coverage\JUnit\TestFileTimeData;
use function Safe\file_get_contents;
use Symfony\Component\Filesystem\Path;

final class CodeceptionUnitProvider
{
    use CannotBeInstantiated;

    private const FIXTURES_DIR = __DIR__ . '/../../Fixtures';

    public static function infoProvider(): iterable
    {
        $junitXml = file_get_contents(
            Path::canonicalize(self::FIXTURES_DIR . '/codeception/junit.xml'),
        );

        yield 'TestSuite' => [
            $junitXml,
            'Codeception_With_Suite_Overridings.unit',
            new TestFileTimeData(
                '',
                0.039222,
            ),
        ];

        yield 'TestCase classname' => [
            $junitXml,
            'Codeception_With_Suite_Overridings\Tests\unit\Covered\CalculatorTest',
            new TestFileTimeData(
                '/path/to/codeception-adapter/tests/e2e/Codeception_With_Suite_Overridings/tests/unit/Covered/CalculatorTest.php',
                0.004677,
            ),
        ];

        yield 'test ID of a simple test' => [
            $junitXml,
            // Note that unlike PHPUnit (despite using it), it uses `:` instead of `::`
            'Codeception_With_Suite_Overridings\Tests\unit\Covered\CalculatorTest:testMultiplication',
            new TestFileTimeData(
                '/path/to/codeception-adapter/tests/e2e/Codeception_With_Suite_Overridings/tests/unit/Covered/CalculatorTest.php',
                0.004677,
            ),
        ];

        yield 'test method of a simple test' => [
            $junitXml,
            'testMultiplication',
            TestFileNameNotFoundException::class,
        ];

        // Codeception does not understand PHPUnit data providers.
        // Instead of having:
        //     Infection\E2ETests\PHPUnit_12_0\Tests\Covered\CalculatorTest::test_subtract#0
        // Codeception will have:
        //     Infection\E2ETests\PHPUnit_12_0\Tests\Covered\CalculatorTest:test_subtract
        // As if no data provider was configured... local like external.
        // This means in the JUnit report, we can see:
        //     <testcase name="testAddition" class="Codeception_With_Suite_Overridings\Tests\functional\CalculatorCest" file="/path/to/codeception-adapter/tests/e2e/Codeception_With_Suite_Overridings/tests/functional/CalculatorCest.php" time="0.000252" assertions="1"/>
        // appearing multiple times due to the data providers, but with no way to distinguish them...
    }
}
