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

namespace Infection\Tests\TestFramework\Coverage\XmlReport;

use const DIRECTORY_SEPARATOR;
use Infection\AbstractTestFramework\Coverage\TestLocation;
use Infection\TestFramework\Coverage\XmlReport\SourceFileInfoProvider;
use Infection\TestFramework\Coverage\XmlReport\XmlCoverageParser;
use Infection\TestFramework\SafeDOMXPath;
use Infection\TestFramework\Tracing\Trace\SourceMethodLineRange;
use Infection\TestFramework\Tracing\Trace\TestLocations;
use Infection\Tests\TestingUtility\PHPUnit\DataProviderFactory;
use Infection\Tests\TestingUtility\FileSystem\MockSplFileInfo;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Path;
use function Pipeline\take;
use Symfony\Component\Filesystem\Path;

#[Group('integration')]
#[CoversClass(XmlCoverageParser::class)]
final class XmlCoverageParserTest extends TestCase
{
    private const FIXTURES_DIR = __DIR__ . '/../Fixtures';

    private XmlCoverageParser $parser;

    protected function setUp(): void
    {
        $this->parser = new XmlCoverageParser();
    }

    #[DataProvider('lineCoverageProvider')]
    public function test_it_can_get_the_line_coverage(
        SafeDOMXPath $xPath,
        TestLocations $expected,
    ): void {
        $sourceFileInfoProviderStub = $this->createSourceFileInfoProviderStub($xPath);

        $actual = $this->parser
            ->parse($sourceFileInfoProviderStub)
            ->getTests();

        $this->assertEquals($expected, $actual);
    }

