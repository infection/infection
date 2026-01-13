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

namespace Infection\Tests\TestFramework\Tracing;

use Infection\AbstractTestFramework\Coverage\TestLocation;
use Infection\AbstractTestFramework\TestFrameworkAdapter;
use Infection\FileSystem\FileSystem;
use Infection\TestFramework\Coverage\CoveredTraceProvider;
use Infection\TestFramework\Coverage\JUnit\JUnitTestExecutionInfoAdder;
use Infection\TestFramework\Coverage\JUnit\JUnitTestFileDataProvider;
use Infection\TestFramework\Coverage\JUnit\MemoizedTestFileDataProvider;
use Infection\TestFramework\Coverage\Locator\FixedLocator;
use Infection\TestFramework\Coverage\XmlReport\IndexXmlCoverageParser;
use Infection\TestFramework\Coverage\XmlReport\PhpUnitXmlCoverageTraceProvider;
use Infection\TestFramework\Coverage\XmlReport\XmlCoverageParser;
use Infection\TestFramework\SafeDOMXPath;
use Infection\TestFramework\Tracing\Trace\SourceMethodLineRange;
use Infection\TestFramework\Tracing\Trace\TestLocations;
use Infection\TestFramework\Tracing\Trace\Trace;
use Infection\TestFramework\Tracing\TraceProviderAdapterTracer;
use Infection\TestFramework\Tracing\Tracer;
use Infection\Tests\TestFramework\Tracing\Trace\SyntheticTrace;
use Infection\Tests\TestFramework\Tracing\Trace\TraceAssertion;
use Infection\Tests\TestingUtility\FileSystem\MockSplFileInfo;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use function Safe\realpath;
use SplFileInfo;
use Symfony\Component\Filesystem\Path;

#[Group('integration')]
#[CoversNothing]
final class TracerIntegrationTest extends TestCase
{
    private const FIXTURES_DIR = __DIR__ . '/../Coverage/Fixtures';

    #[DataProvider('traceProvider')]
    public function test_it_can_create_a_trace(
        string $indexXmlPath,
        string $junitXmlPath,
        Trace $expected,
    ): void {
        $tracer = $this->createTracer(
            $indexXmlPath,
            $junitXmlPath,
        );

        $actual = $tracer->trace(
            $expected->getSourceFileInfo(),
        );

        TraceAssertion::assertEquals($expected, $actual);
    }

