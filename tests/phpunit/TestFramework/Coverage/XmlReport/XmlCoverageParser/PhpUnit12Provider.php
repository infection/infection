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

namespace Infection\Tests\TestFramework\Coverage\XmlReport\XmlCoverageParser;

use Infection\AbstractTestFramework\Coverage\TestLocation;
use Infection\CannotBeInstantiated;
use Infection\TestFramework\SafeDOMXPath;
use Infection\TestFramework\Tracing\Trace\SourceMethodLineRange;
use Infection\TestFramework\Tracing\Trace\TestLocations;
use Symfony\Component\Filesystem\Path;

final class PhpUnit12Provider
{
    use CannotBeInstantiated;

    private const FIXTURES_DIR = __DIR__ . '/../../Fixtures';

    public static function infoProvider(): iterable
    {
        yield 'covered class' => [
            SafeDOMXPath::fromFile(
                Path::canonicalize(self::FIXTURES_DIR . '/phpunit-12-0/coverage-xml/Covered/Calculator.php.xml'),
                namespace: 'p',
            ),
            new TestLocations(
                byLine: [
                    9 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\CalculatorTest::test_add#0',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\CalculatorTest::test_add#1',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\CalculatorTest::test_add#2',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    14 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\CalculatorTest::test_subtract#0',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\CalculatorTest::test_subtract#1',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\CalculatorTest::test_subtract#2',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    19 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\CalculatorTest::test_multiply',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    24 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\CalculatorTest::test_divide',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\CalculatorTest::test_divide_by_zero_throws_exception',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    25 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\CalculatorTest::test_divide_by_zero_throws_exception',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    28 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\CalculatorTest::test_divide',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    33 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\CalculatorTest::test_is_positive',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    38 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\CalculatorTest::test_absolute',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\CalculatorTest::test_absolute_zero',
                            filePath: null,
                            executionTime: null,
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
        ];

        yield 'uncovered class' => [
            SafeDOMXPath::fromFile(
                Path::canonicalize(self::FIXTURES_DIR . '/phpunit-12-0/coverage-xml/Uncovered/Calculator.php.xml'),
                namespace: 'p',
            ),
            new TestLocations(
                byLine: [],
                byMethod: [],
            ),
        ];

        // PHPUnit 12.5 - No data set info in test names
        yield 'PHPUnit 12.5 covered class' => [
            SafeDOMXPath::fromFile(
                Path::canonicalize(self::FIXTURES_DIR . '/phpunit-12-5/Covered/Calculator.php.xml'),
                namespace: 'p',
            ),
            new TestLocations(
                byLine: [
                    9 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_5\Tests\Covered\CalculatorTest::test_add',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    14 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_5\Tests\Covered\CalculatorTest::test_subtract',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    19 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_5\Tests\Covered\CalculatorTest::test_multiply',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    24 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_5\Tests\Covered\CalculatorTest::test_divide',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_5\Tests\Covered\CalculatorTest::test_divide_by_zero_throws_exception',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    25 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_5\Tests\Covered\CalculatorTest::test_divide_by_zero_throws_exception',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    28 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_5\Tests\Covered\CalculatorTest::test_divide',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    33 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_5\Tests\Covered\CalculatorTest::test_is_positive',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    38 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_5\Tests\Covered\CalculatorTest::test_absolute',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_5\Tests\Covered\CalculatorTest::test_absolute_zero',
                            filePath: null,
                            executionTime: null,
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
        ];

        yield 'PHPUnit 12.0 covered trait' => [
            SafeDOMXPath::fromFile(
                Path::canonicalize(self::FIXTURES_DIR . '/phpunit-12-0/coverage-xml/Covered/LoggerTrait.php.xml'),
                namespace: 'p',
            ),
            new TestLocations(
                byLine: [
                    11 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_add_user_successfully',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_add_user_with_empty_name_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_add_user_with_empty_email_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_add_duplicate_user_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_remove_non_existent_user_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_get_user_returns_user_data',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_user_exists',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_logger_trait_methods',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_log_method_is_public',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    16 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_add_user_with_empty_name_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_add_user_with_empty_email_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_add_duplicate_user_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_remove_non_existent_user_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_logger_trait_methods',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_log_method_is_public',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    21 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_add_duplicate_user_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_logger_trait_methods',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    26 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_add_user_successfully',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_logger_trait_methods',
                            filePath: null,
                            executionTime: null,
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
        ];

        yield 'covered class with trait' => [
            SafeDOMXPath::fromFile(
                Path::canonicalize(self::FIXTURES_DIR . '/phpunit-12-0/coverage-xml/Covered/UserService.php.xml'),
                namespace: 'p',
            ),
            new TestLocations(
                byLine: [
                    13 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_add_user_successfully',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_add_user_with_empty_name_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_add_user_with_empty_email_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_add_duplicate_user_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_get_user_returns_user_data',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_user_exists',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_logger_trait_methods',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    14 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_add_user_with_empty_name_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_add_user_with_empty_email_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    15 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_add_user_with_empty_name_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_add_user_with_empty_email_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    18 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_add_user_successfully',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_add_duplicate_user_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_get_user_returns_user_data',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_user_exists',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_logger_trait_methods',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    19 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_add_duplicate_user_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    20 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_add_duplicate_user_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    23 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_add_user_successfully',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_add_duplicate_user_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_get_user_returns_user_data',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_user_exists',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_logger_trait_methods',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    24 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_add_user_successfully',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_add_duplicate_user_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_get_user_returns_user_data',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_user_exists',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_logger_trait_methods',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    25 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_add_user_successfully',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_add_duplicate_user_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_get_user_returns_user_data',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_user_exists',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_logger_trait_methods',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    30 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_remove_non_existent_user_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    31 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_remove_non_existent_user_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    32 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_remove_non_existent_user_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    35 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    36 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    37 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    42 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_get_user_returns_user_data',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_get_user_returns_null_for_non_existent_user',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    47 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_add_user_successfully',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_add_duplicate_user_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_remove_non_existent_user_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_get_user_returns_user_data',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_user_exists',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_logger_trait_methods',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    52 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_add_user_successfully',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_add_user_with_empty_name_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_add_user_with_empty_email_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_add_duplicate_user_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                            filePath: null,
                            executionTime: null,
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
        ];

        yield 'covered function' => [
            SafeDOMXPath::fromFile(
                Path::canonicalize(self::FIXTURES_DIR . '/phpunit-12-0/coverage-xml/Covered/functions.php.xml'),
                namespace: 'p',
            ),
            new TestLocations(
                byLine: [
                    7 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\FunctionsTest::test_format_name_with_both_names',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\FunctionsTest::test_format_name_with_first_name_only',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\FunctionsTest::test_format_name_with_last_name_only',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\FunctionsTest::test_format_name_with_no_names',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    8 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\FunctionsTest::test_format_name_with_no_names',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    11 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\FunctionsTest::test_format_name_with_both_names',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\FunctionsTest::test_format_name_with_first_name_only',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\FunctionsTest::test_format_name_with_last_name_only',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    12 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\FunctionsTest::test_format_name_with_last_name_only',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    15 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\FunctionsTest::test_format_name_with_both_names',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\FunctionsTest::test_format_name_with_first_name_only',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    16 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\FunctionsTest::test_format_name_with_first_name_only',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    19 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\FunctionsTest::test_format_name_with_both_names',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                ],
                byMethod: [],
            ),
        ];

        yield 'uncovered trait' => [
            SafeDOMXPath::fromFile(
                Path::canonicalize(self::FIXTURES_DIR . '/phpunit-12-0/coverage-xml/Uncovered/LoggerTrait.php.xml'),
                namespace: 'p',
            ),
            new TestLocations(
                byLine: [],
                byMethod: [],
            ),
        ];

        yield 'uncovered class with trait' => [
            SafeDOMXPath::fromFile(
                Path::canonicalize(self::FIXTURES_DIR . '/phpunit-12-0/coverage-xml/Uncovered/UserService.php.xml'),
                namespace: 'p',
            ),
            new TestLocations(
                byLine: [],
                byMethod: [],
            ),
        ];

        yield 'uncovered functions' => [
            SafeDOMXPath::fromFile(
                Path::canonicalize(self::FIXTURES_DIR . '/phpunit-12-0/coverage-xml/Uncovered/functions.php.xml'),
                namespace: 'p',
            ),
            new TestLocations(
                byLine: [],
                byMethod: [],
            ),
        ];
    }
}
