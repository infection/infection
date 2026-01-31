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
use Infection\TestFramework\Tracing\Trace\SourceMethodLineRange;
use Infection\TestFramework\Tracing\Trace\TestLocations;
use Infection\TestFramework\Tracing\Trace\Trace;
use Infection\TestFramework\Tracing\TraceProviderAdapterTracer;
use Infection\TestFramework\Tracing\Tracer;
use Infection\Tests\Fixtures\TestFramework\Coverage\JUnit\FakeTestFileDataProvider;
use Infection\Tests\TestFramework\Tracing\Trace\SyntheticTrace;
use Infection\Tests\TestFramework\Tracing\Trace\TraceAssertion;
use Infection\Tests\TestingUtility\PHPUnit\DataProviderFactory;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use function Safe\realpath;
use Symfony\Component\Filesystem\Path;

#[Group('integration')]
#[CoversNothing]
final class TracerIntegrationTest extends TestCase
{
    private const FIXTURES_DIR = __DIR__ . '/../Coverage/Fixtures';

    #[DataProvider('traceProvider')]
    public function test_it_can_create_a_trace(
        string $indexXmlPath,
        ?string $junitXmlPath,
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
        yield from DataProviderFactory::prefix(
            '[PHPUnit 09] ',
            self::phpUnit09InfoProvider(),
        );

        yield from DataProviderFactory::prefix(
            '[PHPUnit 10] ',
            self::phpUnit10InfoProvider(),
        );
        $coveragePath = Path::canonicalize(self::COVERAGE_REPORT_DIR);

        $canonicalDemoCounterServicePathname = Path::canonicalize(self::FIXTURE_DIR . '/src/DemoCounterService.php');

        yield from DataProviderFactory::prefix(
            '[PHPUnit 11] ',
            self::phpUnit11InfoProvider(),
        );

        yield from DataProviderFactory::prefix(
            '[PHPUnit 12.0] ',
            self::phpUnit12_0InfoProvider(),
        );
        $testFilePath = Path::canonicalize(self::FIXTURE_DIR . '/tests/DemoCounterServiceTest.php');

        yield from DataProviderFactory::prefix(
            '[PHPUnit 12.5] ',
            self::phpUnit12_5InfoProvider(),
        );

        yield from DataProviderFactory::prefix(
            '[Codeception] ',
            self::codeceptionInfoProvider(),
        );

        yield from DataProviderFactory::prefix(
            '[PhpSpec] ',
            self::phpSpecInfoProvider(),
        );
    }

