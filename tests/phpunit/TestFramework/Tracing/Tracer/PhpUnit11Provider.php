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

final class PhpUnit11Provider
{
    use CannotBeInstantiated;

    private const FIXTURES_DIR = __DIR__ . '/../../Coverage/Fixtures';

    public static function infoProvider(): iterable
    {
        $coverageDirectory = Path::canonicalize(self::FIXTURES_DIR . '/phpunit-11/');

        $indexXmlPath = Path::canonicalize($coverageDirectory . '/coverage-xml/index.xml');
        $junitXmlPath = Path::canonicalize($coverageDirectory . '/junit.xml');

        yield 'covered class' => (static function () use ($indexXmlPath, $junitXmlPath) {
            $sourcePath = '/path/to/infection/tests/e2e/PHPUnit_11/src/Covered/Calculator.php';
            $testPath = '/path/to/infection/tests/e2e/PHPUnit_11/tests/Covered/CalculatorTest.php';

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
                                    method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\CalculatorTest::test_add#0',
                                    filePath: $testPath,
                                    executionTime: 0.021260,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\CalculatorTest::test_add#1',
                                    filePath: $testPath,
                                    executionTime: 0.021260,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\CalculatorTest::test_add#2',
                                    filePath: $testPath,
                                    executionTime: 0.021260,
                                ),
                            ],
                            14 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\CalculatorTest::test_subtract#0',
                                    filePath: $testPath,
                                    executionTime: 0.021260,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\CalculatorTest::test_subtract#1',
                                    filePath: $testPath,
                                    executionTime: 0.021260,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\CalculatorTest::test_subtract#2',
                                    filePath: $testPath,
                                    executionTime: 0.021260,
                                ),
                            ],
                            19 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\CalculatorTest::test_multiply',
                                    filePath: $testPath,
                                    executionTime: 0.021260,
                                ),
                            ],
                            24 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\CalculatorTest::test_divide',
                                    filePath: $testPath,
                                    executionTime: 0.021260,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\CalculatorTest::test_divide_by_zero_throws_exception',
                                    filePath: $testPath,
                                    executionTime: 0.021260,
                                ),
                            ],
                            25 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\CalculatorTest::test_divide_by_zero_throws_exception',
                                    filePath: $testPath,
                                    executionTime: 0.021260,
                                ),
                            ],
                            28 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\CalculatorTest::test_divide',
                                    filePath: $testPath,
                                    executionTime: 0.021260,
                                ),
                            ],
                            33 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\CalculatorTest::test_is_positive',
                                    filePath: $testPath,
                                    executionTime: 0.021260,
                                ),
                            ],
                            38 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\CalculatorTest::test_absolute',
                                    filePath: $testPath,
                                    executionTime: 0.021260,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\CalculatorTest::test_absolute_zero',
                                    filePath: $testPath,
                                    executionTime: 0.021260,
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
            $sourcePath = '/path/to/infection/tests/e2e/PHPUnit_11/src/Covered/LoggerTrait.php';
            $testPath = '/path/to/infection/tests/e2e/PHPUnit_11/tests/Covered/UserServiceTest.php';

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
                                    method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_add_user_successfully',
                                    filePath: $testPath,
                                    executionTime: 0.013159,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_add_user_with_empty_name_fails',
                                    filePath: $testPath,
                                    executionTime: 0.013159,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_add_user_with_empty_email_fails',
                                    filePath: $testPath,
                                    executionTime: 0.013159,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_add_duplicate_user_fails',
                                    filePath: $testPath,
                                    executionTime: 0.013159,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                                    filePath: $testPath,
                                    executionTime: 0.013159,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_remove_non_existent_user_fails',
                                    filePath: $testPath,
                                    executionTime: 0.013159,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_get_user_returns_user_data',
                                    filePath: $testPath,
                                    executionTime: 0.013159,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_user_exists',
                                    filePath: $testPath,
                                    executionTime: 0.013159,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_logger_trait_methods',
                                    filePath: $testPath,
                                    executionTime: 0.013159,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_log_method_is_public',
                                    filePath: $testPath,
                                    executionTime: 0.013159,
                                ),
                            ],
                            16 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_add_user_with_empty_name_fails',
                                    filePath: $testPath,
                                    executionTime: 0.013159,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_add_user_with_empty_email_fails',
                                    filePath: $testPath,
                                    executionTime: 0.013159,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_add_duplicate_user_fails',
                                    filePath: $testPath,
                                    executionTime: 0.013159,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                                    filePath: $testPath,
                                    executionTime: 0.013159,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_remove_non_existent_user_fails',
                                    filePath: $testPath,
                                    executionTime: 0.013159,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_logger_trait_methods',
                                    filePath: $testPath,
                                    executionTime: 0.013159,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_log_method_is_public',
                                    filePath: $testPath,
                                    executionTime: 0.013159,
                                ),
                            ],
                            21 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_add_duplicate_user_fails',
                                    filePath: $testPath,
                                    executionTime: 0.013159,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                                    filePath: $testPath,
                                    executionTime: 0.013159,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_logger_trait_methods',
                                    filePath: $testPath,
                                    executionTime: 0.013159,
                                ),
                            ],
                            26 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_add_user_successfully',
                                    filePath: $testPath,
                                    executionTime: 0.013159,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_logger_trait_methods',
                                    filePath: $testPath,
                                    executionTime: 0.013159,
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
            $sourcePath = '/path/to/infection/tests/e2e/PHPUnit_11/src/Covered/UserService.php';
            $testPath = '/path/to/infection/tests/e2e/PHPUnit_11/tests/Covered/UserServiceTest.php';

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
                                    method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_add_user_successfully',
                                    filePath: $testPath,
                                    executionTime: 0.013159,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_add_user_with_empty_name_fails',
                                    filePath: $testPath,
                                    executionTime: 0.013159,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_add_user_with_empty_email_fails',
                                    filePath: $testPath,
                                    executionTime: 0.013159,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_add_duplicate_user_fails',
                                    filePath: $testPath,
                                    executionTime: 0.013159,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                                    filePath: $testPath,
                                    executionTime: 0.013159,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_get_user_returns_user_data',
                                    filePath: $testPath,
                                    executionTime: 0.013159,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_user_exists',
                                    filePath: $testPath,
                                    executionTime: 0.013159,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_logger_trait_methods',
                                    filePath: $testPath,
                                    executionTime: 0.013159,
                                ),
                            ],
                            14 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_add_user_with_empty_name_fails',
                                    filePath: $testPath,
                                    executionTime: 0.013159,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_add_user_with_empty_email_fails',
                                    filePath: $testPath,
                                    executionTime: 0.013159,
                                ),
                            ],
                            15 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_add_user_with_empty_name_fails',
                                    filePath: $testPath,
                                    executionTime: 0.013159,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_add_user_with_empty_email_fails',
                                    filePath: $testPath,
                                    executionTime: 0.013159,
                                ),
                            ],
                            18 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_add_user_successfully',
                                    filePath: $testPath,
                                    executionTime: 0.013159,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_add_duplicate_user_fails',
                                    filePath: $testPath,
                                    executionTime: 0.013159,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                                    filePath: $testPath,
                                    executionTime: 0.013159,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_get_user_returns_user_data',
                                    filePath: $testPath,
                                    executionTime: 0.013159,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_user_exists',
                                    filePath: $testPath,
                                    executionTime: 0.013159,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_logger_trait_methods',
                                    filePath: $testPath,
                                    executionTime: 0.013159,
                                ),
                            ],
                            19 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_add_duplicate_user_fails',
                                    filePath: $testPath,
                                    executionTime: 0.013159,
                                ),
                            ],
                            20 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_add_duplicate_user_fails',
                                    filePath: $testPath,
                                    executionTime: 0.013159,
                                ),
                            ],
                            23 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_add_user_successfully',
                                    filePath: $testPath,
                                    executionTime: 0.013159,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_add_duplicate_user_fails',
                                    filePath: $testPath,
                                    executionTime: 0.013159,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                                    filePath: $testPath,
                                    executionTime: 0.013159,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_get_user_returns_user_data',
                                    filePath: $testPath,
                                    executionTime: 0.013159,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_user_exists',
                                    filePath: $testPath,
                                    executionTime: 0.013159,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_logger_trait_methods',
                                    filePath: $testPath,
                                    executionTime: 0.013159,
                                ),
                            ],
                            24 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_add_user_successfully',
                                    filePath: $testPath,
                                    executionTime: 0.013159,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_add_duplicate_user_fails',
                                    filePath: $testPath,
                                    executionTime: 0.013159,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                                    filePath: $testPath,
                                    executionTime: 0.013159,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_get_user_returns_user_data',
                                    filePath: $testPath,
                                    executionTime: 0.013159,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_user_exists',
                                    filePath: $testPath,
                                    executionTime: 0.013159,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_logger_trait_methods',
                                    filePath: $testPath,
                                    executionTime: 0.013159,
                                ),
                            ],
                            25 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_add_user_successfully',
                                    filePath: $testPath,
                                    executionTime: 0.013159,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_add_duplicate_user_fails',
                                    filePath: $testPath,
                                    executionTime: 0.013159,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                                    filePath: $testPath,
                                    executionTime: 0.013159,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_get_user_returns_user_data',
                                    filePath: $testPath,
                                    executionTime: 0.013159,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_user_exists',
                                    filePath: $testPath,
                                    executionTime: 0.013159,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_logger_trait_methods',
                                    filePath: $testPath,
                                    executionTime: 0.013159,
                                ),
                            ],
                            30 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                                    filePath: $testPath,
                                    executionTime: 0.013159,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_remove_non_existent_user_fails',
                                    filePath: $testPath,
                                    executionTime: 0.013159,
                                ),
                            ],
                            31 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_remove_non_existent_user_fails',
                                    filePath: $testPath,
                                    executionTime: 0.013159,
                                ),
                            ],
                            32 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_remove_non_existent_user_fails',
                                    filePath: $testPath,
                                    executionTime: 0.013159,
                                ),
                            ],
                            35 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                                    filePath: $testPath,
                                    executionTime: 0.013159,
                                ),
                            ],
                            36 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                                    filePath: $testPath,
                                    executionTime: 0.013159,
                                ),
                            ],
                            37 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                                    filePath: $testPath,
                                    executionTime: 0.013159,
                                ),
                            ],
                            42 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_get_user_returns_user_data',
                                    filePath: $testPath,
                                    executionTime: 0.013159,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_get_user_returns_null_for_non_existent_user',
                                    filePath: $testPath,
                                    executionTime: 0.013159,
                                ),
                            ],
                            47 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_add_user_successfully',
                                    filePath: $testPath,
                                    executionTime: 0.013159,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_add_duplicate_user_fails',
                                    filePath: $testPath,
                                    executionTime: 0.013159,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                                    filePath: $testPath,
                                    executionTime: 0.013159,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_remove_non_existent_user_fails',
                                    filePath: $testPath,
                                    executionTime: 0.013159,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_get_user_returns_user_data',
                                    filePath: $testPath,
                                    executionTime: 0.013159,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_user_exists',
                                    filePath: $testPath,
                                    executionTime: 0.013159,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_logger_trait_methods',
                                    filePath: $testPath,
                                    executionTime: 0.013159,
                                ),
                            ],
                            52 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_add_user_successfully',
                                    filePath: $testPath,
                                    executionTime: 0.013159,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_add_user_with_empty_name_fails',
                                    filePath: $testPath,
                                    executionTime: 0.013159,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_add_user_with_empty_email_fails',
                                    filePath: $testPath,
                                    executionTime: 0.013159,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_add_duplicate_user_fails',
                                    filePath: $testPath,
                                    executionTime: 0.013159,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                                    filePath: $testPath,
                                    executionTime: 0.013159,
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
            $sourcePath = '/path/to/infection/tests/e2e/PHPUnit_11/src/Covered/functions.php';
            $testPath = '/path/to/infection/tests/e2e/PHPUnit_11/tests/Covered/FunctionsTest.php';

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
                                    method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\FunctionsTest::test_format_name_with_both_names',
                                    filePath: $testPath,
                                    executionTime: 0.003421,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\FunctionsTest::test_format_name_with_first_name_only',
                                    filePath: $testPath,
                                    executionTime: 0.003421,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\FunctionsTest::test_format_name_with_last_name_only',
                                    filePath: $testPath,
                                    executionTime: 0.003421,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\FunctionsTest::test_format_name_with_no_names',
                                    filePath: $testPath,
                                    executionTime: 0.003421,
                                ),
                            ],
                            8 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\FunctionsTest::test_format_name_with_no_names',
                                    filePath: $testPath,
                                    executionTime: 0.003421,
                                ),
                            ],
                            11 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\FunctionsTest::test_format_name_with_both_names',
                                    filePath: $testPath,
                                    executionTime: 0.003421,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\FunctionsTest::test_format_name_with_first_name_only',
                                    filePath: $testPath,
                                    executionTime: 0.003421,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\FunctionsTest::test_format_name_with_last_name_only',
                                    filePath: $testPath,
                                    executionTime: 0.003421,
                                ),
                            ],
                            12 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\FunctionsTest::test_format_name_with_last_name_only',
                                    filePath: $testPath,
                                    executionTime: 0.003421,
                                ),
                            ],
                            15 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\FunctionsTest::test_format_name_with_both_names',
                                    filePath: $testPath,
                                    executionTime: 0.003421,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\FunctionsTest::test_format_name_with_first_name_only',
                                    filePath: $testPath,
                                    executionTime: 0.003421,
                                ),
                            ],
                            16 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\FunctionsTest::test_format_name_with_first_name_only',
                                    filePath: $testPath,
                                    executionTime: 0.003421,
                                ),
                            ],
                            19 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\FunctionsTest::test_format_name_with_both_names',
                                    filePath: $testPath,
                                    executionTime: 0.003421,
                                ),
                            ],
                        ],
                        byMethod: [],
                    ),
                ),
            ];
        })();

        yield 'uncovered class' => (static function () use ($indexXmlPath, $junitXmlPath) {
            $sourcePath = '/path/to/infection/tests/e2e/PHPUnit_11/src/Uncovered/Calculator.php';

            return [
                $indexXmlPath,
                $junitXmlPath,
                SyntheticTrace::forSource(
                    realPath: $sourcePath,
                    hasTest: false,
                    tests: new TestLocations(
                        byLine: [],
                        byMethod: [],
                    ),
                ),
            ];
        })();

        yield 'uncovered trait' => (static function () use ($indexXmlPath, $junitXmlPath) {
            $sourcePath = '/path/to/infection/tests/e2e/PHPUnit_11/src/Uncovered/LoggerTrait.php';

            return [
                $indexXmlPath,
                $junitXmlPath,
                SyntheticTrace::forSource(
                    realPath: $sourcePath,
                    hasTest: false,
                    tests: new TestLocations(
                        byLine: [],
                        byMethod: [],
                    ),
                ),
            ];
        })();

        yield 'uncovered class with trait' => (static function () use ($indexXmlPath, $junitXmlPath) {
            $sourcePath = '/path/to/infection/tests/e2e/PHPUnit_11/src/Uncovered/UserService.php';

            return [
                $indexXmlPath,
                $junitXmlPath,
                SyntheticTrace::forSource(
                    realPath: $sourcePath,
                    hasTest: false,
                    tests: new TestLocations(
                        byLine: [],
                        byMethod: [],
                    ),
                ),
            ];
        })();

        yield 'uncovered functions' => (static function () use ($indexXmlPath, $junitXmlPath) {
            $sourcePath = '/path/to/infection/tests/e2e/PHPUnit_11/src/Uncovered/functions.php';

            return [
                $indexXmlPath,
                $junitXmlPath,
                SyntheticTrace::forSource(
                    realPath: $sourcePath,
                    hasTest: false,
                    tests: new TestLocations(
                        byLine: [],
                        byMethod: [],
                    ),
                ),
            ];
        })();
    }
}
