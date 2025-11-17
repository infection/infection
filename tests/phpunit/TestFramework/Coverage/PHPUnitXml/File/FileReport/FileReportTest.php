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

namespace Infection\Tests\TestFramework\Coverage\PHPUnitXml\File\FileReport;

use Exception;
use Infection\TestFramework\Coverage\PHPUnitXml\File\FileReport;
use Infection\Tests\Fixtures\TestFramework\PhpUnit\Coverage\XmlCoverageFixtures;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Path;

/**
 * @phpstan-import-type LineCoverage from FileReport
 * @phpstan-import-type MethodLineRange from FileReport
 */
#[Group('integration')]
#[CoversClass(FileReport::class)]
final class FileReportTest extends TestCase
{
    private const FIXTURES_DIR = __DIR__ . '/Fixtures';

    /**
     * @param LineCoverage[]|Exception $expected
     */
    #[DataProvider('lineCoverageProvider')]
    public function test_it_can_get_the_line_coverage(
        string $pathname,
        array|Exception $expected,
    ): void {
        $report = new FileReport($pathname);

        if ($expected instanceof Exception) {
            $this->expectExceptionObject($expected);
        }

        $actual = $report->getLineCoverage();

        if (!($expected instanceof Exception)) {
            $this->assertEquals($expected, $actual);
        }
    }

    public static function lineCoverageProvider(): iterable
    {
        yield 'file from PHPUnit XML coverage' => [
            Path::canonicalize(XmlCoverageFixtures::FIXTURES_COVERAGE_DIR . '/FirstLevel/firstLevel.php.xml'),
            [
                self::createLineCoverage(
                    26,
                    [
                        'Infection\Tests\Mutator\Arithmetic\PlusTest::test_it_should_mutate_plus_expression',
                        'Infection\Tests\Mutator\Arithmetic\PlusTest::test_it_should_not_mutate_plus_with_arrays',
                    ],
                ),
                self::createLineCoverage(
                    30,
                    [
                        'Infection\Tests\Mutator\Arithmetic\PlusTest::test_it_should_mutate_plus_expression',
                        'Infection\Tests\Mutator\Arithmetic\PlusTest::test_it_should_not_mutate_plus_with_arrays',
                    ],
                ),
                self::createLineCoverage(
                    31,
                    [
                        'Infection\Tests\Mutator\Arithmetic\PlusTest::test_it_should_not_mutate_plus_with_arrays',
                    ],
                ),
                self::createLineCoverage(
                    34,
                    [
                        'Infection\Tests\Mutator\Arithmetic\PlusTest::test_it_should_mutate_plus_expression',
                    ],
                ),
            ],
        ];

        yield 'file from PHPUnit 6.x<= XML coverage' => [
            Path::canonicalize(XmlCoverageFixtures::FIXTURES_OLD_COVERAGE_DIR . '/Middleware/ReleaseRecordedEventsMiddleware.php.xml'),
            [
                self::createLineCoverage(
                    29,
                    [
                        'BornFree\TacticianDomainEvent\Tests\Middleware\ReleaseRecordedEventsMiddlewareTest::it_dispatches_recorded_events',
                        'BornFree\TacticianDomainEvent\Tests\Middleware\ReleaseRecordedEventsMiddlewareTest::it_erases_events_when_exception_is_raised',
                    ],
                ),
                self::createLineCoverage(
                    30,
                    [
                        'BornFree\TacticianDomainEvent\Tests\Middleware\ReleaseRecordedEventsMiddlewareTest::it_dispatches_recorded_events',
                        'BornFree\TacticianDomainEvent\Tests\Middleware\ReleaseRecordedEventsMiddlewareTest::it_erases_events_when_exception_is_raised',
                    ],
                ),
            ],
        ];

        yield 'file with no covered lines' => [
            self::FIXTURES_DIR . '/no-covered-lines.xml',
            [],
        ];

        yield 'file with precent signs' => [
            self::FIXTURES_DIR . '/file-with-percent-signs.xml',
            [
                self::createLineCoverage(
                    11,
                    ['ExampleTest::test_it_just_works'],
                ),
            ],
        ];

        // TODO: here in the previous implementation it was not returning the method
        yield 'file with empty precent signs' => [
            self::FIXTURES_DIR . '/file-with-empty-percentage.xml',
            [
                self::createLineCoverage(
                    11,
                    ['ExampleTest::test_it_just_works'],
                ),
            ],
        ];
    }

    /**
     * @param MethodLineRange[]|Exception $expected
     */
    #[DataProvider('methodLineRangeProvider')]
    public function test_it_can_get_the_covered_source_method_line_ranges_indexed_by_method_name(
        string $pathname,
        array|Exception $expected,
    ): void {
        $report = new FileReport($pathname);

        if ($expected instanceof Exception) {
            $this->expectExceptionObject($expected);
        }

        $actual = $report->getIndexedCoveredSourceMethodLineRanges();

        if (!($expected instanceof Exception)) {
            $this->assertEquals($expected, $actual);
        }
    }

    public static function methodLineRangeProvider(): iterable
    {
        yield 'file from PHPUnit XML coverage' => [
            Path::canonicalize(XmlCoverageFixtures::FIXTURES_COVERAGE_DIR . '/FirstLevel/firstLevel.php.xml'),
            [
                'shouldMutate' => self::createMethodLineRange(
                    'shouldMutate',
                    24,
                    35,
                ),
            ],
        ];

        yield 'file from PHPUnit 6.x<= XML coverage' => [
            Path::canonicalize(XmlCoverageFixtures::FIXTURES_OLD_COVERAGE_DIR . '/Middleware/ReleaseRecordedEventsMiddleware.php.xml'),
            [
                '__construct' => self::createMethodLineRange(
                    '__construct',
                    27,
                    31,
                ),
                'execute' => self::createMethodLineRange(
                    'execute',
                    43,
                    60,
                ),
            ],
        ];

        yield 'file with no covered lines' => [
            self::FIXTURES_DIR . '/no-covered-lines.xml',
            [],
        ];

        // TODO: more tests
    }

    /**
     * @param int<0, max> $lineNumber
     * @param non-empty-list<string> $coveredBy
     *
     * @return LineCoverage
     */
    private static function createLineCoverage(
        int $lineNumber,
        array $coveredBy,
    ): array {
        return [
            'lineNumber' => $lineNumber,
            'coveredBy' => $coveredBy,
        ];
    }

    /**
     * @param positive-int $startLine
     * @param positive-int $endLine
     *
     * @return MethodLineRange
     */
    private static function createMethodLineRange(
        string $methodName,
        int $startLine,
        int $endLine,
    ): array {
        return [
            'methodName' => $methodName,
            'startLine' => $startLine,
            'endLine' => $endLine,
        ];
    }
}
