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

use function basename;
use const DIRECTORY_SEPARATOR;
use function dirname;
use Infection\FileSystem\FileSystem;
use Infection\Framework\OperatingSystem;
use Infection\TestFramework\Coverage\Locator\Throwable\InvalidReportSource;
use Infection\TestFramework\Coverage\Locator\Throwable\NoReportFound;
use Infection\TestFramework\Coverage\Locator\Throwable\TooManyReportsFound;
use Infection\TestFramework\Coverage\XmlReport\IndexXmlCoverageLocator;
use Infection\Tests\FileSystem\FileSystemTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use function sprintf;
use function strtoupper;
use Symfony\Component\Filesystem\Path;

#[Group('integration')]
#[CoversClass(IndexXmlCoverageLocator::class)]
final class IndexXmlCoverageLocatorTest extends FileSystemTestCase
{
    private const TEST_DEFAULT_RELATIVE_PATHNAME = 'coverage-xml/non-standard/test-index.xml';

    private FileSystem $fileSystem;

    private IndexXmlCoverageLocator $locator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fileSystem = new FileSystem();

        $this->locator = IndexXmlCoverageLocator::create(
            $this->fileSystem,
            $this->tmp,
            $this->tmp . DIRECTORY_SEPARATOR . self::TEST_DEFAULT_RELATIVE_PATHNAME,
        );
    }

    // This is a sanity check to ensure we have the test correctly configured.
    #[CoversNothing]
    public function test_it_the_default_path_of_this_test_is_not_the_standard_location(): void
    {
        $this->fileSystem->dumpFile(self::TEST_DEFAULT_RELATIVE_PATHNAME, '');

        $locator = IndexXmlCoverageLocator::create(
            $this->fileSystem,
            $this->tmp,
        );

        $this->expectException(NoReportFound::class);

        $locator->locate();
    }

    #[DataProvider('defaultLocationProvider')]
    public function test_it_exposes_the_default_location_used(
        string $defaultLocation,
        string $expected,
    ): void {
        $coverageDirectory = '/path/to/random-coverage';

        $locator = IndexXmlCoverageLocator::create(
            $this->fileSystem,
            $coverageDirectory,
            defaultPHPUnitXmlCoverageIndexPathname: $defaultLocation,
        );

        $actual = $locator->getDefaultLocation();

        $this->assertSame($expected, $actual);
    }

    public static function defaultLocationProvider(): iterable
    {
        yield 'canonical pathname' => [
            '/path/to/coverage/default-index.xml',
            '/path/to/coverage/default-index.xml',
        ];

        yield 'non-canonical pathname' => [
            '/path/to/coverage/dir/../default-index.xml',
            '/path/to/coverage/default-index.xml',
        ];
    }

    #[DataProvider('defaultCovergageDirectoryProvider')]
    public function test_it_infers_a_default_pathname_from_the_coverage_directory(
        string $coverageDirectory,
        string $expected,
    ): void {
        $locator = IndexXmlCoverageLocator::create(
            $this->fileSystem,
            $coverageDirectory,
        );

        $actual = $locator->getDefaultLocation();

        $this->assertSame($expected, $actual);
    }

    public static function defaultCovergageDirectoryProvider(): iterable
    {
        yield 'canonical pathname' => [
            '/path/to/coverage',
            '/path/to/coverage/coverage-xml/index.xml',
        ];

        yield 'non-canonical pathname' => [
            '/path/to/coverage/dir/..',
            '/path/to/coverage/coverage-xml/index.xml',
        ];
    }

    #[DataProvider('reportPathnameProvider')]
    public function test_it_can_find_a_report_pathname(
        string $relativePathname,
        ?string $expectedRelativePathname = null,
    ): void {
        $expectedRelativePathname ??= $relativePathname;

        $this->fileSystem->dumpFile($relativePathname, '');
        $expected = Path::normalize($this->tmp . DIRECTORY_SEPARATOR . $expectedRelativePathname);

        $actual = $this->locator->locate();

        $this->assertSame($expected, $actual);
    }

    public static function reportPathnameProvider(): iterable
    {
        yield 'exact match with the default location' => [self::TEST_DEFAULT_RELATIVE_PATHNAME];

        yield 'in sub-directory' => ['sub-dir/index.xml'];

        yield 'all caps in sub-directory' => ['sub-dir/INDEX.xml'];
    }

    public function test_it_can_locate_the_default_report_with_the_wrong_case_on_a_case_insensitive_system(): void
    {
        if (!OperatingSystem::isMacOs()) {
            $this->markTestSkipped('Requires a case-insensitive system.');
        }

        $relativePathname = dirname(self::TEST_DEFAULT_RELATIVE_PATHNAME) . DIRECTORY_SEPARATOR . strtoupper(basename(self::TEST_DEFAULT_RELATIVE_PATHNAME));

        $this->fileSystem->dumpFile($relativePathname, '');

        $expected = Path::normalize($this->tmp . DIRECTORY_SEPARATOR . self::TEST_DEFAULT_RELATIVE_PATHNAME);

        $actual = $this->locator->locate();

        $this->assertSame($expected, $actual);
    }

    public function test_it_cannot_locate_the_default_report_with_the_wrong_case_on_a_case_sensitive_system(): void
    {
        if (OperatingSystem::isMacOs()) {
            $this->markTestSkipped('Requires a case-sensitive system.');
        }

        $relativePathname = dirname(self::TEST_DEFAULT_RELATIVE_PATHNAME) . DIRECTORY_SEPARATOR . strtoupper(basename(self::TEST_DEFAULT_RELATIVE_PATHNAME));

        $this->fileSystem->dumpFile($relativePathname, '');

        $this->expectException(NoReportFound::class);

        $this->locator->locate();
    }

    public function test_it_can_locate_the_report_with_the_wrong_case(): void
    {
        $expected = Path::normalize($this->tmp . DIRECTORY_SEPARATOR . 'INDEX.xml');
        $this->fileSystem->dumpFile($expected, '');

        $locator = IndexXmlCoverageLocator::create(
            $this->fileSystem,
            $this->tmp,
            $this->tmp . '/unknown-file.xml',
        );

        $actual = $locator->locate();

        $this->assertSame($expected, $actual);
    }

    public function test_it_cannot_find_the_report_if_there_is_more_than_one_valid_report(): void
    {
        if (OperatingSystem::isMacOs()) {
            $this->markTestSkipped('Requires a case-sensitive system.');
        }

        $this->fileSystem->touch('index.xml');
        $this->fileSystem->dumpFile('sub-dir/index.xml', '');

        $expectedReportsPathnames = [
            Path::normalize($this->tmp . '/index.xml'),
            Path::normalize($this->tmp . '/sub-dir/index.xml'),
        ];

        $this->expectExceptionObject(
            new TooManyReportsFound(
                sprintf(
                    'Could not find the XML coverage index report in "%s": more than one file with the pattern "%s" was found. Found: "%s", "%s".',
                    $this->tmp,
                    IndexXmlCoverageLocator::INDEX_FILENAME_REGEX,
                    $expectedReportsPathnames[0],
                    $expectedReportsPathnames[1],
                ),
            ),
        );

        $this->locator->locate();
    }

    public function test_it_cannot_find_the_report_if_no_file_was_found(): void
    {
        $this->expectExceptionObject(
            new NoReportFound(
                sprintf(
                    'Could not find the XML coverage index report in "%s": no file with the pattern "%s" was found.',
                    $this->tmp,
                    IndexXmlCoverageLocator::INDEX_FILENAME_REGEX,
                ),
            ),
        );

        $this->locator->locate();
    }

    public function test_it_cannot_find_the_report_no_suitable_file_was_found(): void
    {
        $this->fileSystem->touch('not-a-matching-file.txt');

        $this->expectExceptionObject(
            new NoReportFound(
                sprintf(
                    'Could not find the XML coverage index report in "%s": no file with the pattern "%s" was found.',
                    $this->tmp,
                    IndexXmlCoverageLocator::INDEX_FILENAME_REGEX,
                ),
            ),
        );

        $this->locator->locate();
    }

    public function test_it_cannot_find_the_report_if_the_source_directory_is_invalid(): void
    {
        $unknownDir = $this->tmp . '/unknown-dir';

        $this->locator = IndexXmlCoverageLocator::create(
            $this->fileSystem,
            $unknownDir,
        );

        $this->expectExceptionObject(
            new InvalidReportSource(
                sprintf(
                    'Could not find the XML coverage index report in "%s": the pathname is not a valid or readable directory.',
                    $unknownDir,
                ),
            ),
        );

        $this->locator->locate();
    }

    public function test_it_cannot_locate_the_report_if_the_source_directory_is_not_a_directory(): void
    {
        $file = $this->fileSystem->tempnam($this->tmp, 'default-');
        $this->fileSystem->touch($file);

        $locator = IndexXmlCoverageLocator::create(
            $this->fileSystem,
            $file,
        );

        $this->expectExceptionObject(
            new InvalidReportSource(
                sprintf(
                    'Could not find the XML coverage index report in "%s": the pathname is not a valid or readable directory.',
                    $file,
                ),
            ),
        );

        $locator->locate();
    }
}