    private static function phpUnit09InfoProvider(): iterable
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
                                    executionTime: 0.006446,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\CalculatorTest::test_add with data set #1',
                                    filePath: $testPath,
                                    executionTime: 0.006446,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\CalculatorTest::test_add with data set #2',
                                    filePath: $testPath,
                                    executionTime: 0.006446,
                                ),
                            ],
                            14 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\CalculatorTest::test_subtract with data set #0',
                                    filePath: $testPath,
                                    executionTime: 0.006446,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\CalculatorTest::test_subtract with data set #1',
                                    filePath: $testPath,
                                    executionTime: 0.006446,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\CalculatorTest::test_subtract with data set #2',
                                    filePath: $testPath,
                                    executionTime: 0.006446,
                                ),
                            ],
                            19 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\CalculatorTest::test_multiply',
                                    filePath: $testPath,
                                    executionTime: 0.006446,
                                ),
                            ],
                            24 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\CalculatorTest::test_divide',
                                    filePath: $testPath,
                                    executionTime: 0.006446,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\CalculatorTest::test_divide_by_zero_throws_exception',
                                    filePath: $testPath,
                                    executionTime: 0.006446,
                                ),
                            ],
                            25 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\CalculatorTest::test_divide_by_zero_throws_exception',
                                    filePath: $testPath,
                                    executionTime: 0.006446,
                                ),
                            ],
                            28 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\CalculatorTest::test_divide',
                                    filePath: $testPath,
                                    executionTime: 0.006446,
                                ),
                            ],
                            33 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\CalculatorTest::test_is_positive',
                                    filePath: $testPath,
                                    executionTime: 0.006446,
                                ),
                            ],
                            38 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\CalculatorTest::test_absolute',
                                    filePath: $testPath,
                                    executionTime: 0.006446,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\CalculatorTest::test_absolute_zero',
                                    filePath: $testPath,
                                    executionTime: 0.006446,
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
            $sourcePath = '/path/to/infection/tests/e2e/PHPUnit_09-3/src/Covered/LoggerTrait.php';
            $testPath = '/path/to/infection/tests/e2e/PHPUnit_09-3/tests/Covered/UserServiceTest.php';

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
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_add_user_successfully',
                                    filePath: $testPath,
                                    executionTime: 0.003686,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_add_user_with_empty_name_fails',
                                    filePath: $testPath,
                                    executionTime: 0.003686,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_add_user_with_empty_email_fails',
                                    filePath: $testPath,
                                    executionTime: 0.003686,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_add_duplicate_user_fails',
                                    filePath: $testPath,
                                    executionTime: 0.003686,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                                    filePath: $testPath,
                                    executionTime: 0.003686,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_remove_non_existent_user_fails',
                                    filePath: $testPath,
                                    executionTime: 0.003686,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_get_user_returns_user_data',
                                    filePath: $testPath,
                                    executionTime: 0.003686,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_user_exists',
                                    filePath: $testPath,
                                    executionTime: 0.003686,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_logger_trait_methods',
                                    filePath: $testPath,
                                    executionTime: 0.003686,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_log_method_is_public',
                                    filePath: $testPath,
                                    executionTime: 0.003686,
                                ),
                            ],
                            16 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_add_user_with_empty_name_fails',
                                    filePath: $testPath,
                                    executionTime: 0.003686,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_add_user_with_empty_email_fails',
                                    filePath: $testPath,
                                    executionTime: 0.003686,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_add_duplicate_user_fails',
                                    filePath: $testPath,
                                    executionTime: 0.003686,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                                    filePath: $testPath,
                                    executionTime: 0.003686,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_remove_non_existent_user_fails',
                                    filePath: $testPath,
                                    executionTime: 0.003686,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_logger_trait_methods',
                                    filePath: $testPath,
                                    executionTime: 0.003686,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_log_method_is_public',
                                    filePath: $testPath,
                                    executionTime: 0.003686,
                                ),
                            ],
                            21 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_add_duplicate_user_fails',
                                    filePath: $testPath,
                                    executionTime: 0.003686,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                                    filePath: $testPath,
                                    executionTime: 0.003686,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_logger_trait_methods',
                                    filePath: $testPath,
                                    executionTime: 0.003686,
                                ),
                            ],
                            26 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_add_user_successfully',
                                    filePath: $testPath,
                                    executionTime: 0.003686,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_logger_trait_methods',
                                    filePath: $testPath,
                                    executionTime: 0.003686,
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
            $sourcePath = '/path/to/infection/tests/e2e/PHPUnit_09-3/src/Covered/UserService.php';
            $testPath = '/path/to/infection/tests/e2e/PHPUnit_09-3/tests/Covered/UserServiceTest.php';

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
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_add_user_successfully',
                                    filePath: $testPath,
                                    executionTime: 0.003686,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_add_user_with_empty_name_fails',
                                    filePath: $testPath,
                                    executionTime: 0.003686,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_add_user_with_empty_email_fails',
                                    filePath: $testPath,
                                    executionTime: 0.003686,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_add_duplicate_user_fails',
                                    filePath: $testPath,
                                    executionTime: 0.003686,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                                    filePath: $testPath,
                                    executionTime: 0.003686,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_get_user_returns_user_data',
                                    filePath: $testPath,
                                    executionTime: 0.003686,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_user_exists',
                                    filePath: $testPath,
                                    executionTime: 0.003686,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_logger_trait_methods',
                                    filePath: $testPath,
                                    executionTime: 0.003686,
                                ),
                            ],
                            14 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_add_user_with_empty_name_fails',
                                    filePath: $testPath,
                                    executionTime: 0.003686,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_add_user_with_empty_email_fails',
                                    filePath: $testPath,
                                    executionTime: 0.003686,
                                ),
                            ],
                            15 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_add_user_with_empty_name_fails',
                                    filePath: $testPath,
                                    executionTime: 0.003686,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_add_user_with_empty_email_fails',
                                    filePath: $testPath,
                                    executionTime: 0.003686,
                                ),
                            ],
                            18 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_add_user_successfully',
                                    filePath: $testPath,
                                    executionTime: 0.003686,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_add_duplicate_user_fails',
                                    filePath: $testPath,
                                    executionTime: 0.003686,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                                    filePath: $testPath,
                                    executionTime: 0.003686,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_get_user_returns_user_data',
                                    filePath: $testPath,
                                    executionTime: 0.003686,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_user_exists',
                                    filePath: $testPath,
                                    executionTime: 0.003686,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_logger_trait_methods',
                                    filePath: $testPath,
                                    executionTime: 0.003686,
                                ),
                            ],
                            19 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_add_duplicate_user_fails',
                                    filePath: $testPath,
                                    executionTime: 0.003686,
                                ),
                            ],
                            20 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_add_duplicate_user_fails',
                                    filePath: $testPath,
                                    executionTime: 0.003686,
                                ),
                            ],
                            23 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_add_user_successfully',
                                    filePath: $testPath,
                                    executionTime: 0.003686,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_add_duplicate_user_fails',
                                    filePath: $testPath,
                                    executionTime: 0.003686,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                                    filePath: $testPath,
                                    executionTime: 0.003686,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_get_user_returns_user_data',
                                    filePath: $testPath,
                                    executionTime: 0.003686,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_user_exists',
                                    filePath: $testPath,
                                    executionTime: 0.003686,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_logger_trait_methods',
                                    filePath: $testPath,
                                    executionTime: 0.003686,
                                ),
                            ],
                            24 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_add_user_successfully',
                                    filePath: $testPath,
                                    executionTime: 0.003686,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_add_duplicate_user_fails',
                                    filePath: $testPath,
                                    executionTime: 0.003686,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                                    filePath: $testPath,
                                    executionTime: 0.003686,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_get_user_returns_user_data',
                                    filePath: $testPath,
                                    executionTime: 0.003686,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_user_exists',
                                    filePath: $testPath,
                                    executionTime: 0.003686,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_logger_trait_methods',
                                    filePath: $testPath,
                                    executionTime: 0.003686,
                                ),
                            ],
                            25 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_add_user_successfully',
                                    filePath: $testPath,
                                    executionTime: 0.003686,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_add_duplicate_user_fails',
                                    filePath: $testPath,
                                    executionTime: 0.003686,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                                    filePath: $testPath,
                                    executionTime: 0.003686,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_get_user_returns_user_data',
                                    filePath: $testPath,
                                    executionTime: 0.003686,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_user_exists',
                                    filePath: $testPath,
                                    executionTime: 0.003686,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_logger_trait_methods',
                                    filePath: $testPath,
                                    executionTime: 0.003686,
                                ),
                            ],
                            30 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                                    filePath: $testPath,
                                    executionTime: 0.003686,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_remove_non_existent_user_fails',
                                    filePath: $testPath,
                                    executionTime: 0.003686,
                                ),
                            ],
                            31 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_remove_non_existent_user_fails',
                                    filePath: $testPath,
                                    executionTime: 0.003686,
                                ),
                            ],
                            32 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_remove_non_existent_user_fails',
                                    filePath: $testPath,
                                    executionTime: 0.003686,
                                ),
                            ],
                            35 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                                    filePath: $testPath,
                                    executionTime: 0.003686,
                                ),
                            ],
                            36 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                                    filePath: $testPath,
                                    executionTime: 0.003686,
                                ),
                            ],
                            37 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                                    filePath: $testPath,
                                    executionTime: 0.003686,
                                ),
                            ],
                            42 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_get_user_returns_user_data',
                                    filePath: $testPath,
                                    executionTime: 0.003686,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_get_user_returns_null_for_non_existent_user',
                                    filePath: $testPath,
                                    executionTime: 0.003686,
                                ),
                            ],
                            47 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_add_user_successfully',
                                    filePath: $testPath,
                                    executionTime: 0.003686,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_add_duplicate_user_fails',
                                    filePath: $testPath,
                                    executionTime: 0.003686,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                                    filePath: $testPath,
                                    executionTime: 0.003686,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_remove_non_existent_user_fails',
                                    filePath: $testPath,
                                    executionTime: 0.003686,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_get_user_returns_user_data',
                                    filePath: $testPath,
                                    executionTime: 0.003686,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_user_exists',
                                    filePath: $testPath,
                                    executionTime: 0.003686,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_logger_trait_methods',
                                    filePath: $testPath,
                                    executionTime: 0.003686,
                                ),
                            ],
                            52 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_add_user_successfully',
                                    filePath: $testPath,
                                    executionTime: 0.003686,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_add_user_with_empty_name_fails',
                                    filePath: $testPath,
                                    executionTime: 0.003686,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_add_user_with_empty_email_fails',
                                    filePath: $testPath,
                                    executionTime: 0.003686,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_add_duplicate_user_fails',
                                    filePath: $testPath,
                                    executionTime: 0.003686,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                                    filePath: $testPath,
                                    executionTime: 0.003686,
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
            $sourcePath = '/path/to/infection/tests/e2e/PHPUnit_09-3/src/Covered/functions.php';
            $testPath = '/path/to/infection/tests/e2e/PHPUnit_09-3/tests/Covered/FunctionsTest.php';

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
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\FunctionsTest::test_format_name_with_both_names',
                                    filePath: $testPath,
                                    executionTime: 0.000505,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\FunctionsTest::test_format_name_with_first_name_only',
                                    filePath: $testPath,
                                    executionTime: 0.000505,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\FunctionsTest::test_format_name_with_last_name_only',
                                    filePath: $testPath,
                                    executionTime: 0.000505,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\FunctionsTest::test_format_name_with_no_names',
                                    filePath: $testPath,
                                    executionTime: 0.000505,
                                ),
                            ],
                            8 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\FunctionsTest::test_format_name_with_no_names',
                                    filePath: $testPath,
                                    executionTime: 0.000505,
                                ),
                            ],
                            11 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\FunctionsTest::test_format_name_with_both_names',
                                    filePath: $testPath,
                                    executionTime: 0.000505,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\FunctionsTest::test_format_name_with_first_name_only',
                                    filePath: $testPath,
                                    executionTime: 0.000505,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\FunctionsTest::test_format_name_with_last_name_only',
                                    filePath: $testPath,
                                    executionTime: 0.000505,
                                ),
                            ],
                            12 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\FunctionsTest::test_format_name_with_last_name_only',
                                    filePath: $testPath,
                                    executionTime: 0.000505,
                                ),
                            ],
                            15 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\FunctionsTest::test_format_name_with_both_names',
                                    filePath: $testPath,
                                    executionTime: 0.000505,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\FunctionsTest::test_format_name_with_first_name_only',
                                    filePath: $testPath,
                                    executionTime: 0.000505,
                                ),
                            ],
                            16 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\FunctionsTest::test_format_name_with_first_name_only',
                                    filePath: $testPath,
                                    executionTime: 0.000505,
                                ),
                            ],
                            19 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\FunctionsTest::test_format_name_with_both_names',
                                    filePath: $testPath,
                                    executionTime: 0.000505,
                                ),
                            ],
                        ],
                        byMethod: [],
                    ),
                ),
            ];
        })();

        yield 'uncovered class' => (static function () use ($indexXmlPath, $junitXmlPath) {
            $sourcePath = '/path/to/infection/tests/e2e/PHPUnit_09-3/src/Uncovered/Calculator.php';

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
            $sourcePath = '/path/to/infection/tests/e2e/PHPUnit_09-3/src/Uncovered/LoggerTrait.php';

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
            $sourcePath = '/path/to/infection/tests/e2e/PHPUnit_09-3/src/Uncovered/UserService.php';

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
            $sourcePath = '/path/to/infection/tests/e2e/PHPUnit_09-3/src/Uncovered/functions.php';

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

    private static function phpUnit10InfoProvider(): iterable
    {
        $coverageDirectory = Path::canonicalize(self::FIXTURES_DIR . '/phpunit-10/');

        $indexXmlPath = Path::canonicalize($coverageDirectory . '/coverage-xml/index.xml');
        $junitXmlPath = Path::canonicalize($coverageDirectory . '/junit.xml');

        yield 'covered class' => (static function () use ($indexXmlPath, $junitXmlPath) {
            $sourcePath = '/path/to/infection/tests/e2e/PHPUnit_10-1/src/Covered/Calculator.php';
            $testPath = '/path/to/infection/tests/e2e/PHPUnit_10-1/tests/Covered/CalculatorTest.php';

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
                                    method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\CalculatorTest::test_add#0',
                                    filePath: $testPath,
                                    executionTime: 0.026407,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\CalculatorTest::test_add#1',
                                    filePath: $testPath,
                                    executionTime: 0.026407,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\CalculatorTest::test_add#with a key',
                                    filePath: $testPath,
                                    executionTime: 0.026407,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\CalculatorTest::test_add#with a key with (\'"#::&) special characters',
                                    filePath: $testPath,
                                    executionTime: 0.026407,
                                ),
                            ],
                            14 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\CalculatorTest::test_subtract#0',
                                    filePath: $testPath,
                                    executionTime: 0.026407,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\CalculatorTest::test_subtract#1',
                                    filePath: $testPath,
                                    executionTime: 0.026407,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\CalculatorTest::test_subtract#with a key',
                                    filePath: $testPath,
                                    executionTime: 0.026407,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\CalculatorTest::test_subtract#with a key with (\'"#::&) special characters',
                                    filePath: $testPath,
                                    executionTime: 0.026407,
                                ),
                            ],
                            19 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\CalculatorTest::test_multiply',
                                    filePath: $testPath,
                                    executionTime: 0.026407,
                                ),
                            ],
                            24 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\CalculatorTest::test_divide',
                                    filePath: $testPath,
                                    executionTime: 0.026407,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\CalculatorTest::test_divide_by_zero_throws_exception',
                                    filePath: $testPath,
                                    executionTime: 0.026407,
                                ),
                            ],
                            25 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\CalculatorTest::test_divide_by_zero_throws_exception',
                                    filePath: $testPath,
                                    executionTime: 0.026407,
                                ),
                            ],
                            28 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\CalculatorTest::test_divide',
                                    filePath: $testPath,
                                    executionTime: 0.026407,
                                ),
                            ],
                            33 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\CalculatorTest::test_is_positive',
                                    filePath: $testPath,
                                    executionTime: 0.026407,
                                ),
                            ],
                            38 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\CalculatorTest::test_absolute',
                                    filePath: $testPath,
                                    executionTime: 0.026407,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\CalculatorTest::test_absolute_zero',
                                    filePath: $testPath,
                                    executionTime: 0.026407,
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
            $sourcePath = '/path/to/infection/tests/e2e/PHPUnit_10-1/src/Covered/LoggerTrait.php';
            $testPath = '/path/to/infection/tests/e2e/PHPUnit_10-1/tests/Covered/UserServiceTest.php';

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
                                    method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_add_user_successfully',
                                    filePath: $testPath,
                                    executionTime: 0.012739,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_add_user_with_empty_name_fails',
                                    filePath: $testPath,
                                    executionTime: 0.012739,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_add_user_with_empty_email_fails',
                                    filePath: $testPath,
                                    executionTime: 0.012739,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_add_duplicate_user_fails',
                                    filePath: $testPath,
                                    executionTime: 0.012739,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                                    filePath: $testPath,
                                    executionTime: 0.012739,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_remove_non_existent_user_fails',
                                    filePath: $testPath,
                                    executionTime: 0.012739,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_get_user_returns_user_data',
                                    filePath: $testPath,
                                    executionTime: 0.012739,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_user_exists',
                                    filePath: $testPath,
                                    executionTime: 0.012739,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_logger_trait_methods',
                                    filePath: $testPath,
                                    executionTime: 0.012739,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_log_method_is_public',
                                    filePath: $testPath,
                                    executionTime: 0.012739,
                                ),
                            ],
                            16 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_add_user_with_empty_name_fails',
                                    filePath: $testPath,
                                    executionTime: 0.012739,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_add_user_with_empty_email_fails',
                                    filePath: $testPath,
                                    executionTime: 0.012739,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_add_duplicate_user_fails',
                                    filePath: $testPath,
                                    executionTime: 0.012739,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                                    filePath: $testPath,
                                    executionTime: 0.012739,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_remove_non_existent_user_fails',
                                    filePath: $testPath,
                                    executionTime: 0.012739,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_logger_trait_methods',
                                    filePath: $testPath,
                                    executionTime: 0.012739,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_log_method_is_public',
                                    filePath: $testPath,
                                    executionTime: 0.012739,
                                ),
                            ],
                            21 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_add_duplicate_user_fails',
                                    filePath: $testPath,
                                    executionTime: 0.012739,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                                    filePath: $testPath,
                                    executionTime: 0.012739,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_logger_trait_methods',
                                    filePath: $testPath,
                                    executionTime: 0.012739,
                                ),
                            ],
                            26 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_add_user_successfully',
                                    filePath: $testPath,
                                    executionTime: 0.012739,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_logger_trait_methods',
                                    filePath: $testPath,
                                    executionTime: 0.012739,
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
            $sourcePath = '/path/to/infection/tests/e2e/PHPUnit_10-1/src/Covered/UserService.php';
            $testPath = '/path/to/infection/tests/e2e/PHPUnit_10-1/tests/Covered/UserServiceTest.php';

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
                                    method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_add_user_successfully',
                                    filePath: $testPath,
                                    executionTime: 0.012739,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_add_user_with_empty_name_fails',
                                    filePath: $testPath,
                                    executionTime: 0.012739,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_add_user_with_empty_email_fails',
                                    filePath: $testPath,
                                    executionTime: 0.012739,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_add_duplicate_user_fails',
                                    filePath: $testPath,
                                    executionTime: 0.012739,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                                    filePath: $testPath,
                                    executionTime: 0.012739,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_get_user_returns_user_data',
                                    filePath: $testPath,
                                    executionTime: 0.012739,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_user_exists',
                                    filePath: $testPath,
                                    executionTime: 0.012739,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_logger_trait_methods',
                                    filePath: $testPath,
                                    executionTime: 0.012739,
                                ),
                            ],
                            14 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_add_user_with_empty_name_fails',
                                    filePath: $testPath,
                                    executionTime: 0.012739,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_add_user_with_empty_email_fails',
                                    filePath: $testPath,
                                    executionTime: 0.012739,
                                ),
                            ],
                            15 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_add_user_with_empty_name_fails',
                                    filePath: $testPath,
                                    executionTime: 0.012739,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_add_user_with_empty_email_fails',
                                    filePath: $testPath,
                                    executionTime: 0.012739,
                                ),
                            ],
                            18 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_add_user_successfully',
                                    filePath: $testPath,
                                    executionTime: 0.012739,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_add_duplicate_user_fails',
                                    filePath: $testPath,
                                    executionTime: 0.012739,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                                    filePath: $testPath,
                                    executionTime: 0.012739,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_get_user_returns_user_data',
                                    filePath: $testPath,
                                    executionTime: 0.012739,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_user_exists',
                                    filePath: $testPath,
                                    executionTime: 0.012739,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_logger_trait_methods',
                                    filePath: $testPath,
                                    executionTime: 0.012739,
                                ),
                            ],
                            19 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_add_duplicate_user_fails',
                                    filePath: $testPath,
                                    executionTime: 0.012739,
                                ),
                            ],
                            20 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_add_duplicate_user_fails',
                                    filePath: $testPath,
                                    executionTime: 0.012739,
                                ),
                            ],
                            23 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_add_user_successfully',
                                    filePath: $testPath,
                                    executionTime: 0.012739,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_add_duplicate_user_fails',
                                    filePath: $testPath,
                                    executionTime: 0.012739,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                                    filePath: $testPath,
                                    executionTime: 0.012739,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_get_user_returns_user_data',
                                    filePath: $testPath,
                                    executionTime: 0.012739,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_user_exists',
                                    filePath: $testPath,
                                    executionTime: 0.012739,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_logger_trait_methods',
                                    filePath: $testPath,
                                    executionTime: 0.012739,
                                ),
                            ],
                            24 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_add_user_successfully',
                                    filePath: $testPath,
                                    executionTime: 0.012739,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_add_duplicate_user_fails',
                                    filePath: $testPath,
                                    executionTime: 0.012739,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                                    filePath: $testPath,
                                    executionTime: 0.012739,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_get_user_returns_user_data',
                                    filePath: $testPath,
                                    executionTime: 0.012739,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_user_exists',
                                    filePath: $testPath,
                                    executionTime: 0.012739,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_logger_trait_methods',
                                    filePath: $testPath,
                                    executionTime: 0.012739,
                                ),
                            ],
                            25 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_add_user_successfully',
                                    filePath: $testPath,
                                    executionTime: 0.012739,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_add_duplicate_user_fails',
                                    filePath: $testPath,
                                    executionTime: 0.012739,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                                    filePath: $testPath,
                                    executionTime: 0.012739,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_get_user_returns_user_data',
                                    filePath: $testPath,
                                    executionTime: 0.012739,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_user_exists',
                                    filePath: $testPath,
                                    executionTime: 0.012739,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_logger_trait_methods',
                                    filePath: $testPath,
                                    executionTime: 0.012739,
                                ),
                            ],
                            30 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                                    filePath: $testPath,
                                    executionTime: 0.012739,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_remove_non_existent_user_fails',
                                    filePath: $testPath,
                                    executionTime: 0.012739,
                                ),
                            ],
                            31 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_remove_non_existent_user_fails',
                                    filePath: $testPath,
                                    executionTime: 0.012739,
                                ),
                            ],
                            32 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_remove_non_existent_user_fails',
                                    filePath: $testPath,
                                    executionTime: 0.012739,
                                ),
                            ],
                            35 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                                    filePath: $testPath,
                                    executionTime: 0.012739,
                                ),
                            ],
                            36 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                                    filePath: $testPath,
                                    executionTime: 0.012739,
                                ),
                            ],
                            37 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                                    filePath: $testPath,
                                    executionTime: 0.012739,
                                ),
                            ],
                            42 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_get_user_returns_user_data',
                                    filePath: $testPath,
                                    executionTime: 0.012739,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_get_user_returns_null_for_non_existent_user',
                                    filePath: $testPath,
                                    executionTime: 0.012739,
                                ),
                            ],
                            47 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_add_user_successfully',
                                    filePath: $testPath,
                                    executionTime: 0.012739,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_add_duplicate_user_fails',
                                    filePath: $testPath,
                                    executionTime: 0.012739,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                                    filePath: $testPath,
                                    executionTime: 0.012739,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_remove_non_existent_user_fails',
                                    filePath: $testPath,
                                    executionTime: 0.012739,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_get_user_returns_user_data',
                                    filePath: $testPath,
                                    executionTime: 0.012739,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_user_exists',
                                    filePath: $testPath,
                                    executionTime: 0.012739,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_logger_trait_methods',
                                    filePath: $testPath,
                                    executionTime: 0.012739,
                                ),
                            ],
                            52 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_add_user_successfully',
                                    filePath: $testPath,
                                    executionTime: 0.012739,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_add_user_with_empty_name_fails',
                                    filePath: $testPath,
                                    executionTime: 0.012739,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_add_user_with_empty_email_fails',
                                    filePath: $testPath,
                                    executionTime: 0.012739,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_add_duplicate_user_fails',
                                    filePath: $testPath,
                                    executionTime: 0.012739,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                                    filePath: $testPath,
                                    executionTime: 0.012739,
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
            $sourcePath = '/path/to/infection/tests/e2e/PHPUnit_10-1/src/Covered/functions.php';
            $testPath = '/path/to/infection/tests/e2e/PHPUnit_10-1/tests/Covered/FunctionsTest.php';

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
                                    method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\FunctionsTest::test_format_name_with_both_names',
                                    filePath: $testPath,
                                    executionTime: 0.003231,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\FunctionsTest::test_format_name_with_first_name_only',
                                    filePath: $testPath,
                                    executionTime: 0.003231,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\FunctionsTest::test_format_name_with_last_name_only',
                                    filePath: $testPath,
                                    executionTime: 0.003231,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\FunctionsTest::test_format_name_with_no_names',
                                    filePath: $testPath,
                                    executionTime: 0.003231,
                                ),
                            ],
                            8 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\FunctionsTest::test_format_name_with_no_names',
                                    filePath: $testPath,
                                    executionTime: 0.003231,
                                ),
                            ],
                            11 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\FunctionsTest::test_format_name_with_both_names',
                                    filePath: $testPath,
                                    executionTime: 0.003231,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\FunctionsTest::test_format_name_with_first_name_only',
                                    filePath: $testPath,
                                    executionTime: 0.003231,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\FunctionsTest::test_format_name_with_last_name_only',
                                    filePath: $testPath,
                                    executionTime: 0.003231,
                                ),
                            ],
                            12 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\FunctionsTest::test_format_name_with_last_name_only',
                                    filePath: $testPath,
                                    executionTime: 0.003231,
                                ),
                            ],
                            15 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\FunctionsTest::test_format_name_with_both_names',
                                    filePath: $testPath,
                                    executionTime: 0.003231,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\FunctionsTest::test_format_name_with_first_name_only',
                                    filePath: $testPath,
                                    executionTime: 0.003231,
                                ),
                            ],
                            16 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\FunctionsTest::test_format_name_with_first_name_only',
                                    filePath: $testPath,
                                    executionTime: 0.003231,
                                ),
                            ],
                            19 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\FunctionsTest::test_format_name_with_both_names',
                                    filePath: $testPath,
                                    executionTime: 0.003231,
                                ),
                            ],
                        ],
                        byMethod: [],
                    ),
                ),
            ];
        })();

        yield 'uncovered class' => (static function () use ($indexXmlPath, $junitXmlPath) {
            $sourcePath = '/path/to/infection/tests/e2e/PHPUnit_10-1/src/Uncovered/Calculator.php';

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
            $sourcePath = '/path/to/infection/tests/e2e/PHPUnit_10-1/src/Uncovered/LoggerTrait.php';

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
            $sourcePath = '/path/to/infection/tests/e2e/PHPUnit_10-1/src/Uncovered/UserService.php';

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
            $sourcePath = '/path/to/infection/tests/e2e/PHPUnit_10-1/src/Uncovered/functions.php';

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

    private static function phpUnit11InfoProvider(): iterable
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

    private static function phpUnit12_0InfoProvider(): iterable
    {
        $coverageDirectory = Path::canonicalize(self::FIXTURES_DIR . '/phpunit-12-0/');

        $indexXmlPath = Path::canonicalize($coverageDirectory . '/coverage-xml/index.xml');
        $junitXmlPath = Path::canonicalize($coverageDirectory . '/junit.xml');

        yield 'covered class' => (static function () use ($indexXmlPath, $junitXmlPath) {
            $sourcePath = '/path/to/infection/tests/e2e/PHPUnit_12-0/src/Covered/Calculator.php';
            $testPath = '/path/to/infection/tests/e2e/PHPUnit_12-0/tests/Covered/CalculatorTest.php';

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
                                    method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\CalculatorTest::test_add#0',
                                    filePath: $testPath,
                                    executionTime: 0.022453,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\CalculatorTest::test_add#1',
                                    filePath: $testPath,
                                    executionTime: 0.022453,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\CalculatorTest::test_add#2',
                                    filePath: $testPath,
                                    executionTime: 0.022453,
                                ),
                            ],
                            14 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\CalculatorTest::test_subtract#0',
                                    filePath: $testPath,
                                    executionTime: 0.022453,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\CalculatorTest::test_subtract#1',
                                    filePath: $testPath,
                                    executionTime: 0.022453,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\CalculatorTest::test_subtract#2',
                                    filePath: $testPath,
                                    executionTime: 0.022453,
                                ),
                            ],
                            19 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\CalculatorTest::test_multiply',
                                    filePath: $testPath,
                                    executionTime: 0.022453,
                                ),
                            ],
                            24 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\CalculatorTest::test_divide',
                                    filePath: $testPath,
                                    executionTime: 0.022453,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\CalculatorTest::test_divide_by_zero_throws_exception',
                                    filePath: $testPath,
                                    executionTime: 0.022453,
                                ),
                            ],
                            25 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\CalculatorTest::test_divide_by_zero_throws_exception',
                                    filePath: $testPath,
                                    executionTime: 0.022453,
                                ),
                            ],
                            28 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\CalculatorTest::test_divide',
                                    filePath: $testPath,
                                    executionTime: 0.022453,
                                ),
                            ],
                            33 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\CalculatorTest::test_is_positive',
                                    filePath: $testPath,
                                    executionTime: 0.022453,
                                ),
                            ],
                            38 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\CalculatorTest::test_absolute',
                                    filePath: $testPath,
                                    executionTime: 0.022453,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\CalculatorTest::test_absolute_zero',
                                    filePath: $testPath,
                                    executionTime: 0.022453,
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
            $sourcePath = '/path/to/infection/tests/e2e/PHPUnit_12-0/src/Covered/LoggerTrait.php';
            $testPath = '/path/to/infection/tests/e2e/PHPUnit_12-0/tests/Covered/UserServiceTest.php';

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
                                    method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_add_user_successfully',
                                    filePath: $testPath,
                                    executionTime: 0.013035,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_add_user_with_empty_name_fails',
                                    filePath: $testPath,
                                    executionTime: 0.013035,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_add_user_with_empty_email_fails',
                                    filePath: $testPath,
                                    executionTime: 0.013035,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_add_duplicate_user_fails',
                                    filePath: $testPath,
                                    executionTime: 0.013035,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                                    filePath: $testPath,
                                    executionTime: 0.013035,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_remove_non_existent_user_fails',
                                    filePath: $testPath,
                                    executionTime: 0.013035,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_get_user_returns_user_data',
                                    filePath: $testPath,
                                    executionTime: 0.013035,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_user_exists',
                                    filePath: $testPath,
                                    executionTime: 0.013035,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_logger_trait_methods',
                                    filePath: $testPath,
                                    executionTime: 0.013035,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_log_method_is_public',
                                    filePath: $testPath,
                                    executionTime: 0.013035,
                                ),
                            ],
                            16 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_add_user_with_empty_name_fails',
                                    filePath: $testPath,
                                    executionTime: 0.013035,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_add_user_with_empty_email_fails',
                                    filePath: $testPath,
                                    executionTime: 0.013035,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_add_duplicate_user_fails',
                                    filePath: $testPath,
                                    executionTime: 0.013035,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                                    filePath: $testPath,
                                    executionTime: 0.013035,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_remove_non_existent_user_fails',
                                    filePath: $testPath,
                                    executionTime: 0.013035,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_logger_trait_methods',
                                    filePath: $testPath,
                                    executionTime: 0.013035,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_log_method_is_public',
                                    filePath: $testPath,
                                    executionTime: 0.013035,
                                ),
                            ],
                            21 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_add_duplicate_user_fails',
                                    filePath: $testPath,
                                    executionTime: 0.013035,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                                    filePath: $testPath,
                                    executionTime: 0.013035,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_logger_trait_methods',
                                    filePath: $testPath,
                                    executionTime: 0.013035,
                                ),
                            ],
                            26 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_add_user_successfully',
                                    filePath: $testPath,
                                    executionTime: 0.013035,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_logger_trait_methods',
                                    filePath: $testPath,
                                    executionTime: 0.013035,
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
            $sourcePath = '/path/to/infection/tests/e2e/PHPUnit_12-0/src/Covered/UserService.php';
            $testPath = '/path/to/infection/tests/e2e/PHPUnit_12-0/tests/Covered/UserServiceTest.php';

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
                                    method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_add_user_successfully',
                                    filePath: $testPath,
                                    executionTime: 0.013035,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_add_user_with_empty_name_fails',
                                    filePath: $testPath,
                                    executionTime: 0.013035,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_add_user_with_empty_email_fails',
                                    filePath: $testPath,
                                    executionTime: 0.013035,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_add_duplicate_user_fails',
                                    filePath: $testPath,
                                    executionTime: 0.013035,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                                    filePath: $testPath,
                                    executionTime: 0.013035,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_get_user_returns_user_data',
                                    filePath: $testPath,
                                    executionTime: 0.013035,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_user_exists',
                                    filePath: $testPath,
                                    executionTime: 0.013035,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_logger_trait_methods',
                                    filePath: $testPath,
                                    executionTime: 0.013035,
                                ),
                            ],
                            14 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_add_user_with_empty_name_fails',
                                    filePath: $testPath,
                                    executionTime: 0.013035,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_add_user_with_empty_email_fails',
                                    filePath: $testPath,
                                    executionTime: 0.013035,
                                ),
                            ],
                            15 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_add_user_with_empty_name_fails',
                                    filePath: $testPath,
                                    executionTime: 0.013035,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_add_user_with_empty_email_fails',
                                    filePath: $testPath,
                                    executionTime: 0.013035,
                                ),
                            ],
                            18 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_add_user_successfully',
                                    filePath: $testPath,
                                    executionTime: 0.013035,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_add_duplicate_user_fails',
                                    filePath: $testPath,
                                    executionTime: 0.013035,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                                    filePath: $testPath,
                                    executionTime: 0.013035,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_get_user_returns_user_data',
                                    filePath: $testPath,
                                    executionTime: 0.013035,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_user_exists',
                                    filePath: $testPath,
                                    executionTime: 0.013035,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_logger_trait_methods',
                                    filePath: $testPath,
                                    executionTime: 0.013035,
                                ),
                            ],
                            19 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_add_duplicate_user_fails',
                                    filePath: $testPath,
                                    executionTime: 0.013035,
                                ),
                            ],
                            20 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_add_duplicate_user_fails',
                                    filePath: $testPath,
                                    executionTime: 0.013035,
                                ),
                            ],
                            23 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_add_user_successfully',
                                    filePath: $testPath,
                                    executionTime: 0.013035,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_add_duplicate_user_fails',
                                    filePath: $testPath,
                                    executionTime: 0.013035,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                                    filePath: $testPath,
                                    executionTime: 0.013035,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_get_user_returns_user_data',
                                    filePath: $testPath,
                                    executionTime: 0.013035,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_user_exists',
                                    filePath: $testPath,
                                    executionTime: 0.013035,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_logger_trait_methods',
                                    filePath: $testPath,
                                    executionTime: 0.013035,
                                ),
                            ],
                            24 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_add_user_successfully',
                                    filePath: $testPath,
                                    executionTime: 0.013035,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_add_duplicate_user_fails',
                                    filePath: $testPath,
                                    executionTime: 0.013035,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                                    filePath: $testPath,
                                    executionTime: 0.013035,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_get_user_returns_user_data',
                                    filePath: $testPath,
                                    executionTime: 0.013035,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_user_exists',
                                    filePath: $testPath,
                                    executionTime: 0.013035,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_logger_trait_methods',
                                    filePath: $testPath,
                                    executionTime: 0.013035,
                                ),
                            ],
                            25 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_add_user_successfully',
                                    filePath: $testPath,
                                    executionTime: 0.013035,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_add_duplicate_user_fails',
                                    filePath: $testPath,
                                    executionTime: 0.013035,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                                    filePath: $testPath,
                                    executionTime: 0.013035,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_get_user_returns_user_data',
                                    filePath: $testPath,
                                    executionTime: 0.013035,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_user_exists',
                                    filePath: $testPath,
                                    executionTime: 0.013035,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_logger_trait_methods',
                                    filePath: $testPath,
                                    executionTime: 0.013035,
                                ),
                            ],
                            30 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                                    filePath: $testPath,
                                    executionTime: 0.013035,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_remove_non_existent_user_fails',
                                    filePath: $testPath,
                                    executionTime: 0.013035,
                                ),
                            ],
                            31 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_remove_non_existent_user_fails',
                                    filePath: $testPath,
                                    executionTime: 0.013035,
                                ),
                            ],
                            32 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_remove_non_existent_user_fails',
                                    filePath: $testPath,
                                    executionTime: 0.013035,
                                ),
                            ],
                            35 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                                    filePath: $testPath,
                                    executionTime: 0.013035,
                                ),
                            ],
                            36 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                                    filePath: $testPath,
                                    executionTime: 0.013035,
                                ),
                            ],
                            37 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                                    filePath: $testPath,
                                    executionTime: 0.013035,
                                ),
                            ],
                            42 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_get_user_returns_user_data',
                                    filePath: $testPath,
                                    executionTime: 0.013035,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_get_user_returns_null_for_non_existent_user',
                                    filePath: $testPath,
                                    executionTime: 0.013035,
                                ),
                            ],
                            47 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_add_user_successfully',
                                    filePath: $testPath,
                                    executionTime: 0.013035,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_add_duplicate_user_fails',
                                    filePath: $testPath,
                                    executionTime: 0.013035,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                                    filePath: $testPath,
                                    executionTime: 0.013035,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_remove_non_existent_user_fails',
                                    filePath: $testPath,
                                    executionTime: 0.013035,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_get_user_returns_user_data',
                                    filePath: $testPath,
                                    executionTime: 0.013035,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_user_exists',
                                    filePath: $testPath,
                                    executionTime: 0.013035,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_logger_trait_methods',
                                    filePath: $testPath,
                                    executionTime: 0.013035,
                                ),
                            ],
                            52 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_add_user_successfully',
                                    filePath: $testPath,
                                    executionTime: 0.013035,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_add_user_with_empty_name_fails',
                                    filePath: $testPath,
                                    executionTime: 0.013035,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_add_user_with_empty_email_fails',
                                    filePath: $testPath,
                                    executionTime: 0.013035,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_add_duplicate_user_fails',
                                    filePath: $testPath,
                                    executionTime: 0.013035,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                                    filePath: $testPath,
                                    executionTime: 0.013035,
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
            $sourcePath = '/path/to/infection/tests/e2e/PHPUnit_12-0/src/Covered/functions.php';
            $testPath = '/path/to/infection/tests/e2e/PHPUnit_12-0/tests/Covered/FunctionsTest.php';

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
                                    method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\FunctionsTest::test_format_name_with_both_names',
                                    filePath: $testPath,
                                    executionTime: 0.004188,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\FunctionsTest::test_format_name_with_first_name_only',
                                    filePath: $testPath,
                                    executionTime: 0.004188,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\FunctionsTest::test_format_name_with_last_name_only',
                                    filePath: $testPath,
                                    executionTime: 0.004188,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\FunctionsTest::test_format_name_with_no_names',
                                    filePath: $testPath,
                                    executionTime: 0.004188,
                                ),
                            ],
                            8 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\FunctionsTest::test_format_name_with_no_names',
                                    filePath: $testPath,
                                    executionTime: 0.004188,
                                ),
                            ],
                            11 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\FunctionsTest::test_format_name_with_both_names',
                                    filePath: $testPath,
                                    executionTime: 0.004188,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\FunctionsTest::test_format_name_with_first_name_only',
                                    filePath: $testPath,
                                    executionTime: 0.004188,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\FunctionsTest::test_format_name_with_last_name_only',
                                    filePath: $testPath,
                                    executionTime: 0.004188,
                                ),
                            ],
                            12 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\FunctionsTest::test_format_name_with_last_name_only',
                                    filePath: $testPath,
                                    executionTime: 0.004188,
                                ),
                            ],
                            15 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\FunctionsTest::test_format_name_with_both_names',
                                    filePath: $testPath,
                                    executionTime: 0.004188,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\FunctionsTest::test_format_name_with_first_name_only',
                                    filePath: $testPath,
                                    executionTime: 0.004188,
                                ),
                            ],
                            16 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\FunctionsTest::test_format_name_with_first_name_only',
                                    filePath: $testPath,
                                    executionTime: 0.004188,
                                ),
                            ],
                            19 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_0\Tests\Covered\FunctionsTest::test_format_name_with_both_names',
                                    filePath: $testPath,
                                    executionTime: 0.004188,
                                ),
                            ],
                        ],
                        byMethod: [],
                    ),
                ),
            ];
        })();

        yield 'uncovered class' => (static function () use ($indexXmlPath, $junitXmlPath) {
            $sourcePath = '/path/to/infection/tests/e2e/PHPUnit_12-0/src/Uncovered/Calculator.php';

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
            $sourcePath = '/path/to/infection/tests/e2e/PHPUnit_12-0/src/Uncovered/LoggerTrait.php';

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
            $sourcePath = '/path/to/infection/tests/e2e/PHPUnit_12-0/src/Uncovered/UserService.php';

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
            $sourcePath = '/path/to/infection/tests/e2e/PHPUnit_12-0/src/Uncovered/functions.php';

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

    private static function phpUnit12_5InfoProvider(): iterable
    {
        $coverageDirectory = Path::canonicalize(self::FIXTURES_DIR . '/phpunit-12-5/');

        $indexXmlPath = Path::canonicalize($coverageDirectory . '/index.xml');
        $junitXmlPath = false;

        yield 'covered class' => (static function () use ($indexXmlPath, $junitXmlPath) {
            $sourcePath = '/path/to/infection/tests/e2e/PHPUnit_12-5/src/Covered/Calculator.php';
            $testPath = '/path/to/infection/tests/e2e/PHPUnit_12-5/tests/Covered/CalculatorTest.php';

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
                ),
            ];
        })();

        yield 'covered trait' => (static function () use ($indexXmlPath, $junitXmlPath) {
            $sourcePath = '/path/to/infection/tests/e2e/PHPUnit_12-5/src/Covered/LoggerTrait.php';
            $testPath = '/path/to/infection/tests/e2e/PHPUnit_12-5/tests/Covered/UserServiceTest.php';

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
                                    method: 'Infection\E2ETests\PHPUnit_12_5\Tests\Covered\UserServiceTest::test_add_user_successfully',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_5\Tests\Covered\UserServiceTest::test_add_user_with_empty_name_fails',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_5\Tests\Covered\UserServiceTest::test_add_user_with_empty_email_fails',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_5\Tests\Covered\UserServiceTest::test_add_duplicate_user_fails',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_5\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_5\Tests\Covered\UserServiceTest::test_remove_non_existent_user_fails',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_5\Tests\Covered\UserServiceTest::test_get_user_returns_user_data',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_5\Tests\Covered\UserServiceTest::test_user_exists',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_5\Tests\Covered\UserServiceTest::test_logger_trait_methods',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_5\Tests\Covered\UserServiceTest::test_log_method_is_public',
                                    filePath: null,
                                    executionTime: null,
                                ),
                            ],
                            16 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_5\Tests\Covered\UserServiceTest::test_add_user_with_empty_name_fails',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_5\Tests\Covered\UserServiceTest::test_add_user_with_empty_email_fails',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_5\Tests\Covered\UserServiceTest::test_add_duplicate_user_fails',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_5\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_5\Tests\Covered\UserServiceTest::test_remove_non_existent_user_fails',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_5\Tests\Covered\UserServiceTest::test_logger_trait_methods',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_5\Tests\Covered\UserServiceTest::test_log_method_is_public',
                                    filePath: null,
                                    executionTime: null,
                                ),
                            ],
                            21 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_5\Tests\Covered\UserServiceTest::test_add_duplicate_user_fails',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_5\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_5\Tests\Covered\UserServiceTest::test_logger_trait_methods',
                                    filePath: null,
                                    executionTime: null,
                                ),
                            ],
                            26 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_5\Tests\Covered\UserServiceTest::test_add_user_successfully',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_5\Tests\Covered\UserServiceTest::test_logger_trait_methods',
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
                ),
            ];
        })();

        yield 'covered class with trait' => (static function () use ($indexXmlPath, $junitXmlPath) {
            $sourcePath = '/path/to/infection/tests/e2e/PHPUnit_12-5/src/Covered/UserService.php';
            $testPath = '/path/to/infection/tests/e2e/PHPUnit_12-5/tests/Covered/UserServiceTest.php';

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
                                    method: 'Infection\E2ETests\PHPUnit_12_5\Tests\Covered\UserServiceTest::test_add_user_successfully',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_5\Tests\Covered\UserServiceTest::test_add_user_with_empty_name_fails',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_5\Tests\Covered\UserServiceTest::test_add_user_with_empty_email_fails',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_5\Tests\Covered\UserServiceTest::test_add_duplicate_user_fails',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_5\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_5\Tests\Covered\UserServiceTest::test_get_user_returns_user_data',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_5\Tests\Covered\UserServiceTest::test_user_exists',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_5\Tests\Covered\UserServiceTest::test_logger_trait_methods',
                                    filePath: null,
                                    executionTime: null,
                                ),
                            ],
                            14 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_5\Tests\Covered\UserServiceTest::test_add_user_with_empty_name_fails',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_5\Tests\Covered\UserServiceTest::test_add_user_with_empty_email_fails',
                                    filePath: null,
                                    executionTime: null,
                                ),
                            ],
                            15 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_5\Tests\Covered\UserServiceTest::test_add_user_with_empty_name_fails',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_5\Tests\Covered\UserServiceTest::test_add_user_with_empty_email_fails',
                                    filePath: null,
                                    executionTime: null,
                                ),
                            ],
                            18 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_5\Tests\Covered\UserServiceTest::test_add_user_successfully',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_5\Tests\Covered\UserServiceTest::test_add_duplicate_user_fails',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_5\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_5\Tests\Covered\UserServiceTest::test_get_user_returns_user_data',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_5\Tests\Covered\UserServiceTest::test_user_exists',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_5\Tests\Covered\UserServiceTest::test_logger_trait_methods',
                                    filePath: null,
                                    executionTime: null,
                                ),
                            ],
                            19 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_5\Tests\Covered\UserServiceTest::test_add_duplicate_user_fails',
                                    filePath: null,
                                    executionTime: null,
                                ),
                            ],
                            20 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_5\Tests\Covered\UserServiceTest::test_add_duplicate_user_fails',
                                    filePath: null,
                                    executionTime: null,
                                ),
                            ],
                            23 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_5\Tests\Covered\UserServiceTest::test_add_user_successfully',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_5\Tests\Covered\UserServiceTest::test_add_duplicate_user_fails',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_5\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_5\Tests\Covered\UserServiceTest::test_get_user_returns_user_data',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_5\Tests\Covered\UserServiceTest::test_user_exists',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_5\Tests\Covered\UserServiceTest::test_logger_trait_methods',
                                    filePath: null,
                                    executionTime: null,
                                ),
                            ],
                            24 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_5\Tests\Covered\UserServiceTest::test_add_user_successfully',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_5\Tests\Covered\UserServiceTest::test_add_duplicate_user_fails',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_5\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_5\Tests\Covered\UserServiceTest::test_get_user_returns_user_data',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_5\Tests\Covered\UserServiceTest::test_user_exists',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_5\Tests\Covered\UserServiceTest::test_logger_trait_methods',
                                    filePath: null,
                                    executionTime: null,
                                ),
                            ],
                            25 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_5\Tests\Covered\UserServiceTest::test_add_user_successfully',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_5\Tests\Covered\UserServiceTest::test_add_duplicate_user_fails',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_5\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_5\Tests\Covered\UserServiceTest::test_get_user_returns_user_data',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_5\Tests\Covered\UserServiceTest::test_user_exists',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_5\Tests\Covered\UserServiceTest::test_logger_trait_methods',
                                    filePath: null,
                                    executionTime: null,
                                ),
                            ],
                            30 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_5\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_5\Tests\Covered\UserServiceTest::test_remove_non_existent_user_fails',
                                    filePath: null,
                                    executionTime: null,
                                ),
                            ],
                            31 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_5\Tests\Covered\UserServiceTest::test_remove_non_existent_user_fails',
                                    filePath: null,
                                    executionTime: null,
                                ),
                            ],
                            32 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_5\Tests\Covered\UserServiceTest::test_remove_non_existent_user_fails',
                                    filePath: null,
                                    executionTime: null,
                                ),
                            ],
                            35 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_5\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                                    filePath: null,
                                    executionTime: null,
                                ),
                            ],
                            36 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_5\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                                    filePath: null,
                                    executionTime: null,
                                ),
                            ],
                            37 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_5\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                                    filePath: null,
                                    executionTime: null,
                                ),
                            ],
                            42 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_5\Tests\Covered\UserServiceTest::test_get_user_returns_user_data',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_5\Tests\Covered\UserServiceTest::test_get_user_returns_null_for_non_existent_user',
                                    filePath: null,
                                    executionTime: null,
                                ),
                            ],
                            47 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_5\Tests\Covered\UserServiceTest::test_add_user_successfully',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_5\Tests\Covered\UserServiceTest::test_add_duplicate_user_fails',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_5\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_5\Tests\Covered\UserServiceTest::test_remove_non_existent_user_fails',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_5\Tests\Covered\UserServiceTest::test_get_user_returns_user_data',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_5\Tests\Covered\UserServiceTest::test_user_exists',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_5\Tests\Covered\UserServiceTest::test_logger_trait_methods',
                                    filePath: null,
                                    executionTime: null,
                                ),
                            ],
                            52 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_5\Tests\Covered\UserServiceTest::test_add_user_successfully',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_5\Tests\Covered\UserServiceTest::test_add_user_with_empty_name_fails',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_5\Tests\Covered\UserServiceTest::test_add_user_with_empty_email_fails',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_5\Tests\Covered\UserServiceTest::test_add_duplicate_user_fails',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_5\Tests\Covered\UserServiceTest::test_remove_user_successfully',
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
                ),
            ];
        })();

        yield 'covered function' => (static function () use ($indexXmlPath, $junitXmlPath) {
            $sourcePath = '/path/to/infection/tests/e2e/PHPUnit_12-5/src/Covered/functions.php';
            $testPath = '/path/to/infection/tests/e2e/PHPUnit_12-5/tests/Covered/FunctionsTest.php';

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
                                    method: 'Infection\E2ETests\PHPUnit_12_5\Tests\Covered\FunctionsTest::test_format_name_with_both_names',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_5\Tests\Covered\FunctionsTest::test_format_name_with_first_name_only',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_5\Tests\Covered\FunctionsTest::test_format_name_with_last_name_only',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_5\Tests\Covered\FunctionsTest::test_format_name_with_no_names',
                                    filePath: null,
                                    executionTime: null,
                                ),
                            ],
                            8 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_5\Tests\Covered\FunctionsTest::test_format_name_with_no_names',
                                    filePath: null,
                                    executionTime: null,
                                ),
                            ],
                            11 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_5\Tests\Covered\FunctionsTest::test_format_name_with_both_names',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_5\Tests\Covered\FunctionsTest::test_format_name_with_first_name_only',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_5\Tests\Covered\FunctionsTest::test_format_name_with_last_name_only',
                                    filePath: null,
                                    executionTime: null,
                                ),
                            ],
                            12 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_5\Tests\Covered\FunctionsTest::test_format_name_with_last_name_only',
                                    filePath: null,
                                    executionTime: null,
                                ),
                            ],
                            15 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_5\Tests\Covered\FunctionsTest::test_format_name_with_both_names',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_5\Tests\Covered\FunctionsTest::test_format_name_with_first_name_only',
                                    filePath: null,
                                    executionTime: null,
                                ),
                            ],
                            16 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_5\Tests\Covered\FunctionsTest::test_format_name_with_first_name_only',
                                    filePath: null,
                                    executionTime: null,
                                ),
                            ],
                            19 => [
                                new TestLocation(
                                    method: 'Infection\E2ETests\PHPUnit_12_5\Tests\Covered\FunctionsTest::test_format_name_with_both_names',
                                    filePath: null,
                                    executionTime: null,
                                ),
                            ],
                        ],
                        byMethod: [],
                    ),
                ),
            ];
        })();

        yield 'uncovered class' => (static function () use ($indexXmlPath, $junitXmlPath) {
            $sourcePath = '/path/to/infection/tests/e2e/PHPUnit_12-5/src/Uncovered/Calculator.php';

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
            $sourcePath = '/path/to/infection/tests/e2e/PHPUnit_12-5/src/Uncovered/LoggerTrait.php';

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
            $sourcePath = '/path/to/infection/tests/e2e/PHPUnit_12-5/src/Uncovered/UserService.php';

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
            $sourcePath = '/path/to/infection/tests/e2e/PHPUnit_12-5/src/Uncovered/functions.php';

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

    private static function codeceptionInfoProvider(): iterable
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

    private static function phpSpecInfoProvider(): iterable
    {
        $coverageDirectory = Path::canonicalize(self::FIXTURES_DIR . '/phpspec/');

        $indexXmlPath = Path::canonicalize($coverageDirectory . '/index.xml');
        $junitXmlPath = false;

        yield 'covered class' => (static function () use ($indexXmlPath, $junitXmlPath) {
            $sourcePath = '/path/to/phpspec-adapter/tests/e2e/PhpSpec/src/Covered/Calculator.php';
            $testPath = '/path/to/phpspec-adapter/tests/e2e/PhpSpec/spec/Covered/CalculatorSpec.php';

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
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\CalculatorSpec::it_adds_two_positive_numbers',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\CalculatorSpec::it_adds_negative_and_positive_numbers',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\CalculatorSpec::it_adds_two_negative_numbers',
                                    filePath: null,
                                    executionTime: null,
                                ),
                            ],
                            14 => [
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\CalculatorSpec::it_subtracts_two_numbers',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\CalculatorSpec::it_subtracts_with_negative_result',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\CalculatorSpec::it_subtracts_equal_numbers',
                                    filePath: null,
                                    executionTime: null,
                                ),
                            ],
                            19 => [
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\CalculatorSpec::it_multiplies_two_positive_numbers',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\CalculatorSpec::it_multiplies_negative_and_positive_numbers',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\CalculatorSpec::it_multiplies_by_zero',
                                    filePath: null,
                                    executionTime: null,
                                ),
                            ],
                            24 => [
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\CalculatorSpec::it_divides_two_numbers',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\CalculatorSpec::it_divides_negative_and_positive_numbers',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\CalculatorSpec::it_divides_equal_numbers',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\CalculatorSpec::it_throws_exception_when_dividing_by_zero',
                                    filePath: null,
                                    executionTime: null,
                                ),
                            ],
                            25 => [
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\CalculatorSpec::it_throws_exception_when_dividing_by_zero',
                                    filePath: null,
                                    executionTime: null,
                                ),
                            ],
                            28 => [
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\CalculatorSpec::it_divides_two_numbers',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\CalculatorSpec::it_divides_negative_and_positive_numbers',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\CalculatorSpec::it_divides_equal_numbers',
                                    filePath: null,
                                    executionTime: null,
                                ),
                            ],
                            33 => [
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\CalculatorSpec::it_checks_if_positive_number_is_positive',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\CalculatorSpec::it_checks_if_negative_number_is_not_positive',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\CalculatorSpec::it_checks_if_zero_is_positive',
                                    filePath: null,
                                    executionTime: null,
                                ),
                            ],
                            38 => [
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\CalculatorSpec::it_returns_absolute_value_of_positive_number',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\CalculatorSpec::it_returns_absolute_value_of_negative_number',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\CalculatorSpec::it_returns_absolute_value_of_zero',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\CalculatorSpec::it_handles_boundary_values_for_absolute',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\CalculatorSpec::it_ensures_zero_is_not_negated_in_absolute',
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
                ),
            ];
        })();

        yield 'covered trait' => (static function () use ($indexXmlPath, $junitXmlPath) {
            $sourcePath = '/path/to/phpspec-adapter/tests/e2e/PhpSpec/src/Covered/LoggerTrait.php';
            $testPath = '/path/to/phpspec-adapter/tests/e2e/PhpSpec/spec/Covered/UserServiceSpec.php';

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
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_adds_user_successfully',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_fails_to_add_user_with_empty_name',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_fails_to_add_user_with_empty_email',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_fails_to_add_duplicate_user',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_removes_user_successfully',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_fails_to_remove_non_existent_user',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_returns_user_data',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_checks_if_user_exists',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_has_logger_trait_methods',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_has_public_log_method',
                                    filePath: null,
                                    executionTime: null,
                                ),
                            ],
                            16 => [
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_fails_to_add_user_with_empty_name',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_fails_to_add_user_with_empty_email',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_fails_to_add_duplicate_user',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_removes_user_successfully',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_fails_to_remove_non_existent_user',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_has_logger_trait_methods',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_has_public_log_method',
                                    filePath: null,
                                    executionTime: null,
                                ),
                            ],
                            21 => [
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_fails_to_add_duplicate_user',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_removes_user_successfully',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_has_logger_trait_methods',
                                    filePath: null,
                                    executionTime: null,
                                ),
                            ],
                            26 => [
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_adds_user_successfully',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_has_logger_trait_methods',
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
                ),
            ];
        })();

        yield 'covered class with trait' => (static function () use ($indexXmlPath, $junitXmlPath) {
            $sourcePath = '/path/to/phpspec-adapter/tests/e2e/PhpSpec/src/Covered/UserService.php';
            $testPath = '/path/to/phpspec-adapter/tests/e2e/PhpSpec/spec/Covered/UserServiceSpec.php';

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
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_adds_user_successfully',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_fails_to_add_user_with_empty_name',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_fails_to_add_user_with_empty_email',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_fails_to_add_duplicate_user',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_removes_user_successfully',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_returns_user_data',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_checks_if_user_exists',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_has_logger_trait_methods',
                                    filePath: null,
                                    executionTime: null,
                                ),
                            ],
                            14 => [
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_fails_to_add_user_with_empty_name',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_fails_to_add_user_with_empty_email',
                                    filePath: null,
                                    executionTime: null,
                                ),
                            ],
                            15 => [
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_fails_to_add_user_with_empty_name',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_fails_to_add_user_with_empty_email',
                                    filePath: null,
                                    executionTime: null,
                                ),
                            ],
                            18 => [
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_adds_user_successfully',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_fails_to_add_duplicate_user',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_removes_user_successfully',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_returns_user_data',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_checks_if_user_exists',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_has_logger_trait_methods',
                                    filePath: null,
                                    executionTime: null,
                                ),
                            ],
                            19 => [
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_fails_to_add_duplicate_user',
                                    filePath: null,
                                    executionTime: null,
                                ),
                            ],
                            20 => [
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_fails_to_add_duplicate_user',
                                    filePath: null,
                                    executionTime: null,
                                ),
                            ],
                            23 => [
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_adds_user_successfully',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_fails_to_add_duplicate_user',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_removes_user_successfully',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_returns_user_data',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_checks_if_user_exists',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_has_logger_trait_methods',
                                    filePath: null,
                                    executionTime: null,
                                ),
                            ],
                            24 => [
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_adds_user_successfully',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_fails_to_add_duplicate_user',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_removes_user_successfully',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_returns_user_data',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_checks_if_user_exists',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_has_logger_trait_methods',
                                    filePath: null,
                                    executionTime: null,
                                ),
                            ],
                            25 => [
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_adds_user_successfully',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_fails_to_add_duplicate_user',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_removes_user_successfully',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_returns_user_data',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_checks_if_user_exists',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_has_logger_trait_methods',
                                    filePath: null,
                                    executionTime: null,
                                ),
                            ],
                            30 => [
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_removes_user_successfully',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_fails_to_remove_non_existent_user',
                                    filePath: null,
                                    executionTime: null,
                                ),
                            ],
                            31 => [
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_fails_to_remove_non_existent_user',
                                    filePath: null,
                                    executionTime: null,
                                ),
                            ],
                            32 => [
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_fails_to_remove_non_existent_user',
                                    filePath: null,
                                    executionTime: null,
                                ),
                            ],
                            35 => [
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_removes_user_successfully',
                                    filePath: null,
                                    executionTime: null,
                                ),
                            ],
                            36 => [
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_removes_user_successfully',
                                    filePath: null,
                                    executionTime: null,
                                ),
                            ],
                            37 => [
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_removes_user_successfully',
                                    filePath: null,
                                    executionTime: null,
                                ),
                            ],
                            42 => [
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_returns_user_data',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_returns_null_for_non_existent_user',
                                    filePath: null,
                                    executionTime: null,
                                ),
                            ],
                            47 => [
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_adds_user_successfully',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_fails_to_add_duplicate_user',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_removes_user_successfully',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_fails_to_remove_non_existent_user',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_returns_user_data',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_checks_if_user_exists',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_has_logger_trait_methods',
                                    filePath: null,
                                    executionTime: null,
                                ),
                            ],
                            52 => [
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_adds_user_successfully',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_fails_to_add_user_with_empty_name',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_fails_to_add_user_with_empty_email',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_fails_to_add_duplicate_user',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\UserServiceSpec::it_removes_user_successfully',
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
                ),
            ];
        })();

        yield 'covered function' => (static function () use ($indexXmlPath, $junitXmlPath) {
            $sourcePath = '/path/to/phpspec-adapter/tests/e2e/PhpSpec/src/Covered/functions.php';
            $testPath = '/path/to/phpspec-adapter/tests/e2e/PhpSpec/spec/Covered/FormatNameFunctionSpec.php';

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
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\FormatNameFunctionSpec::it_formats_name_with_both_names',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\FormatNameFunctionSpec::it_formats_name_with_first_name_only',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\FormatNameFunctionSpec::it_formats_name_with_last_name_only',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\FormatNameFunctionSpec::it_formats_name_with_no_names',
                                    filePath: null,
                                    executionTime: null,
                                ),
                            ],
                            8 => [
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\FormatNameFunctionSpec::it_formats_name_with_no_names',
                                    filePath: null,
                                    executionTime: null,
                                ),
                            ],
                            11 => [
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\FormatNameFunctionSpec::it_formats_name_with_both_names',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\FormatNameFunctionSpec::it_formats_name_with_first_name_only',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\FormatNameFunctionSpec::it_formats_name_with_last_name_only',
                                    filePath: null,
                                    executionTime: null,
                                ),
                            ],
                            12 => [
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\FormatNameFunctionSpec::it_formats_name_with_last_name_only',
                                    filePath: null,
                                    executionTime: null,
                                ),
                            ],
                            15 => [
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\FormatNameFunctionSpec::it_formats_name_with_both_names',
                                    filePath: null,
                                    executionTime: null,
                                ),
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\FormatNameFunctionSpec::it_formats_name_with_first_name_only',
                                    filePath: null,
                                    executionTime: null,
                                ),
                            ],
                            16 => [
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\FormatNameFunctionSpec::it_formats_name_with_first_name_only',
                                    filePath: null,
                                    executionTime: null,
                                ),
                            ],
                            19 => [
                                new TestLocation(
                                    method: 'spec\Infection\PhpSpecAdapter\E2ETests\PhpSpec\Covered\FormatNameFunctionSpec::it_formats_name_with_both_names',
                                    filePath: null,
                                    executionTime: null,
                                ),
                            ],
                        ],
                        byMethod: [],
                    ),
                ),
            ];
        })();
    }

    private function createTracer(
        string $indexXmlPath,
        ?string $junitXmlPath,
    ): Tracer {
        $testFrameworkAdapterStub = $this->createStub(TestFrameworkAdapter::class);
        $testFrameworkAdapterStub
            ->method('hasJUnitReport')
            ->willReturn($junitXmlPath !== null);

        $junitFileDataProvider = $junitXmlPath === null
            ? new FakeTestFileDataProvider()
            : new MemoizedTestFileDataProvider(
                new JUnitTestFileDataProvider(
                    new FixedLocator($junitXmlPath),
                ),
            );

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
                    $junitFileDataProvider,
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
