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
use InvalidArgumentException;
use function Safe\file_get_contents;
use Symfony\Component\Filesystem\Path;

final class CodeceptionBDDProvider
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
            'Codeception_With_Suite_Overridings.acceptance',
            new TestFileTimeData(
                '',
                0.013694,
            ),
        ];

        yield 'TestCase (feature)' => [
            $junitXml,
            'Calculator',
            // TODO: this is not ideal
            InvalidArgumentException::class,
        ];

        yield 'test ID of a simple test (ID found in a JUnit report)' => [
            $junitXml,
            'Calculator: Dividing two numbers',
            TestFileNameNotFoundException::class,
        ];

        yield 'test ID of a simple test (ID found in a CoverageXML report)' => [
            $junitXml,
            'calculator:Dividing two numbers',
            new TestFileTimeData(
                '/path/to/codeception-adapter/tests/e2e/Codeception_With_Suite_Overridings/tests/acceptance/calculator.feature',
                0.003794,
            ),
        ];

        yield 'test method of a simple test' => [
            $junitXml,
            'Dividing two numbers',
            TestFileNameNotFoundException::class,
        ];

        yield 'test ID of a test with a scenario outline (ID found in a JUnit report)' => [
            $junitXml,
            'Calculator: Checking if numbers are positive | 5, true',
            TestFileNameNotFoundException::class,
        ];

        yield 'test ID of a test with a scenario outline (ID found in a CoverageXML report)' => [
            $junitXml,
            'calculator:Checking if numbers are positive | 5, true',
            new TestFileTimeData(
                '/path/to/codeception-adapter/tests/e2e/Codeception_With_Suite_Overridings/tests/acceptance/calculator.feature',
                0.003794,
            ),
        ];

        yield 'test method of a test with a scenario outline' => [
            $junitXml,
            'Checking if numbers are positive',
            TestFileNameNotFoundException::class,
        ];

        yield 'test ID of a test with a scenario outline with a placeholder in the scenario title (ID found in a JUnit report)' => [
            $junitXml,
            'Calculator: Computing absolute value with label &quot;&lt;label&gt;&quot; | 42, 5, 5',
            TestFileNameNotFoundException::class,
        ];

        yield 'test ID of a test with a scenario outline with a placeholder in the scenario title (ID found in a CoverageXML report)' => [
            $junitXml,
            'calculator:Computing absolute value with label &quot;&lt;label&gt;&quot; | 42, 5, 5',
            new TestFileTimeData(
                '/path/to/codeception-adapter/tests/e2e/Codeception_With_Suite_Overridings/tests/acceptance/calculator.feature',
                0.003794,
            ),
        ];

        yield 'test ID of a test with a scenario outline with a placeholder in the scenario title with special characters (ID found in a JUnit report)' => [
            $junitXml,
            'Calculator: Computing absolute value with label &quot;&lt;label&gt;&quot; | with special chars (\'&quot;#::&amp;), -15, 15',
            TestFileNameNotFoundException::class,
        ];

        yield 'test ID of a test with a scenario outline with a placeholder in the scenario title with special characters (ID found in a CoverageXML report)' => [
            $junitXml,
            'calculator:Computing absolute value with label &quot;&lt;label&gt;&quot; | with special chars (\'&quot;#::&amp;), -15, 15',
            TestFileNameNotFoundException::class,
        ];

        yield 'test method of a test with a scenario outline with a placeholder in the scenario title' => [
            $junitXml,
            'Computing absolute value with label &quot;&lt;label&gt;&quot;',
            TestFileNameNotFoundException::class,
        ];
    }
}
