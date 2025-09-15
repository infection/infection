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

namespace Infection\Tests\TestFramework\NewCoverage\PHPUnitXml\Index;

use function array_keys;
use Infection\TestFramework\NewCoverage\PHPUnitXml\Index\IndexReport;
use Infection\TestFramework\NewCoverage\PHPUnitXml\Index\LinesCoverageSummary;
use Infection\TestFramework\NewCoverage\PHPUnitXml\Index\SourceFileIndexXmlInfo;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Path;

#[CoversClass(IndexReport::class)]
final class IndexReportTest extends TestCase
{
    private const FIXTURE_DIR = __DIR__ . '/Fixtures';

    /**
     * @param non-empty-array<string, bool> $expected
     */
    #[DataProvider('hasTestsProvider')]
    public function test_it_can_tell_if_a_source_file_has_tests(
        string $xmlPathname,
        array $expected,
    ): void {
        $report = new IndexReport(
            Path::canonicalize($xmlPathname),
        );

        $actual = [];

        foreach (array_keys($expected) as $sourcePathname) {
            $actual[$sourcePathname] = $report->hasTest($sourcePathname);
        }

        $this->assertSame($expected, $actual);
    }

    public static function hasTestsProvider(): iterable
    {
        yield 'same file with different forms' => [
            self::FIXTURE_DIR . '/phpunit9-php81-pcov1.xml',
            [
                // Absolute path
                '/path/to/infection/src/CI/MemoizedCiDetector.php' => true,
                // Path relative to the project source
                'CI/MemoizedCiDetector.php' => true,
            ],
        ];

        yield 'file with no executable code' => [
            self::FIXTURE_DIR . '/phpunit9-php81-pcov1.xml',
            [
                'Configuration/Configuration.php' => false,
            ],
        ];

        yield 'same file with deeper hierarchy with different forms' => [
            self::FIXTURE_DIR . '/phpunit9-php81-pcov1.xml',
            [
                // Deeper Absolute path
                '/path/to/infection/src/Configuration/Entry/PhpStan.php' => true,
                // Deeper Path relative to the project source
                'Configuration/Entry/PhpStan.php' => true,
            ],
        ];

        // This scenario may happen when the file does exist in the codebase
        // but is not configured in the `source` of the PHPUnit configuration
        // file.
        yield 'non existent file' => [
            self::FIXTURE_DIR . '/phpunit9-php81-pcov1.xml',
            [
                // Absolute path
                '/path/to/infection/src/UnknownDirectory/Unknown.php' => false,
                // Relative path to the project source
                'UnknownDirectory/Unknown.php' => false,
            ],
        ];

        yield 'file outside of the project source' => [
            self::FIXTURE_DIR . '/phpunit9-php81-pcov1.xml',
            [
                '/path/to/unknown.php' => false,
            ],
        ];

        yield 'file for which the basename exists multiple times' => [
            self::FIXTURE_DIR . '/index-with-duplicate-entries.xml',
            [
                'Configuration/Config.php' => false,
                'Configuration/Schema/Config.php' => true,
            ],
        ];
    }

    /**
     * @param non-empty-array<string, SourceFileIndexXmlInfo> $expected
     */
    #[DataProvider('fileInfoProvider')]
    public function test_it_can_get_a_source_file_information(
        string $xmlPathname,
        array $expected,
    ): void {
        $report = new IndexReport(
            Path::canonicalize($xmlPathname),
        );

        $actual = [];

        foreach (array_keys($expected) as $sourcePathname) {
            $actual[$sourcePathname] = $report->findSourceFileInfo($sourcePathname);
        }

        $this->assertSame($expected, $actual);
    }

    public static function fileInfoProvider(): iterable
    {
        yield 'covered file' => [
            self::FIXTURE_DIR . '/phpunit9-php81-pcov1.xml',
            [
                'CI/MemoizedCiDetector.php' => new SourceFileIndexXmlInfo(
                    '/path/to/infection/src/CI/MemoizedCiDetector.php',
                    __DIR__ . '/Fixtures/CI/MemoizedCiDetector.php.xml',
                    new LinesCoverageSummary(
                        78,
                        42,
                        36,
                        8,
                        8,
                        100.,
                    ),
                ),
            ],
        ];

        yield 'covered file with no tests' => [
            self::FIXTURE_DIR . '/phpunit9-php81-pcov1.xml',
            [
                'Configuration/Configuration.php' => new SourceFileIndexXmlInfo(
                    '/path/to/infection/src/Configuration/Configuration.php',
                    __DIR__ . '/Fixtures/Configuration/Configuration.php.xml',
                    new LinesCoverageSummary(
                        369,
                        65,
                        304,
                        60,
                        0,
                        0.,
                    ),
                ),
            ],
        ];

        yield 'file outside of the project source' => [
            self::FIXTURE_DIR . '/phpunit9-php81-pcov1.xml',
            [
                '/path/to/unknown.php' => null,
            ],
        ];
    }

    public function test_the_information_is_memoized(): void
    {
        $report = new IndexReport(self::FIXTURE_DIR . '/phpunit9-php81-pcov1.xml');

        $fileInfo1 = $report->findSourceFileInfo('CI/MemoizedCiDetector.php');
        $fileInfo2 = $report->findSourceFileInfo('CI/MemoizedCiDetector.php');

        $this->assertSame($fileInfo1, $fileInfo2);
    }

    public function test_it_can_provide_information_even_once_a_full_traverse_is_done(): void
    {
        $report = new IndexReport(self::FIXTURE_DIR . '/phpunit9-php81-pcov1.xml');

        // Looking for an unknown file will cause it to process the entire XML file.
        $report->findSourceFileInfo('Unknown/Unknown.php');
        $fileInfo = $report->findSourceFileInfo('CI/MemoizedCiDetector.php');

        $this->assertNotNull($fileInfo);
    }
}