    public static function lineCoverageProvider(): iterable
    {
        yield 'coverage with empty percentage' => [
            SafeDOMXPath::fromString(
                <<<'XML'
                    <?xml version="1.0"?>
                    <phpunit xmlns="http://schema.phpunit.de/coverage/1.0">
                        <file name="secondLevel.php" path="/FirstLevel/SecondLevel">
                            <totals>
                                <lines total="1e7" comments="0" code="1" executable="1" executed="1" percent=""/>
                            </totals>
                            <coverage>
                                <line nr="11">
                                    <covered by="ExampleTest::test_it_just_works"/>
                                </line>
                            </coverage>
                        </file>
                    </phpunit>
                    XML,
                namespace: 'p',
            ),
            new TestLocations(),
        ];

        yield 'coverage with percent sign' => [
            SafeDOMXPath::fromString(
                <<<'XML'
                    <?xml version="1.0"?>
                    <phpunit xmlns="http://schema.phpunit.de/coverage/1.0">
                        <file name="secondLevel.php" path="/FirstLevel/SecondLevel">
                            <totals>
                                <lines total="1e7" comments="0" code="1" executable="1" executed="1" percent="1.0%"/>
                            </totals>
                            <coverage>
                                <line nr="11">
                                    <covered by="ExampleTest::test_it_just_works"/>
                                </line>
                            </coverage>
                        </file>
                    </phpunit>
                    XML,
                namespace: 'p',
            ),
            new TestLocations(
                byLine: [
                    11 => [
                        new TestLocation(
                            method: 'ExampleTest::test_it_just_works',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                ],
            ),
        ];

        yield 'coverage with no covered lines' => [
            SafeDOMXPath::fromString(
                <<<'XML'
                    <phpunit xmlns="http://schema.phpunit.de/coverage/1.0">
                        <file name="secondLevel.php" path="/FirstLevel/SecondLevel">
                            <totals>
                                <lines total="1" comments="0" code="1" executable="1" executed="1" percent="100"/>
                            </totals>
                            <coverage>
                            </coverage>
                        </file>
                    </phpunit>
                    XML,
                namespace: 'p',
            ),
            new TestLocations(),
        ];

        yield from DataProviderFactory::prefix(
            '[PHPUnit 09] ',
            self::phpUnit09InfoProvider(),
        );

        yield from DataProviderFactory::prefix(
            '[PHPUnit 10] ',
            self::phpUnit10InfoProvider(),
        );

        yield from DataProviderFactory::prefix(
            '[PHPUnit 11] ',
            self::phpUnit11InfoProvider(),
        );

        yield from DataProviderFactory::prefix(
            '[PHPUnit 12.0] ',
            self::phpUnit120InfoProvider(),
        );

        yield from DataProviderFactory::prefix(
            '[PHPUnit 12.5] ',
            self::phpUnit125InfoProvider(),
        );

        yield from DataProviderFactory::prefix(
            '[Codeception] ',
            self::codeceptionInfoProvider(),
        );
    }

    private static function phpUnit09InfoProvider(): iterable
    {
        yield 'covered class' => [
            SafeDOMXPath::fromFile(
                Path::canonicalize(self::FIXTURES_DIR . '/phpunit-09/coverage-xml/Covered/Calculator.php.xml'),
                namespace: 'p',
            ),
            new TestLocations(
                byLine: [
                    9 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\CalculatorTest::test_add with data set #0',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\CalculatorTest::test_add with data set #1',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\CalculatorTest::test_add with data set #2',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    14 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\CalculatorTest::test_subtract with data set #0',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\CalculatorTest::test_subtract with data set #1',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\CalculatorTest::test_subtract with data set #2',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    19 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\CalculatorTest::test_multiply',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    24 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\CalculatorTest::test_divide',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\CalculatorTest::test_divide_by_zero_throws_exception',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    25 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\CalculatorTest::test_divide_by_zero_throws_exception',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    28 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\CalculatorTest::test_divide',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    33 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\CalculatorTest::test_is_positive',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    38 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\CalculatorTest::test_absolute',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\CalculatorTest::test_absolute_zero',
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

    private static function phpUnit10InfoProvider(): iterable
    {
        yield 'covered class' => [
            SafeDOMXPath::fromFile(
                Path::canonicalize(self::FIXTURES_DIR . '/phpunit-10/coverage-xml/Covered/Calculator.php.xml'),
                namespace: 'p',
            ),
            new TestLocations(
                byLine: [
                    9 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\CalculatorTest::test_add#0',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\CalculatorTest::test_add#1',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\CalculatorTest::test_add#with a key',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\CalculatorTest::test_add#with a key with (\'"#::&) special characters',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    14 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\CalculatorTest::test_subtract#0',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\CalculatorTest::test_subtract#1',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\CalculatorTest::test_subtract#with a key',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\CalculatorTest::test_subtract#with a key with (\'"#::&) special characters',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    19 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\CalculatorTest::test_multiply',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    24 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\CalculatorTest::test_divide',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\CalculatorTest::test_divide_by_zero_throws_exception',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    25 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\CalculatorTest::test_divide_by_zero_throws_exception',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    28 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\CalculatorTest::test_divide',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    33 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\CalculatorTest::test_is_positive',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    38 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\CalculatorTest::test_absolute',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\CalculatorTest::test_absolute_zero',
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
                Path::canonicalize(self::FIXTURES_DIR . '/phpunit-10/coverage-xml/Uncovered/Calculator.php.xml'),
                namespace: 'p',
            ),
            new TestLocations(
                byLine: [],
                byMethod: [],
            ),
        ];

        yield 'covered trait' => [
            SafeDOMXPath::fromFile(
                Path::canonicalize(self::FIXTURES_DIR . '/phpunit-10/coverage-xml/Covered/LoggerTrait.php.xml'),
                namespace: 'p',
            ),
            new TestLocations(
                byLine: [
                    11 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_add_user_successfully',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_add_user_with_empty_name_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_add_user_with_empty_email_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_add_duplicate_user_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_remove_non_existent_user_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_get_user_returns_user_data',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_user_exists',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_logger_trait_methods',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_log_method_is_public',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    16 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_add_user_with_empty_name_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_add_user_with_empty_email_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_add_duplicate_user_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_remove_non_existent_user_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_logger_trait_methods',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_log_method_is_public',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    21 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_add_duplicate_user_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_logger_trait_methods',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    26 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_add_user_successfully',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_logger_trait_methods',
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
                Path::canonicalize(self::FIXTURES_DIR . '/phpunit-10/coverage-xml/Covered/UserService.php.xml'),
                namespace: 'p',
            ),
            new TestLocations(
                byLine: [
                    13 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_add_user_successfully',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_add_user_with_empty_name_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_add_user_with_empty_email_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_add_duplicate_user_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_get_user_returns_user_data',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_user_exists',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_logger_trait_methods',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    14 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_add_user_with_empty_name_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_add_user_with_empty_email_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    15 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_add_user_with_empty_name_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_add_user_with_empty_email_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    18 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_add_user_successfully',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_add_duplicate_user_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_get_user_returns_user_data',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_user_exists',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_logger_trait_methods',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    19 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_add_duplicate_user_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    20 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_add_duplicate_user_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    23 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_add_user_successfully',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_add_duplicate_user_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_get_user_returns_user_data',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_user_exists',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_logger_trait_methods',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    24 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_add_user_successfully',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_add_duplicate_user_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_get_user_returns_user_data',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_user_exists',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_logger_trait_methods',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    25 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_add_user_successfully',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_add_duplicate_user_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_get_user_returns_user_data',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_user_exists',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_logger_trait_methods',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    30 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_remove_non_existent_user_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    31 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_remove_non_existent_user_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    32 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_remove_non_existent_user_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    35 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    36 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    37 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    42 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_get_user_returns_user_data',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_get_user_returns_null_for_non_existent_user',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    47 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_add_user_successfully',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_add_duplicate_user_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_remove_non_existent_user_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_get_user_returns_user_data',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_user_exists',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_logger_trait_methods',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    52 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_add_user_successfully',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_add_user_with_empty_name_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_add_user_with_empty_email_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_add_duplicate_user_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\UserServiceTest::test_remove_user_successfully',
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
                Path::canonicalize(self::FIXTURES_DIR . '/phpunit-10/coverage-xml/Covered/functions.php.xml'),
                namespace: 'p',
            ),
            new TestLocations(
                byLine: [
                    7 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\FunctionsTest::test_format_name_with_both_names',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\FunctionsTest::test_format_name_with_first_name_only',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\FunctionsTest::test_format_name_with_last_name_only',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\FunctionsTest::test_format_name_with_no_names',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    8 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\FunctionsTest::test_format_name_with_no_names',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    11 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\FunctionsTest::test_format_name_with_both_names',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\FunctionsTest::test_format_name_with_first_name_only',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\FunctionsTest::test_format_name_with_last_name_only',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    12 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\FunctionsTest::test_format_name_with_last_name_only',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    15 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\FunctionsTest::test_format_name_with_both_names',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\FunctionsTest::test_format_name_with_first_name_only',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    16 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\FunctionsTest::test_format_name_with_first_name_only',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    19 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_10_1\Tests\Covered\FunctionsTest::test_format_name_with_both_names',
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
                Path::canonicalize(self::FIXTURES_DIR . '/phpunit-10/coverage-xml/Uncovered/LoggerTrait.php.xml'),
                namespace: 'p',
            ),
            new TestLocations(
                byLine: [],
                byMethod: [],
            ),
        ];

        yield 'uncovered class with trait' => [
            SafeDOMXPath::fromFile(
                Path::canonicalize(self::FIXTURES_DIR . '/phpunit-10/coverage-xml/Uncovered/UserService.php.xml'),
                namespace: 'p',
            ),
            new TestLocations(
                byLine: [],
                byMethod: [],
            ),
        ];

        yield 'uncovered functions' => [
            SafeDOMXPath::fromFile(
                Path::canonicalize(self::FIXTURES_DIR . '/phpunit-10/coverage-xml/Uncovered/functions.php.xml'),
                namespace: 'p',
            ),
            new TestLocations(
                byLine: [],
                byMethod: [],
            ),
        ];
    }

    private static function phpUnit11InfoProvider(): iterable
    {
        yield 'covered class' => [
            SafeDOMXPath::fromFile(
                Path::canonicalize(self::FIXTURES_DIR . '/phpunit-11/coverage-xml/Covered/Calculator.php.xml'),
                namespace: 'p',
            ),
            new TestLocations(
                byLine: [
                    9 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\CalculatorTest::test_add#0',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\CalculatorTest::test_add#1',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\CalculatorTest::test_add#2',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    14 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\CalculatorTest::test_subtract#0',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\CalculatorTest::test_subtract#1',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\CalculatorTest::test_subtract#2',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    19 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\CalculatorTest::test_multiply',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    24 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\CalculatorTest::test_divide',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\CalculatorTest::test_divide_by_zero_throws_exception',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    25 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\CalculatorTest::test_divide_by_zero_throws_exception',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    28 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\CalculatorTest::test_divide',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    33 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\CalculatorTest::test_is_positive',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    38 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\CalculatorTest::test_absolute',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\CalculatorTest::test_absolute_zero',
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
                Path::canonicalize(self::FIXTURES_DIR . '/phpunit-11/coverage-xml/Uncovered/Calculator.php.xml'),
                namespace: 'p',
            ),
            new TestLocations(
                byLine: [],
                byMethod: [],
            ),
        ];

        yield 'covered trait' => [
            SafeDOMXPath::fromFile(
                Path::canonicalize(self::FIXTURES_DIR . '/phpunit-11/coverage-xml/Covered/LoggerTrait.php.xml'),
                namespace: 'p',
            ),
            new TestLocations(
                byLine: [
                    11 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_add_user_successfully',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_add_user_with_empty_name_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_add_user_with_empty_email_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_add_duplicate_user_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_remove_non_existent_user_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_get_user_returns_user_data',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_user_exists',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_logger_trait_methods',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_log_method_is_public',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    16 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_add_user_with_empty_name_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_add_user_with_empty_email_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_add_duplicate_user_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_remove_non_existent_user_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_logger_trait_methods',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_log_method_is_public',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    21 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_add_duplicate_user_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_logger_trait_methods',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    26 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_add_user_successfully',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_logger_trait_methods',
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
                Path::canonicalize(self::FIXTURES_DIR . '/phpunit-11/coverage-xml/Covered/UserService.php.xml'),
                namespace: 'p',
            ),
            new TestLocations(
                byLine: [
                    13 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_add_user_successfully',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_add_user_with_empty_name_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_add_user_with_empty_email_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_add_duplicate_user_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_get_user_returns_user_data',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_user_exists',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_logger_trait_methods',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    14 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_add_user_with_empty_name_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_add_user_with_empty_email_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    15 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_add_user_with_empty_name_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_add_user_with_empty_email_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    18 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_add_user_successfully',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_add_duplicate_user_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_get_user_returns_user_data',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_user_exists',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_logger_trait_methods',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    19 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_add_duplicate_user_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    20 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_add_duplicate_user_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    23 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_add_user_successfully',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_add_duplicate_user_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_get_user_returns_user_data',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_user_exists',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_logger_trait_methods',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    24 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_add_user_successfully',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_add_duplicate_user_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_get_user_returns_user_data',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_user_exists',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_logger_trait_methods',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    25 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_add_user_successfully',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_add_duplicate_user_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_get_user_returns_user_data',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_user_exists',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_logger_trait_methods',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    30 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_remove_non_existent_user_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    31 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_remove_non_existent_user_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    32 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_remove_non_existent_user_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    35 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    36 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    37 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    42 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_get_user_returns_user_data',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_get_user_returns_null_for_non_existent_user',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    47 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_add_user_successfully',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_add_duplicate_user_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_remove_user_successfully',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_remove_non_existent_user_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_get_user_returns_user_data',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_user_exists',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_logger_trait_methods',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    52 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_add_user_successfully',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_add_user_with_empty_name_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_add_user_with_empty_email_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_add_duplicate_user_fails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\UserServiceTest::test_remove_user_successfully',
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
                Path::canonicalize(self::FIXTURES_DIR . '/phpunit-11/coverage-xml/Covered/functions.php.xml'),
                namespace: 'p',
            ),
            new TestLocations(
                byLine: [
                    7 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\FunctionsTest::test_format_name_with_both_names',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\FunctionsTest::test_format_name_with_first_name_only',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\FunctionsTest::test_format_name_with_last_name_only',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\FunctionsTest::test_format_name_with_no_names',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    8 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\FunctionsTest::test_format_name_with_no_names',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    11 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\FunctionsTest::test_format_name_with_both_names',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\FunctionsTest::test_format_name_with_first_name_only',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\FunctionsTest::test_format_name_with_last_name_only',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    12 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\FunctionsTest::test_format_name_with_last_name_only',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    15 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\FunctionsTest::test_format_name_with_both_names',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\FunctionsTest::test_format_name_with_first_name_only',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    16 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\FunctionsTest::test_format_name_with_first_name_only',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    19 => [
                        new TestLocation(
                            method: 'Infection\E2ETests\PHPUnit_11\Tests\Covered\FunctionsTest::test_format_name_with_both_names',
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
                Path::canonicalize(self::FIXTURES_DIR . '/phpunit-11/coverage-xml/Uncovered/LoggerTrait.php.xml'),
                namespace: 'p',
            ),
            new TestLocations(
                byLine: [],
                byMethod: [],
            ),
        ];

        yield 'uncovered class with trait' => [
            SafeDOMXPath::fromFile(
                Path::canonicalize(self::FIXTURES_DIR . '/phpunit-11/coverage-xml/Uncovered/UserService.php.xml'),
                namespace: 'p',
            ),
            new TestLocations(
                byLine: [],
                byMethod: [],
            ),
        ];

        yield 'uncovered functions' => [
            SafeDOMXPath::fromFile(
                Path::canonicalize(self::FIXTURES_DIR . '/phpunit-11/coverage-xml/Uncovered/functions.php.xml'),
                namespace: 'p',
            ),
            new TestLocations(
                byLine: [],
                byMethod: [],
            ),
        ];
    }

    private static function phpUnit120InfoProvider(): iterable
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

    private static function phpUnit125InfoProvider(): iterable
    {
        yield 'uncovered class' => [
            SafeDOMXPath::fromFile(
                Path::canonicalize(self::FIXTURES_DIR . '/phpunit-12-5/Uncovered/Calculator.php.xml'),
                namespace: 'p',
            ),
            new TestLocations(
                byLine: [],
                byMethod: [],
            ),
        ];

        yield 'covered trait' => [
            SafeDOMXPath::fromFile(
                Path::canonicalize(self::FIXTURES_DIR . '/phpunit-12-5/Covered/LoggerTrait.php.xml'),
                namespace: 'p',
            ),
            new TestLocations(
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
        ];

        yield 'covered class with trait' => [
            SafeDOMXPath::fromFile(
                Path::canonicalize(self::FIXTURES_DIR . '/phpunit-12-5/Covered/UserService.php.xml'),
                namespace: 'p',
            ),
            new TestLocations(
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
        ];

        yield 'covered function' => [
            SafeDOMXPath::fromFile(
                Path::canonicalize(self::FIXTURES_DIR . '/phpunit-12-5/Covered/functions.php.xml'),
                namespace: 'p',
            ),
            new TestLocations(
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
        ];

        yield 'uncovered trait' => [
            SafeDOMXPath::fromFile(
                Path::canonicalize(self::FIXTURES_DIR . '/phpunit-12-5/Uncovered/LoggerTrait.php.xml'),
                namespace: 'p',
            ),
            new TestLocations(
                byLine: [],
                byMethod: [],
            ),
        ];

        yield 'uncovered class with trait' => [
            SafeDOMXPath::fromFile(
                Path::canonicalize(self::FIXTURES_DIR . '/phpunit-12-5/Uncovered/UserService.php.xml'),
                namespace: 'p',
            ),
            new TestLocations(
                byLine: [],
                byMethod: [],
            ),
        ];

        yield 'uncovered functions' => [
            SafeDOMXPath::fromFile(
                Path::canonicalize(self::FIXTURES_DIR . '/phpunit-12-5/Uncovered/functions.php.xml'),
                namespace: 'p',
            ),
            new TestLocations(
                byLine: [],
                byMethod: [],
            ),
        ];
    }

    private static function codeceptionInfoProvider(): iterable
    {
        yield 'covered class (BDD, Cest, Unit tests)' => [
            SafeDOMXPath::fromFile(
                Path::canonicalize(self::FIXTURES_DIR . '/codeception/coverage-xml/Covered/Calculator.php.xml'),
                namespace: 'p',
            ),
            new TestLocations(
                byLine: [
                    9 => [
                        new TestLocation(
                            method: 'calculator:Adding two numbers',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\functional\CalculatorCest:testAddition',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\CalculatorTest:testAddition',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    14 => [
                        new TestLocation(
                            method: 'calculator:Subtracting two numbers',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\functional\CalculatorCest:testSubtraction',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\CalculatorTest:testSubtraction',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    19 => [
                        new TestLocation(
                            method: 'calculator:Multiplying two numbers',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\functional\CalculatorCest:testMultiplication',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\CalculatorTest:testMultiplication',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    24 => [
                        new TestLocation(
                            method: 'calculator:Dividing two numbers',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'calculator:Division by zero throws error',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\functional\CalculatorCest:testDivision',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\functional\CalculatorCest:testDivisionByZeroThrowsException',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\CalculatorTest:testDivision',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\CalculatorTest:testDivisionByZero',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    25 => [
                        new TestLocation(
                            method: 'calculator:Division by zero throws error',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\functional\CalculatorCest:testDivisionByZeroThrowsException',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\CalculatorTest:testDivisionByZero',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    28 => [
                        new TestLocation(
                            method: 'calculator:Dividing two numbers',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\functional\CalculatorCest:testDivision',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\CalculatorTest:testDivision',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    33 => [
                        new TestLocation(
                            method: 'calculator:Checking if numbers are positive | 5, true',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'calculator:Checking if numbers are positive | 0, true',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'calculator:Checking if numbers are positive | -5, false',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\functional\CalculatorCest:testIsPositive',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\CalculatorTest:testIsPositive',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    38 => [
                        new TestLocation(
                            method: 'calculator:Computing absolute value with label "<label>" | 42, 5, 5',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'calculator:Computing absolute value with label "<label>" | positive number, 10, 10',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'calculator:Computing absolute value with label "<label>" | negative number, -7, 7',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'calculator:Computing absolute value with label "<label>" | zero, 0, 0',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'calculator:Computing absolute value with label "<label>" | with special chars (\'"#::&), -15, 15',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'calculator:Computing absolute value with label "<label>" | another "quoted" value, -1, 1',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\functional\CalculatorCest:testAbsolute',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\CalculatorTest:testAbsolute',
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

        yield 'covered trait' => [
            SafeDOMXPath::fromFile(
                Path::canonicalize(self::FIXTURES_DIR . '/codeception/coverage-xml/Covered/LoggerTrait.php.xml'),
                namespace: 'p',
            ),
            new TestLocations(
                byLine: [
                    11 => [
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testAddUser',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testAddUserWithEmptyNameFails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testAddUserWithEmptyEmailFails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testAddDuplicateUserFails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testRemoveUser',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testRemoveNonExistentUserFails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testGetUser',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testUserExists',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testClearLogs',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testAddUser',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testAddUserWithEmptyName',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testAddUserWithEmptyEmail',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testAddDuplicateUser',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testRemoveUser',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testRemoveNonExistentUser',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testGetUser',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testUserExists',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testGetLogs',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testClearLogs',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    16 => [
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testGetLogs',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    21 => [
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testClearLogs',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testClearLogs',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    26 => [
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testAddUser',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testClearLogs',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testAddUser',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testClearLogs',
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
                Path::canonicalize(self::FIXTURES_DIR . '/codeception/coverage-xml/Covered/UserService.php.xml'),
                namespace: 'p',
            ),
            new TestLocations(
                byLine: [
                    13 => [
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testAddUser',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testAddUserWithEmptyNameFails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testAddUserWithEmptyEmailFails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testAddDuplicateUserFails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testRemoveUser',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testGetUser',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testUserExists',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testClearLogs',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testAddUser',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testAddUserWithEmptyName',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testAddUserWithEmptyEmail',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testAddDuplicateUser',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testRemoveUser',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testGetUser',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testUserExists',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testGetLogs',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testClearLogs',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    14 => [
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testAddUserWithEmptyNameFails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testAddUserWithEmptyEmailFails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testAddUserWithEmptyName',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testAddUserWithEmptyEmail',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    15 => [
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testAddUserWithEmptyNameFails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testAddUserWithEmptyEmailFails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testAddUserWithEmptyName',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testAddUserWithEmptyEmail',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    18 => [
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testAddUser',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testAddDuplicateUserFails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testRemoveUser',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testGetUser',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testUserExists',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testClearLogs',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testAddUser',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testAddDuplicateUser',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testRemoveUser',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testGetUser',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testUserExists',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testGetLogs',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testClearLogs',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    19 => [
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testAddDuplicateUserFails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testAddDuplicateUser',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    20 => [
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testAddDuplicateUserFails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testAddDuplicateUser',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    23 => [
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testAddUser',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testAddDuplicateUserFails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testRemoveUser',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testGetUser',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testUserExists',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testClearLogs',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testAddUser',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testAddDuplicateUser',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testRemoveUser',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testGetUser',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testUserExists',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testGetLogs',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testClearLogs',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    24 => [
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testAddUser',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testAddDuplicateUserFails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testRemoveUser',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testGetUser',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testUserExists',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testClearLogs',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testAddUser',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testAddDuplicateUser',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testRemoveUser',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testGetUser',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testUserExists',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testGetLogs',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testClearLogs',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    25 => [
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testAddUser',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testAddDuplicateUserFails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testRemoveUser',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testGetUser',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testUserExists',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testClearLogs',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testAddUser',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testAddDuplicateUser',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testRemoveUser',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testGetUser',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testUserExists',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testGetLogs',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testClearLogs',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    30 => [
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testRemoveUser',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testRemoveNonExistentUserFails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testRemoveUser',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testRemoveNonExistentUser',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    31 => [
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testRemoveNonExistentUserFails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testRemoveNonExistentUser',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    32 => [
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testRemoveNonExistentUserFails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testRemoveNonExistentUser',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    35 => [
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testRemoveUser',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testRemoveUser',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    36 => [
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testRemoveUser',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testRemoveUser',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    37 => [
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testRemoveUser',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testRemoveUser',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    42 => [
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testGetUser',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testGetNonExistentUserReturnsNull',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testGetUser',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testGetNonExistentUser',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    47 => [
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testAddUser',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testAddDuplicateUserFails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testRemoveUser',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testRemoveNonExistentUserFails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testGetUser',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testUserExists',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testClearLogs',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testAddUser',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testAddDuplicateUser',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testRemoveUser',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testRemoveNonExistentUser',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testGetUser',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testUserExists',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testGetLogs',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testClearLogs',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    52 => [
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testAddUser',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testAddUserWithEmptyNameFails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testAddUserWithEmptyEmailFails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testAddDuplicateUserFails',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\functional\UserServiceCest:testRemoveUser',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testAddUser',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testAddUserWithEmptyName',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testAddUserWithEmptyEmail',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testAddDuplicateUser',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\UserServiceTest:testRemoveUser',
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
                Path::canonicalize(self::FIXTURES_DIR . '/codeception/coverage-xml/Covered/functions.php.xml'),
                namespace: 'p',
            ),
            new TestLocations(
                byLine: [
                    7 => [
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\FormatNameFunctionTest:testFormatFullName',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\FormatNameFunctionTest:testFormatFirstNameOnly',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\FormatNameFunctionTest:testFormatLastNameOnly',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\FormatNameFunctionTest:testFormatEmptyNames',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\FormatNameFunctionTest:testFormatWithSpaces',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    8 => [
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\FormatNameFunctionTest:testFormatEmptyNames',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    11 => [
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\FormatNameFunctionTest:testFormatFullName',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\FormatNameFunctionTest:testFormatFirstNameOnly',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\FormatNameFunctionTest:testFormatLastNameOnly',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\FormatNameFunctionTest:testFormatWithSpaces',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    12 => [
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\FormatNameFunctionTest:testFormatLastNameOnly',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    15 => [
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\FormatNameFunctionTest:testFormatFullName',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\FormatNameFunctionTest:testFormatFirstNameOnly',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\FormatNameFunctionTest:testFormatWithSpaces',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    16 => [
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\FormatNameFunctionTest:testFormatFirstNameOnly',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    19 => [
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\FormatNameFunctionTest:testFormatFullName',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\Tests\unit\Covered\FormatNameFunctionTest:testFormatWithSpaces',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                ],
                byMethod: [],
            ),
        ];

        yield 'covered Database class' => [
            SafeDOMXPath::fromFile(
                Path::canonicalize(self::FIXTURES_DIR . '/codeception/coverage-xml/Database.php.xml'),
                namespace: 'p',
            ),
            new TestLocations(
                byLine: [
                    15 => [
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\DatabaseTest:testGetStuffWithoutLimit',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\DatabaseTest:testGetStuffWithLimit',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    20 => [
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\DatabaseTest:testGetStuffWithoutLimit',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\DatabaseTest:testGetStuffWithLimit',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    21 => [
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\DatabaseTest:testGetStuffWithoutLimit',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    23 => [
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\DatabaseTest:testGetStuffWithLimit',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                    25 => [
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\DatabaseTest:testGetStuffWithoutLimit',
                            filePath: null,
                            executionTime: null,
                        ),
                        new TestLocation(
                            method: 'Codeception_With_Suite_Overridings\DatabaseTest:testGetStuffWithLimit',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                ],
                byMethod: [
                    '__construct' => new SourceMethodLineRange(13, 16),
                    'getStuff' => new SourceMethodLineRange(18, 26),
                ],
            ),
        ];
    }

    private function createSourceFileInfoProviderStub(SafeDOMXPath $xPath): SourceFileInfoProvider
    {
        $sourceFileInfoProviderStub = $this->createStub(SourceFileInfoProvider::class);
        $sourceFileInfoProviderStub
            ->method('provideFileInfo')
            ->willReturn(new MockSplFileInfo(''));
        $sourceFileInfoProviderStub
            ->method('provideXPath')
            ->willReturn($xPath);

        return $sourceFileInfoProviderStub;
    }
}
