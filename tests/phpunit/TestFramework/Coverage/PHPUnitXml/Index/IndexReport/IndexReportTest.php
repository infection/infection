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

namespace Infection\Tests\TestFramework\Coverage\PHPUnitXml\Index\IndexReport;

use function array_map;
use Exception;
use Infection\TestFramework\Coverage\PHPUnitXml\Index\IndexReport;
use Infection\TestFramework\Coverage\PHPUnitXml\Index\LinesCoverageSummary;
use Infection\TestFramework\Coverage\PHPUnitXml\Index\SourceFileIndexXmlInfo;
use Infection\TestFramework\Coverage\XmlReport\NoLineExecuted;
use Infection\Tests\Fixtures\TestFramework\PhpUnit\Coverage\XmlCoverageFixtures;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use function Pipeline\take;
use function sprintf;
use Symfony\Component\Filesystem\Path;

#[CoversClass(IndexReport::class)]
final class IndexReportTest extends TestCase
{
    private const FIXTURES_DIR = __DIR__ . '/Fixtures';

    public function test_it_throws_an_exception_if_the_report_file_does_not_exist(): void
    {
        $report = new IndexReport('/path/to/unknown.xml');

        $this->expectExceptionObject(
            new InvalidArgumentException('The path "/path/to/unknown.xml" is not a file.'),
        );

        // The file is lazy-loaded and the result is lazy too
        take($report->getSourceFileInfos())->toAssoc();
    }

    /**
     * @param list<SourceFileIndexXmlInfo>|Exception $expected
     */
    #[DataProvider('indexProvider')]
    public function test_it_provides_file_information(
        string $pathname,
        array|Exception $expected,
    ): void {
        $report = new IndexReport($pathname);

        if ($expected instanceof Exception) {
            $this->expectExceptionObject($expected);
        }

        $actual = take($report->getSourceFileInfos())->toAssoc();

        if (!($expected instanceof Exception)) {
            $this->assertEquals($expected, $actual);
        }
    }

    public static function indexProvider(): iterable
    {
        yield 'PHPUnit index XML coverage' => [
            XmlCoverageFixtures::FIXTURES_COVERAGE_DIR . '/index.xml',
            [
                new SourceFileIndexXmlInfo(
                    '/path/to/src/FirstLevel/firstLevel.php',
                    Path::canonicalize(XmlCoverageFixtures::FIXTURES_COVERAGE_DIR) . '/FirstLevel/firstLevel.php.xml',
                    new LinesCoverageSummary(
                        total: 55,
                        comments: 2,
                        code: 53,
                        executable: 31,
                        executed: 0,
                        percent: .0,
                    ),
                ),
                new SourceFileIndexXmlInfo(
                    '/path/to/src/FirstLevel/SecondLevel/secondLevel.php',
                    Path::canonicalize(XmlCoverageFixtures::FIXTURES_COVERAGE_DIR) . '/FirstLevel/SecondLevel/secondLevel.php.xml',
                    new LinesCoverageSummary(
                        total: 114,
                        comments: 22,
                        code: 92,
                        executable: 59,
                        executed: 0,
                        percent: .0,
                    ),
                ),
                new SourceFileIndexXmlInfo(
                    '/path/to/src/FirstLevel/SecondLevel/secondLevelTrait.php',
                    Path::canonicalize(XmlCoverageFixtures::FIXTURES_COVERAGE_DIR) . '/FirstLevel/SecondLevel/secondLevelTrait.php.xml',
                    new LinesCoverageSummary(
                        total: 114,
                        comments: 22,
                        code: 92,
                        executable: 59,
                        executed: 0,
                        percent: .0,
                    ),
                ),
                new SourceFileIndexXmlInfo(
                    '/path/to/src/zeroLevel.php',
                    Path::canonicalize(XmlCoverageFixtures::FIXTURES_COVERAGE_DIR) . '/zeroLevel.php.xml',
                    new LinesCoverageSummary(
                        total: 99,
                        comments: 12,
                        code: 87,
                        executable: 58,
                        executed: 0,
                        percent: .0,
                    ),
                ),
                new SourceFileIndexXmlInfo(
                    '/path/to/src/noPercentage.php',
                    Path::canonicalize(XmlCoverageFixtures::FIXTURES_COVERAGE_DIR) . '/noPercentage.php.xml',
                    new LinesCoverageSummary(
                        total: 55,
                        comments: 2,
                        code: 53,
                        executable: 31,
                        executed: 0,
                        percent: .0,
                    ),
                ),
            ],
        ];

        yield 'PHPUnit 6.x<= index XML coverage' => [
            XmlCoverageFixtures::FIXTURES_OLD_COVERAGE_DIR . '/index.xml',
            [
                new SourceFileIndexXmlInfo(
                    'Middleware/ReleaseRecordedEventsMiddleware.php',
                    Path::canonicalize(XmlCoverageFixtures::FIXTURES_OLD_COVERAGE_DIR) . '/Middleware/ReleaseRecordedEventsMiddleware.php.xml',
                    new LinesCoverageSummary(
                        total: 60,
                        comments: 22,
                        code: 38,
                        executable: 11,
                        executed: 11,
                        percent: 100.,
                    ),
                ),
            ],
        ];

        // TODO: look up this: we have two different files with content that look quite different...
        yield 'PHPUnit 6.x<= index XML coverage (2)' => [
            self::FIXTURES_DIR . '/phpunit6-and-older-index.xml',
            [
                new SourceFileIndexXmlInfo(
                    'FirstLevel/firstLevel.php',
                    self::FIXTURES_DIR . '/FirstLevel/firstLevel.php.xml',
                    new LinesCoverageSummary(
                        total: 55,
                        comments: 2,
                        code: 53,
                        executable: 31,
                        executed: 0,
                        percent: .0,
                    ),
                ),
                new SourceFileIndexXmlInfo(
                    'FirstLevel/SecondLevel/secondLevel.php',
                    self::FIXTURES_DIR . '/FirstLevel/SecondLevel/secondLevel.php.xml',
                    new LinesCoverageSummary(
                        total: 114,
                        comments: 22,
                        code: 92,
                        executable: 59,
                        executed: 0,
                        percent: .0,
                    ),
                ),
                new SourceFileIndexXmlInfo(
                    'FirstLevel/SecondLevel/secondLevelTrait.php',
                    self::FIXTURES_DIR . '/FirstLevel/SecondLevel/secondLevelTrait.php.xml',
                    new LinesCoverageSummary(
                        total: 114,
                        comments: 22,
                        code: 92,
                        executable: 59,
                        executed: 0,
                        percent: .0,
                    ),
                ),
                new SourceFileIndexXmlInfo(
                    'zeroLevel.php',
                    self::FIXTURES_DIR . '/zeroLevel.php.xml',
                    new LinesCoverageSummary(
                        total: 99,
                        comments: 12,
                        code: 87,
                        executable: 58,
                        executed: 0,
                        percent: .0,
                    ),
                ),
                new SourceFileIndexXmlInfo(
                    'noPercentage.php',
                    self::FIXTURES_DIR . '/noPercentage.php.xml',
                    new LinesCoverageSummary(
                        total: 55,
                        comments: 2,
                        code: 53,
                        executable: 31,
                        executed: 0,
                        percent: .0,
                    ),
                ),
            ],
        ];

        yield 'invalid XML' => [
            __FILE__,
            new InvalidArgumentException(
                sprintf(
                    'The file "%s" does not contain valid XML.',
                    __FILE__,
                ),
            ),
        ];

        yield 'zero lines executed' => [
            self::FIXTURES_DIR . '/no-lines-executed-index.xml',
            // TODO: exception should be thrown; unless it is the JUnit report that should throw it now
            [],
            //new NoLineExecuted('foo'),
        ];

        yield 'lines is not present' => [
            self::FIXTURES_DIR . '/lines-not-present-index.xml',
            // TODO: exception should be thrown; unless it is the JUnit report that should throw it now
            [],
            //new NoLineExecuted('foo'),
        ];
    }

