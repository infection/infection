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

final readonly class PhpUnit09Provider
{
    use CannotBeInstantiated;

    private const FIXTURES_DIR = __DIR__ . '/../../Fixtures';

    public static function infoProvider(): iterable
    {
        $junitXml = file_get_contents(
            Path::canonicalize(self::FIXTURES_DIR . '/phpunit-09/junit.xml'),
        );

        yield 'TestCase classname' => [
            $junitXml,
            'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\CalculatorTest',
            new TestFileTimeData(
                '/path/to/infection/tests/e2e/PHPUnit_09-3/tests/Covered/CalculatorTest.php',
                0.005626,
            ),
        ];

        yield 'test ID of a simple test' => [
            $junitXml,
            'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\CalculatorTest::test_multiply',
            TestFileNameNotFoundException::class,
        ];

        yield 'test method of a simple test' => [
            $junitXml,
            'test_multiply',
            TestFileNameNotFoundException::class,
        ];

        yield 'test ID of a test with a data provider with a numerical key' => [
            $junitXml,
            'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\CalculatorTest::test_subtract#0',
            TestFileNameNotFoundException::class,
        ];

        yield 'test ID of a test with a data provider with a string key' => [
            $junitXml,
            'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\CalculatorTest::test_subtract#with a key',
            TestFileNameNotFoundException::class,
        ];

        yield 'test ID of a test with a data provider with a string key with special characters' => [
            $junitXml,
            'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\CalculatorTest::test_subtract#with a key with (\'&quot;#::&amp;) special characters',
            TestFileNameNotFoundException::class,
        ];

        yield 'test method of a test with a data provider' => [
            $junitXml,
            'test_subtract',
            TestFileNameNotFoundException::class,
        ];

        yield 'test case of a data provider' => [
            $junitXml,
            'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\CalculatorTest::test_subtract',
            new TestFileTimeData(
                '',
                0.000462,
            ),
        ];

        yield 'test ID of a test with an external data provider with a numerical key' => [
            $junitXml,
            'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\CalculatorTest::test_add#0',
            TestFileNameNotFoundException::class,
        ];

        yield 'test ID of a test with an external data provider with a string key' => [
            $junitXml,
            'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\CalculatorTest::test_add#with a key',
            TestFileNameNotFoundException::class,
        ];

        yield 'test ID of a test with an external data provider with a string key with special characters' => [
            $junitXml,
            'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\CalculatorTest::test_add#with a key with (\'&quot;#::&amp;) special characters',
            TestFileNameNotFoundException::class,
        ];

        yield 'test method of a test with an external data provider' => [
            $junitXml,
            'test_add',
            TestFileNameNotFoundException::class,
        ];

        yield 'test case of an external data provider' => [
            $junitXml,
            'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\CalculatorTest::test_add',
            new TestFileTimeData(
                '',
                0.003283,
            ),
        ];
    }
}
