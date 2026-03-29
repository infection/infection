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

namespace Infection\Tests\TestFramework\Tracing\Tracer;

use Infection\AbstractTestFramework\Coverage\TestLocation;
use Infection\CannotBeInstantiated;
use Infection\TestFramework\Tracing\Trace\SourceMethodLineRange;
use Infection\TestFramework\Tracing\Trace\TestLocations;
use Infection\Tests\TestFramework\Tracing\Trace\SyntheticTrace;
use Symfony\Component\Filesystem\Path;

final class CodeceptionProvider
{
    use CannotBeInstantiated;

    private const FIXTURES_DIR = __DIR__ . '/../../Coverage/Fixtures';

    public static function infoProvider(): iterable
    {
        $coverageDirectory = Path::canonicalize(self::FIXTURES_DIR . '/codeception/');

        $indexXmlPath = Path::canonicalize($coverageDirectory . '/coverage-xml/index.xml');
        $junitXmlPath = Path::canonicalize($coverageDirectory . '/junit.xml');

        yield 'covered class' => (static function () use ($indexXmlPath, $junitXmlPath) {
            $sourcePath = '/path/to/codeception-adapter/tests/e2e/Codeception_With_Suite_Overridings/src/Covered/Calculator.php';
            $acceptanceTestPath = '/path/to/codeception-adapter/tests/e2e/Codeception_With_Suite_Overridings/tests/acceptance/calculator.feature';
            $functionalTestPath = '/path/to/codeception-adapter/tests/e2e/Codeception_With_Suite_Overridings/tests/functional/CalculatorCest.php';
            $unitTestPath = '/path/to/codeception-adapter/tests/e2e/Codeception_With_Suite_Overridings/tests/unit/Covered/CalculatorTest.php';

            return [
                $indexXmlPath,
                $junitXmlPath,
                SyntheticTrace::forSource(
                    realPath: $sourcePath,
                    hasTest: true,
                    tests: new TestLocations(
                        byLine: [
                            9 => [
                                new TestLocation(
                                    method: 'calculator:Adding two numbers',
                                    filePath: $acceptanceTestPath,
                                    executionTime: 0.003794,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\functional\CalculatorCest:testAddition',
                                    filePath: $functionalTestPath,
                                    executionTime: 0.001363,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\CalculatorTest:testAddition',
                                    filePath: $unitTestPath,
                                    executionTime: 0.004677,
                                ),
                            ],
                            14 => [
                                new TestLocation(
                                    method: 'calculator:Subtracting two numbers',
                                    filePath: $acceptanceTestPath,
                                    executionTime: 0.003794,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\functional\CalculatorCest:testSubtraction',
                                    filePath: $functionalTestPath,
                                    executionTime: 0.001363,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\CalculatorTest:testSubtraction',
                                    filePath: $unitTestPath,
                                    executionTime: 0.004677,
                                ),
                            ],
                            19 => [
                                new TestLocation(
                                    method: 'calculator:Multiplying two numbers',
                                    filePath: $acceptanceTestPath,
                                    executionTime: 0.003794,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\functional\CalculatorCest:testMultiplication',
                                    filePath: $functionalTestPath,
                                    executionTime: 0.001363,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\CalculatorTest:testMultiplication',
                                    filePath: $unitTestPath,
                                    executionTime: 0.004677,
                                ),
                            ],
                            24 => [
                                new TestLocation(
                                    method: 'calculator:Dividing two numbers',
                                    filePath: $acceptanceTestPath,
                                    executionTime: 0.003794,
                                ),
                                new TestLocation(
                                    method: 'calculator:Division by zero throws error',
                                    filePath: $acceptanceTestPath,
                                    executionTime: 0.003794,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\functional\CalculatorCest:testDivision',
                                    filePath: $functionalTestPath,
                                    executionTime: 0.001363,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\functional\CalculatorCest:testDivisionByZeroThrowsException',
                                    filePath: $functionalTestPath,
                                    executionTime: 0.001363,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\CalculatorTest:testDivision',
                                    filePath: $unitTestPath,
                                    executionTime: 0.004677,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\CalculatorTest:testDivisionByZero',
                                    filePath: $unitTestPath,
                                    executionTime: 0.004677,
                                ),
                            ],
                            25 => [
                                new TestLocation(
                                    method: 'calculator:Division by zero throws error',
                                    filePath: $acceptanceTestPath,
                                    executionTime: 0.003794,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\functional\CalculatorCest:testDivisionByZeroThrowsException',
                                    filePath: $functionalTestPath,
                                    executionTime: 0.001363,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\CalculatorTest:testDivisionByZero',
                                    filePath: $unitTestPath,
                                    executionTime: 0.004677,
                                ),
                            ],
                            28 => [
                                new TestLocation(
                                    method: 'calculator:Dividing two numbers',
                                    filePath: $acceptanceTestPath,
                                    executionTime: 0.003794,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\functional\CalculatorCest:testDivision',
                                    filePath: $functionalTestPath,
                                    executionTime: 0.001363,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\CalculatorTest:testDivision',
                                    filePath: $unitTestPath,
                                    executionTime: 0.004677,
                                ),
                            ],
                            33 => [
                                new TestLocation(
                                    method: 'calculator:Checking if numbers are positive | 5, true',
                                    filePath: $acceptanceTestPath,
                                    executionTime: 0.003794,
                                ),
                                new TestLocation(
                                    method: 'calculator:Checking if numbers are positive | 0, true',
                                    filePath: $acceptanceTestPath,
                                    executionTime: 0.003794,
                                ),
                                new TestLocation(
                                    method: 'calculator:Checking if numbers are positive | -5, false',
                                    filePath: $acceptanceTestPath,
                                    executionTime: 0.003794,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\functional\CalculatorCest:testIsPositive',
                                    filePath: $functionalTestPath,
                                    executionTime: 0.001363,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\CalculatorTest:testIsPositive',
                                    filePath: $unitTestPath,
                                    executionTime: 0.004677,
                                ),
                            ],
                            38 => [
                                new TestLocation(
                                    method: 'calculator:Computing absolute value with label "<label>" | 42, 5, 5',
                                    filePath: $acceptanceTestPath,
                                    executionTime: 0.003794,
                                ),
                                new TestLocation(
                                    method: 'calculator:Computing absolute value with label "<label>" | positive number, 10, 10',
                                    filePath: $acceptanceTestPath,
                                    executionTime: 0.003794,
                                ),
                                new TestLocation(
                                    method: 'calculator:Computing absolute value with label "<label>" | negative number, -7, 7',
                                    filePath: $acceptanceTestPath,
                                    executionTime: 0.003794,
                                ),
                                new TestLocation(
                                    method: 'calculator:Computing absolute value with label "<label>" | zero, 0, 0',
                                    filePath: $acceptanceTestPath,
                                    executionTime: 0.003794,
                                ),
                                new TestLocation(
                                    method: 'calculator:Computing absolute value with label "<label>" | with special chars (\'"#::&), -15, 15',
                                    filePath: $acceptanceTestPath,
                                    executionTime: 0.003794,
                                ),
                                new TestLocation(
                                    method: 'calculator:Computing absolute value with label "<label>" | another "quoted" value, -1, 1',
                                    filePath: $acceptanceTestPath,
                                    executionTime: 0.003794,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\functional\CalculatorCest:testAbsolute',
                                    filePath: $functionalTestPath,
                                    executionTime: 0.001363,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\CalculatorTest:testAbsolute',
                                    filePath: $unitTestPath,
                                    executionTime: 0.004677,
                                ),
                            ],
                        ],
                        byMethod: [
                            'add' => new SourceMethodLineRange(7, 10),
                            'subtract' => new SourceMethodLineRange(12, 15),
                            'multiply' => new SourceMethodLineRange(17, 20),
                            'divide' => new SourceMethodLineRange(22, 29),
                            'isPositive' => new SourceMethodLineRange(31, 34),
                            'absolute' => new SourceMethodLineRange(36, 39),
                        ],
                    ),
                ),
            ];
        })();

        yield 'covered trait' => (static function () use ($indexXmlPath, $junitXmlPath) {
            $sourcePath = '/path/to/codeception-adapter/tests/e2e/Codeception_With_Suite_Overridings/src/Covered/LoggerTrait.php';
            $functionalTestPath = '/path/to/codeception-adapter/tests/e2e/Codeception_With_Suite_Overridings/tests/functional/UserServiceCest.php';
            $unitTestPath = '/path/to/codeception-adapter/tests/e2e/Codeception_With_Suite_Overridings/tests/unit/Covered/UserServiceTest.php';

            return [
                $indexXmlPath,
                $junitXmlPath,
                SyntheticTrace::forSource(
                    realPath: $sourcePath,
                    hasTest: true,
                    tests: new TestLocations(
                        byLine: [
                            11 => [
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testAddUser',
                                    filePath: $functionalTestPath,
                                    executionTime: 0.000577,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testAddUserWithEmptyNameFails',
                                    filePath: $functionalTestPath,
                                    executionTime: 0.000577,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testAddUserWithEmptyEmailFails',
                                    filePath: $functionalTestPath,
                                    executionTime: 0.000577,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testAddDuplicateUserFails',
                                    filePath: $functionalTestPath,
                                    executionTime: 0.000577,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testRemoveUser',
                                    filePath: $functionalTestPath,
                                    executionTime: 0.000577,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testRemoveNonExistentUserFails',
                                    filePath: $functionalTestPath,
                                    executionTime: 0.000577,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testGetUser',
                                    filePath: $functionalTestPath,
                                    executionTime: 0.000577,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testUserExists',
                                    filePath: $functionalTestPath,
                                    executionTime: 0.000577,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testClearLogs',
                                    filePath: $functionalTestPath,
                                    executionTime: 0.000577,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testAddUser',
                                    filePath: $unitTestPath,
                                    executionTime: 0.002154,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testAddUserWithEmptyName',
                                    filePath: $unitTestPath,
                                    executionTime: 0.002154,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testAddUserWithEmptyEmail',
                                    filePath: $unitTestPath,
                                    executionTime: 0.002154,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testAddDuplicateUser',
                                    filePath: $unitTestPath,
                                    executionTime: 0.002154,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testRemoveUser',
                                    filePath: $unitTestPath,
                                    executionTime: 0.002154,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testRemoveNonExistentUser',
                                    filePath: $unitTestPath,
                                    executionTime: 0.002154,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testGetUser',
                                    filePath: $unitTestPath,
                                    executionTime: 0.002154,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testUserExists',
                                    filePath: $unitTestPath,
                                    executionTime: 0.002154,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testGetLogs',
                                    filePath: $unitTestPath,
                                    executionTime: 0.002154,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testClearLogs',
                                    filePath: $unitTestPath,
                                    executionTime: 0.002154,
                                ),
                            ],
                            16 => [
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testGetLogs',
                                    filePath: $unitTestPath,
                                    executionTime: 0.002154,
                                ),
                            ],
                            21 => [
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testClearLogs',
                                    filePath: $functionalTestPath,
                                    executionTime: 0.000577,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testClearLogs',
                                    filePath: $unitTestPath,
                                    executionTime: 0.002154,
                                ),
                            ],
                            26 => [
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testAddUser',
                                    filePath: $functionalTestPath,
                                    executionTime: 0.000577,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testClearLogs',
                                    filePath: $functionalTestPath,
                                    executionTime: 0.000577,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testAddUser',
                                    filePath: $unitTestPath,
                                    executionTime: 0.002154,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testClearLogs',
                                    filePath: $unitTestPath,
                                    executionTime: 0.002154,
                                ),
                            ],
                        ],
                        byMethod: [
                            'log' => new SourceMethodLineRange(9, 12),
                            'getLogs' => new SourceMethodLineRange(14, 17),
                            'clearLogs' => new SourceMethodLineRange(19, 22),
                            'hasLogs' => new SourceMethodLineRange(24, 27),
                        ],
                    ),
                ),
            ];
        })();

        yield 'covered class with trait' => (static function () use ($indexXmlPath, $junitXmlPath) {
            $sourcePath = '/path/to/codeception-adapter/tests/e2e/Codeception_With_Suite_Overridings/src/Covered/UserService.php';
            $functionalTestPath = '/path/to/codeception-adapter/tests/e2e/Codeception_With_Suite_Overridings/tests/functional/UserServiceCest.php';
            $unitTestPath = '/path/to/codeception-adapter/tests/e2e/Codeception_With_Suite_Overridings/tests/unit/Covered/UserServiceTest.php';

            return [
                $indexXmlPath,
                $junitXmlPath,
                SyntheticTrace::forSource(
                    realPath: $sourcePath,
                    hasTest: true,
                    tests: new TestLocations(
                        byLine: [
                            13 => [
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testAddUser',
                                    filePath: $functionalTestPath,
                                    executionTime: 0.000577,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testAddUserWithEmptyNameFails',
                                    filePath: $functionalTestPath,
                                    executionTime: 0.000577,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testAddUserWithEmptyEmailFails',
                                    filePath: $functionalTestPath,
                                    executionTime: 0.000577,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testAddDuplicateUserFails',
                                    filePath: $functionalTestPath,
                                    executionTime: 0.000577,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testRemoveUser',
                                    filePath: $functionalTestPath,
                                    executionTime: 0.000577,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testGetUser',
                                    filePath: $functionalTestPath,
                                    executionTime: 0.000577,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testUserExists',
                                    filePath: $functionalTestPath,
                                    executionTime: 0.000577,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testClearLogs',
                                    filePath: $functionalTestPath,
                                    executionTime: 0.000577,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testAddUser',
                                    filePath: $unitTestPath,
                                    executionTime: 0.002154,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testAddUserWithEmptyName',
                                    filePath: $unitTestPath,
                                    executionTime: 0.002154,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testAddUserWithEmptyEmail',
                                    filePath: $unitTestPath,
                                    executionTime: 0.002154,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testAddDuplicateUser',
                                    filePath: $unitTestPath,
                                    executionTime: 0.002154,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testRemoveUser',
                                    filePath: $unitTestPath,
                                    executionTime: 0.002154,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testGetUser',
                                    filePath: $unitTestPath,
                                    executionTime: 0.002154,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testUserExists',
                                    filePath: $unitTestPath,
                                    executionTime: 0.002154,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testGetLogs',
                                    filePath: $unitTestPath,
                                    executionTime: 0.002154,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testClearLogs',
                                    filePath: $unitTestPath,
                                    executionTime: 0.002154,
                                ),
                            ],
                            14 => [
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testAddUserWithEmptyNameFails',
                                    filePath: $functionalTestPath,
                                    executionTime: 0.000577,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testAddUserWithEmptyEmailFails',
                                    filePath: $functionalTestPath,
                                    executionTime: 0.000577,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testAddUserWithEmptyName',
                                    filePath: $unitTestPath,
                                    executionTime: 0.002154,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testAddUserWithEmptyEmail',
                                    filePath: $unitTestPath,
                                    executionTime: 0.002154,
                                ),
                            ],
                            15 => [
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testAddUserWithEmptyNameFails',
                                    filePath: $functionalTestPath,
                                    executionTime: 0.000577,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testAddUserWithEmptyEmailFails',
                                    filePath: $functionalTestPath,
                                    executionTime: 0.000577,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testAddUserWithEmptyName',
                                    filePath: $unitTestPath,
                                    executionTime: 0.002154,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testAddUserWithEmptyEmail',
                                    filePath: $unitTestPath,
                                    executionTime: 0.002154,
                                ),
                            ],
                            18 => [
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testAddUser',
                                    filePath: $functionalTestPath,
                                    executionTime: 0.000577,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testAddDuplicateUserFails',
                                    filePath: $functionalTestPath,
                                    executionTime: 0.000577,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testRemoveUser',
                                    filePath: $functionalTestPath,
                                    executionTime: 0.000577,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testGetUser',
                                    filePath: $functionalTestPath,
                                    executionTime: 0.000577,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testUserExists',
                                    filePath: $functionalTestPath,
                                    executionTime: 0.000577,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testClearLogs',
                                    filePath: $functionalTestPath,
                                    executionTime: 0.000577,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testAddUser',
                                    filePath: $unitTestPath,
                                    executionTime: 0.002154,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testAddDuplicateUser',
                                    filePath: $unitTestPath,
                                    executionTime: 0.002154,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testRemoveUser',
                                    filePath: $unitTestPath,
                                    executionTime: 0.002154,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testGetUser',
                                    filePath: $unitTestPath,
                                    executionTime: 0.002154,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testUserExists',
                                    filePath: $unitTestPath,
                                    executionTime: 0.002154,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testGetLogs',
                                    filePath: $unitTestPath,
                                    executionTime: 0.002154,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testClearLogs',
                                    filePath: $unitTestPath,
                                    executionTime: 0.002154,
                                ),
                            ],
                            19 => [
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testAddDuplicateUserFails',
                                    filePath: $functionalTestPath,
                                    executionTime: 0.000577,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testAddDuplicateUser',
                                    filePath: $unitTestPath,
                                    executionTime: 0.002154,
                                ),
                            ],
                            20 => [
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testAddDuplicateUserFails',
                                    filePath: $functionalTestPath,
                                    executionTime: 0.000577,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testAddDuplicateUser',
                                    filePath: $unitTestPath,
                                    executionTime: 0.002154,
                                ),
                            ],
                            23 => [
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testAddUser',
                                    filePath: $functionalTestPath,
                                    executionTime: 0.000577,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testAddDuplicateUserFails',
                                    filePath: $functionalTestPath,
                                    executionTime: 0.000577,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testRemoveUser',
                                    filePath: $functionalTestPath,
                                    executionTime: 0.000577,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testGetUser',
                                    filePath: $functionalTestPath,
                                    executionTime: 0.000577,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testUserExists',
                                    filePath: $functionalTestPath,
                                    executionTime: 0.000577,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testClearLogs',
                                    filePath: $functionalTestPath,
                                    executionTime: 0.000577,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testAddUser',
                                    filePath: $unitTestPath,
                                    executionTime: 0.002154,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testAddDuplicateUser',
                                    filePath: $unitTestPath,
                                    executionTime: 0.002154,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testRemoveUser',
                                    filePath: $unitTestPath,
                                    executionTime: 0.002154,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testGetUser',
                                    filePath: $unitTestPath,
                                    executionTime: 0.002154,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testUserExists',
                                    filePath: $unitTestPath,
                                    executionTime: 0.002154,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testGetLogs',
                                    filePath: $unitTestPath,
                                    executionTime: 0.002154,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testClearLogs',
                                    filePath: $unitTestPath,
                                    executionTime: 0.002154,
                                ),
                            ],
                            24 => [
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testAddUser',
                                    filePath: $functionalTestPath,
                                    executionTime: 0.000577,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testAddDuplicateUserFails',
                                    filePath: $functionalTestPath,
                                    executionTime: 0.000577,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testRemoveUser',
                                    filePath: $functionalTestPath,
                                    executionTime: 0.000577,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testGetUser',
                                    filePath: $functionalTestPath,
                                    executionTime: 0.000577,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testUserExists',
                                    filePath: $functionalTestPath,
                                    executionTime: 0.000577,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testClearLogs',
                                    filePath: $functionalTestPath,
                                    executionTime: 0.000577,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testAddUser',
                                    filePath: $unitTestPath,
                                    executionTime: 0.002154,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testAddDuplicateUser',
                                    filePath: $unitTestPath,
                                    executionTime: 0.002154,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testRemoveUser',
                                    filePath: $unitTestPath,
                                    executionTime: 0.002154,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testGetUser',
                                    filePath: $unitTestPath,
                                    executionTime: 0.002154,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testUserExists',
                                    filePath: $unitTestPath,
                                    executionTime: 0.002154,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testGetLogs',
                                    filePath: $unitTestPath,
                                    executionTime: 0.002154,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testClearLogs',
                                    filePath: $unitTestPath,
                                    executionTime: 0.002154,
                                ),
                            ],
                            25 => [
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testAddUser',
                                    filePath: $functionalTestPath,
                                    executionTime: 0.000577,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testAddDuplicateUserFails',
                                    filePath: $functionalTestPath,
                                    executionTime: 0.000577,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testRemoveUser',
                                    filePath: $functionalTestPath,
                                    executionTime: 0.000577,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testGetUser',
                                    filePath: $functionalTestPath,
                                    executionTime: 0.000577,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testUserExists',
                                    filePath: $functionalTestPath,
                                    executionTime: 0.000577,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testClearLogs',
                                    filePath: $functionalTestPath,
                                    executionTime: 0.000577,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testAddUser',
                                    filePath: $unitTestPath,
                                    executionTime: 0.002154,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testAddDuplicateUser',
                                    filePath: $unitTestPath,
                                    executionTime: 0.002154,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testRemoveUser',
                                    filePath: $unitTestPath,
                                    executionTime: 0.002154,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testGetUser',
                                    filePath: $unitTestPath,
                                    executionTime: 0.002154,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testUserExists',
                                    filePath: $unitTestPath,
                                    executionTime: 0.002154,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testGetLogs',
                                    filePath: $unitTestPath,
                                    executionTime: 0.002154,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testClearLogs',
                                    filePath: $unitTestPath,
                                    executionTime: 0.002154,
                                ),
                            ],
                            30 => [
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testRemoveUser',
                                    filePath: $functionalTestPath,
                                    executionTime: 0.000577,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testRemoveNonExistentUserFails',
                                    filePath: $functionalTestPath,
                                    executionTime: 0.000577,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testRemoveUser',
                                    filePath: $unitTestPath,
                                    executionTime: 0.002154,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testRemoveNonExistentUser',
                                    filePath: $unitTestPath,
                                    executionTime: 0.002154,
                                ),
                            ],
                            31 => [
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testRemoveNonExistentUserFails',
                                    filePath: $functionalTestPath,
                                    executionTime: 0.000577,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testRemoveNonExistentUser',
                                    filePath: $unitTestPath,
                                    executionTime: 0.002154,
                                ),
                            ],
                            32 => [
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testRemoveNonExistentUserFails',
                                    filePath: $functionalTestPath,
                                    executionTime: 0.000577,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testRemoveNonExistentUser',
                                    filePath: $unitTestPath,
                                    executionTime: 0.002154,
                                ),
                            ],
                            35 => [
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testRemoveUser',
                                    filePath: $functionalTestPath,
                                    executionTime: 0.000577,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testRemoveUser',
                                    filePath: $unitTestPath,
                                    executionTime: 0.002154,
                                ),
                            ],
                            36 => [
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testRemoveUser',
                                    filePath: $functionalTestPath,
                                    executionTime: 0.000577,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testRemoveUser',
                                    filePath: $unitTestPath,
                                    executionTime: 0.002154,
                                ),
                            ],
                            37 => [
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testRemoveUser',
                                    filePath: $functionalTestPath,
                                    executionTime: 0.000577,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testRemoveUser',
                                    filePath: $unitTestPath,
                                    executionTime: 0.002154,
                                ),
                            ],
                            42 => [
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testGetUser',
                                    filePath: $functionalTestPath,
                                    executionTime: 0.000577,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testGetNonExistentUserReturnsNull',
                                    filePath: $functionalTestPath,
                                    executionTime: 0.000577,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testGetUser',
                                    filePath: $unitTestPath,
                                    executionTime: 0.002154,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testGetNonExistentUser',
                                    filePath: $unitTestPath,
                                    executionTime: 0.002154,
                                ),
                            ],
                            47 => [
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testAddUser',
                                    filePath: $functionalTestPath,
                                    executionTime: 0.000577,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testAddDuplicateUserFails',
                                    filePath: $functionalTestPath,
                                    executionTime: 0.000577,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testRemoveUser',
                                    filePath: $functionalTestPath,
                                    executionTime: 0.000577,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testRemoveNonExistentUserFails',
                                    filePath: $functionalTestPath,
                                    executionTime: 0.000577,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testGetUser',
                                    filePath: $functionalTestPath,
                                    executionTime: 0.000577,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testUserExists',
                                    filePath: $functionalTestPath,
                                    executionTime: 0.000577,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testClearLogs',
                                    filePath: $functionalTestPath,
                                    executionTime: 0.000577,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testAddUser',
                                    filePath: $unitTestPath,
                                    executionTime: 0.002154,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testAddDuplicateUser',
                                    filePath: $unitTestPath,
                                    executionTime: 0.002154,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testRemoveUser',
                                    filePath: $unitTestPath,
                                    executionTime: 0.002154,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testRemoveNonExistentUser',
                                    filePath: $unitTestPath,
                                    executionTime: 0.002154,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testGetUser',
                                    filePath: $unitTestPath,
                                    executionTime: 0.002154,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testUserExists',
                                    filePath: $unitTestPath,
                                    executionTime: 0.002154,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testGetLogs',
                                    filePath: $unitTestPath,
                                    executionTime: 0.002154,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testClearLogs',
                                    filePath: $unitTestPath,
                                    executionTime: 0.002154,
                                ),
                            ],
                            52 => [
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testAddUser',
                                    filePath: $functionalTestPath,
                                    executionTime: 0.000577,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testAddUserWithEmptyNameFails',
                                    filePath: $functionalTestPath,
                                    executionTime: 0.000577,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testAddUserWithEmptyEmailFails',
                                    filePath: $functionalTestPath,
                                    executionTime: 0.000577,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testAddDuplicateUserFails',
                                    filePath: $functionalTestPath,
                                    executionTime: 0.000577,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testRemoveUser',
                                    filePath: $functionalTestPath,
                                    executionTime: 0.000577,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testAddUser',
                                    filePath: $unitTestPath,
                                    executionTime: 0.002154,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testAddUserWithEmptyName',
                                    filePath: $unitTestPath,
                                    executionTime: 0.002154,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testAddUserWithEmptyEmail',
                                    filePath: $unitTestPath,
                                    executionTime: 0.002154,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testAddDuplicateUser',
                                    filePath: $unitTestPath,
                                    executionTime: 0.002154,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testRemoveUser',
                                    filePath: $unitTestPath,
                                    executionTime: 0.002154,
                                ),
                            ],
                        ],
                        byMethod: [
                            'addUser' => new SourceMethodLineRange(11, 26),
                            'removeUser' => new SourceMethodLineRange(28, 38),
                            'getUser' => new SourceMethodLineRange(40, 43),
                            'userExists' => new SourceMethodLineRange(45, 48),
                            'getUserCount' => new SourceMethodLineRange(50, 53),
                        ],
                    ),
                ),
            ];
        })();

        yield 'covered function' => (static function () use ($indexXmlPath, $junitXmlPath) {
            $sourcePath = '/path/to/codeception-adapter/tests/e2e/Codeception_With_Suite_Overridings/src/Covered/functions.php';
            $testPath = '/path/to/codeception-adapter/tests/e2e/Codeception_With_Suite_Overridings/tests/unit/Covered/FormatNameFunctionTest.php';

            return [
                $indexXmlPath,
                $junitXmlPath,
                SyntheticTrace::forSource(
                    realPath: $sourcePath,
                    hasTest: true,
                    tests: new TestLocations(
                        byLine: [
                            7 => [
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\FormatNameFunctionTest:testFormatFullName',
                                    filePath: $testPath,
                                    executionTime: 0.002082,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\FormatNameFunctionTest:testFormatFirstNameOnly',
                                    filePath: $testPath,
                                    executionTime: 0.002082,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\FormatNameFunctionTest:testFormatLastNameOnly',
                                    filePath: $testPath,
                                    executionTime: 0.002082,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\FormatNameFunctionTest:testFormatEmptyNames',
                                    filePath: $testPath,
                                    executionTime: 0.002082,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\FormatNameFunctionTest:testFormatWithSpaces',
                                    filePath: $testPath,
                                    executionTime: 0.002082,
                                ),
                            ],
                            8 => [
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\FormatNameFunctionTest:testFormatEmptyNames',
                                    filePath: $testPath,
                                    executionTime: 0.002082,
                                ),
                            ],
                            11 => [
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\FormatNameFunctionTest:testFormatFullName',
                                    filePath: $testPath,
                                    executionTime: 0.002082,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\FormatNameFunctionTest:testFormatFirstNameOnly',
                                    filePath: $testPath,
                                    executionTime: 0.002082,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\FormatNameFunctionTest:testFormatLastNameOnly',
                                    filePath: $testPath,
                                    executionTime: 0.002082,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\FormatNameFunctionTest:testFormatWithSpaces',
                                    filePath: $testPath,
                                    executionTime: 0.002082,
                                ),
                            ],
                            12 => [
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\FormatNameFunctionTest:testFormatLastNameOnly',
                                    filePath: $testPath,
                                    executionTime: 0.002082,
                                ),
                            ],
                            15 => [
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\FormatNameFunctionTest:testFormatFullName',
                                    filePath: $testPath,
                                    executionTime: 0.002082,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\FormatNameFunctionTest:testFormatFirstNameOnly',
                                    filePath: $testPath,
                                    executionTime: 0.002082,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\FormatNameFunctionTest:testFormatWithSpaces',
                                    filePath: $testPath,
                                    executionTime: 0.002082,
                                ),
                            ],
                            16 => [
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\FormatNameFunctionTest:testFormatFirstNameOnly',
                                    filePath: $testPath,
                                    executionTime: 0.002082,
                                ),
                            ],
                            19 => [
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\FormatNameFunctionTest:testFormatFullName',
                                    filePath: $testPath,
                                    executionTime: 0.002082,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\FormatNameFunctionTest:testFormatWithSpaces',
                                    filePath: $testPath,
                                    executionTime: 0.002082,
                                ),
                            ],
                        ],
                        byMethod: [],
                    ),
                ),
            ];
        })();

        yield 'covered class (root level)' => (static function () use ($indexXmlPath, $junitXmlPath) {
            $sourcePath = '/path/to/codeception-adapter/tests/e2e/Codeception_With_Suite_Overridings/src/Database.php';
            $testPath = '/path/to/codeception-adapter/tests/e2e/Codeception_With_Suite_Overridings/tests/integration/DatabaseTest.php';

            return [
                $indexXmlPath,
                $junitXmlPath,
                SyntheticTrace::forSource(
                    realPath: $sourcePath,
                    hasTest: true,
                    tests: new TestLocations(
                        byLine: [
                            15 => [
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\DatabaseTest:testGetStuffWithoutLimit',
                                    filePath: $testPath,
                                    executionTime: 0.006812,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\DatabaseTest:testGetStuffWithLimit',
                                    filePath: $testPath,
                                    executionTime: 0.006812,
                                ),
                            ],
                            20 => [
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\DatabaseTest:testGetStuffWithoutLimit',
                                    filePath: $testPath,
                                    executionTime: 0.006812,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\DatabaseTest:testGetStuffWithLimit',
                                    filePath: $testPath,
                                    executionTime: 0.006812,
                                ),
                            ],
                            21 => [
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\DatabaseTest:testGetStuffWithoutLimit',
                                    filePath: $testPath,
                                    executionTime: 0.006812,
                                ),
                            ],
                            23 => [
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\DatabaseTest:testGetStuffWithLimit',
                                    filePath: $testPath,
                                    executionTime: 0.006812,
                                ),
                            ],
                            25 => [
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\DatabaseTest:testGetStuffWithoutLimit',
                                    filePath: $testPath,
                                    executionTime: 0.006812,
                                ),
                                new TestLocation(
                                    method: 'Codeception_With_Suite_Overridings\DatabaseTest:testGetStuffWithLimit',
                                    filePath: $testPath,
                                    executionTime: 0.006812,
                                ),
                            ],
                        ],
                        byMethod: [
                            '__construct' => new SourceMethodLineRange(13, 16),
                            'getStuff' => new SourceMethodLineRange(18, 26),
                        ],
                    ),
                ),
            ];
        })();
    }
}