    /**
     * @param list<SourceFileIndexXmlInfo> $expected
     */
    #[DataProvider('validIndexProvider')]
    public function test_it_can_find_a_specific_file_information(
        string $pathname,
        array $expected,
    ): void {
        $report = new IndexReport($pathname);

        $expectedSourcePathNames = array_map(
            static fn (SourceFileIndexXmlInfo $sourceInfo) => $sourceInfo->sourcePathname,
            $expected,
        );

        $actual = array_map(
            static fn (string $sourcePathname) => $report->findSourceFileInfo($sourcePathname),
            $expectedSourcePathNames,
        );

        $this->assertEquals($expected, $actual);
    }

    public static function validIndexProvider(): iterable
    {
        yield from take(self::indexProvider())
            ->filter(
                static function (array $scenario): bool {
                    $expected = $scenario[1];

                    return !($expected instanceof Exception);
                },
            )
            ->toAssoc();
    }

    // TODO: find where this test goes now
    //    public function test_it_errors_when_the_source_file_could_not_be_found(): void
    //    {
    //        $incorrectCoverageSrcDir = Path::canonicalize(XmlCoverageFixtures::FIXTURES_INCORRECT_COVERAGE_DIR . '/src');
    //
    //        $provider = new SourceFileInfoProvider(
    //            '/path/to/index.xml',
    //            XmlCoverageFixtures::FIXTURES_COVERAGE_DIR,
    //            'zeroLevel.php.xml',
    //            $incorrectCoverageSrcDir,
    //        );
    //
    //        try {
    //            $provider->provideFileInfo();
    //
    //            $this->fail();
    //        } catch (InvalidCoverage $exception) {
    //            $this->assertSame(
    //                sprintf(
    //                    'Could not find the source file "%s/zeroLevel.php" referred by '
    //                    . '"%s/zeroLevel.php.xml". Make sure the coverage used is up to date',
    //                    $incorrectCoverageSrcDir,
    //                    Path::canonicalize(XmlCoverageFixtures::FIXTURES_COVERAGE_DIR),
    //                ),
    //                $exception->getMessage(),
    //            );
    //            $this->assertSame(0, $exception->getCode());
    //            $this->assertNull($exception->getPrevious());
    //        }
    //    }

    // TODO: double check where this test goes now.
    //    #[DataProvider('invalidIndexFile')]
    //    public function test_it_errors_for_git_diff_lines_mode_when_no_lines_were_executed(string $xml): void
    //    {
    //        $filename = __DIR__ . '/generated_index.xml';
    //        $this->filesystem->dumpFile($filename, $xml);
    //
    //        $this->expectException(NoLineExecutedInDiffLinesMode::class);
    //
    //        (new IndexXmlCoverageParser(true))->parse(
    //            $filename,
    //            __DIR__,
    //        );
    //    }
}