    public static function traceProvider(): iterable
    {
        $coverageDirectory = Path::canonicalize(self::FIXTURES_DIR . '/phpunit-09/');

        $indexXmlPath = Path::canonicalize($coverageDirectory . '/coverage-xml/index.xml');
        $junitXmlPath = Path::canonicalize($coverageDirectory . '/junit.xml');

        yield 'covered class' => (static function () use ($indexXmlPath, $junitXmlPath) {
            $sourcePath = '/path/to/infection/tests/e2e/PHPUnit_09-3/src/Covered/Calculator.php';
            $testPath = '/path/to/infection/tests/e2e/PHPUnit_09-3/tests/Covered/CalculatorTest.php';

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
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\CalculatorTest::test_add with data set #0',
                                    filePath: $testPath,
                                    executionTime: 0.006446
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\CalculatorTest::test_add with data set #1',
                                    filePath: $testPath,
                                    executionTime: 0.006446
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\CalculatorTest::test_add with data set #2',
                                    filePath: $testPath,
                                    executionTime: 0.006446
                                ),
                            ],
                            14 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\CalculatorTest::test_subtract with data set #0',
                                    filePath: $testPath,
                                    executionTime: 0.006446
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\CalculatorTest::test_subtract with data set #1',
                                    filePath: $testPath,
                                    executionTime: 0.006446
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\CalculatorTest::test_subtract with data set #2',
                                    filePath: $testPath,
                                    executionTime: 0.006446
                                ),
                            ],
                            19 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\CalculatorTest::test_multiply',
                                    filePath: $testPath,
                                    executionTime: 0.006446
                                ),
                            ],
                            24 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\CalculatorTest::test_divide',
                                    filePath: $testPath,
                                    executionTime: 0.006446
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\CalculatorTest::test_divide_by_zero_throws_exception',
                                    filePath: $testPath,
                                    executionTime: 0.006446
                                ),
                            ],
                            25 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\CalculatorTest::test_divide_by_zero_throws_exception',
                                    filePath: $testPath,
                                    executionTime: 0.006446
                                ),
                            ],
                            28 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\CalculatorTest::test_divide',
                                    filePath: $testPath,
                                    executionTime: 0.006446
                                ),
                            ],
                            33 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\CalculatorTest::test_is_positive',
                                    filePath: $testPath,
                                    executionTime: 0.006446
                                ),
                            ],
                            38 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\CalculatorTest::test_absolute',
                                    filePath: $testPath,
                                    executionTime: 0.006446
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\CalculatorTest::test_absolute_zero',
                                    filePath: $testPath,
                                    executionTime: 0.006446
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

        yield 'covered trait' => [
            SafeDOMXPath::fromFile(
                Path::canonicalize(self::FIXTURES_DIR . '/phpunit-09/coverage-xml/Covered/LoggerTrait.php.xml'),
                namespace: 'p',
            ),
            new TestLocations(
                byLine: [
                    11 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_add_user_successfully',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_add_user_with_empty_name_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_add_user_with_empty_email_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_add_duplicate_user_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_remove_non_existent_user_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_get_user_returns_user_data',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_user_exists',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_logger_trait_methods',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_log_method_is_public',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    16 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_add_user_with_empty_name_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_add_user_with_empty_email_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_add_duplicate_user_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_remove_non_existent_user_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_logger_trait_methods',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_log_method_is_public',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    21 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_add_duplicate_user_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_logger_trait_methods',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    26 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_add_user_successfully',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_logger_trait_methods',
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
                Path::canonicalize(self::FIXTURES_DIR . '/phpunit-09/coverage-xml/Covered/UserService.php.xml'),
                namespace: 'p',
            ),
            new TestLocations(
                byLine: [
                    13 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_add_user_successfully',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_add_user_with_empty_name_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_add_user_with_empty_email_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_add_duplicate_user_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_get_user_returns_user_data',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_user_exists',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_logger_trait_methods',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    14 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_add_user_with_empty_name_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_add_user_with_empty_email_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    15 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_add_user_with_empty_name_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_add_user_with_empty_email_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    18 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_add_user_successfully',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_add_duplicate_user_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_get_user_returns_user_data',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_user_exists',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_logger_trait_methods',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    19 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_add_duplicate_user_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    20 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_add_duplicate_user_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    23 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_add_user_successfully',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_add_duplicate_user_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_get_user_returns_user_data',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_user_exists',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_logger_trait_methods',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    24 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_add_user_successfully',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_add_duplicate_user_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_get_user_returns_user_data',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_user_exists',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_logger_trait_methods',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    25 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_add_user_successfully',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_add_duplicate_user_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_get_user_returns_user_data',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_user_exists',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_logger_trait_methods',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    30 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_remove_non_existent_user_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    31 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_remove_non_existent_user_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    32 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_remove_non_existent_user_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    35 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    36 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    37 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    42 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_get_user_returns_user_data',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_get_user_returns_null_for_non_existent_user',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    47 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_add_user_successfully',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_add_duplicate_user_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_remove_non_existent_user_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_get_user_returns_user_data',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_user_exists',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_logger_trait_methods',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    52 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_add_user_successfully',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_add_user_with_empty_name_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_add_user_with_empty_email_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_add_duplicate_user_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_remove_user_successfully',
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
                Path::canonicalize(self::FIXTURES_DIR . '/phpunit-09/coverage-xml/Covered/functions.php.xml'),
                namespace: 'p',
            ),
            new TestLocations(
                byLine: [
                    7 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\FunctionsTest::test_format_name_with_both_names',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\FunctionsTest::test_format_name_with_first_name_only',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\FunctionsTest::test_format_name_with_last_name_only',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\FunctionsTest::test_format_name_with_no_names',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    8 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\FunctionsTest::test_format_name_with_no_names',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    11 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\FunctionsTest::test_format_name_with_both_names',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\FunctionsTest::test_format_name_with_first_name_only',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\FunctionsTest::test_format_name_with_last_name_only',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    12 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\FunctionsTest::test_format_name_with_last_name_only',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    15 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\FunctionsTest::test_format_name_with_both_names',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\FunctionsTest::test_format_name_with_first_name_only',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    16 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\FunctionsTest::test_format_name_with_first_name_only',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    19 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\FunctionsTest::test_format_name_with_both_names',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                ],
                byMethod: [],
            ),
        ];

        yield 'uncovered class' => [
            SafeDOMXPath::fromFile(
                Path::canonicalize(self::FIXTURES_DIR . '/phpunit-09/coverage-xml/Uncovered/Calculator.php.xml'),
                namespace: 'p',
            ),
            new TestLocations(
                byLine: [],
                byMethod: [],
            ),
        ];

        yield 'uncovered trait' => [
            SafeDOMXPath::fromFile(
                Path::canonicalize(self::FIXTURES_DIR . '/phpunit-09/coverage-xml/Uncovered/LoggerTrait.php.xml'),
                namespace: 'p',
            ),
            new TestLocations(
                byLine: [],
                byMethod: [],
            ),
        ];

        yield 'uncovered class with trait' => [
            SafeDOMXPath::fromFile(
                Path::canonicalize(self::FIXTURES_DIR . '/phpunit-09/coverage-xml/Uncovered/UserService.php.xml'),
                namespace: 'p',
            ),
            new TestLocations(
                byLine: [],
                byMethod: [],
            ),
        ];

        yield 'uncovered functions' => [
            SafeDOMXPath::fromFile(
                Path::canonicalize(self::FIXTURES_DIR . '/phpunit-09/coverage-xml/Uncovered/functions.php.xml'),
                namespace: 'p',
            ),
            new TestLocations(
                byLine: [],
                byMethod: [],
            ),
        ];
    }

    private function createTracer(
        string $indexXmlPath,
        string $junitXmlPath,
    ): Tracer {
        $testFrameworkAdapterStub = $this->createStub(TestFrameworkAdapter::class);
        $testFrameworkAdapterStub
            ->method('hasJUnitReport')
            ->willReturn(true);

        $fileSystemStub = $this->createFileSystemStub();

        return new TraceProviderAdapterTracer(
            new CoveredTraceProvider(
                new PhpUnitXmlCoverageTraceProvider(
                    indexLocator: new FixedLocator($indexXmlPath),
                    indexParser: new IndexXmlCoverageParser(
                        isSourceFiltered: false,
                        fileSystem: $fileSystemStub,
                    ),
                    parser: new XmlCoverageParser(
                        $fileSystemStub,
                    ),
                ),
                new JUnitTestExecutionInfoAdder(
                    $testFrameworkAdapterStub,
                    new MemoizedTestFileDataProvider(
                        new JUnitTestFileDataProvider(
                            new FixedLocator($junitXmlPath),
                        ),
                    ),
                ),
            ),
        );
    }

    private function createFileSystemStub(): FileSystem
    {
        $fileSystem = new FileSystem();

        $fileSystemStub = $this->createStub(FileSystem::class);
        $fileSystemStub
            ->method('isReadableFile')
            ->willReturnCallback($fileSystem->isReadableFile(...));
        $fileSystemStub
            ->method('readFile')
            ->willReturnCallback($fileSystem->readFile(...));

        // We are only interested in mocking the realPath check!
        // In this test, we do not ~~need~~ want to check that the source file exists as this
        // makes the tests too inflexible.
        // In a real run, this is what provides the guarantee that the constructed path makes
        // sense; in this test it is done by checking that the path we get at the end for the
        // source file is the one we expect.
        $fileSystemStub
            ->method('realPath')
            ->willReturnCallback(static fn (string $path): string => $path);

        return $fileSystemStub;
    }
}
