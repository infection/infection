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

namespace Infection\Tests\TestFramework\Coverage\JUnit;

use const DIRECTORY_SEPARATOR;
use Infection\FileSystem\FakeFileSystem;
use Infection\FileSystem\FileSystem;
use Infection\Framework\OperatingSystem;
use Infection\TestFramework\Coverage\JUnit\JUnitReportLocator;
use Infection\TestFramework\Coverage\Locator\Exception\InvalidReportSource;
use Infection\TestFramework\Coverage\Locator\Exception\NoReportFound;
use Infection\TestFramework\Coverage\Locator\Exception\TooManyReportsFound;
use Infection\Tests\FileSystem\FileSystemTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use function Safe\chdir;
use function sprintf;
use Symfony\Component\Filesystem\Path;

#[CoversClass(JUnitReportLocator::class)]
final class JUnitReportLocatorTest extends FileSystemTestCase
{
    private FileSystem $filesystem;

    private JUnitReportLocator $locator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->filesystem = new FileSystem();

        $this->locator = JUnitReportLocator::create(
            $this->filesystem,
            $this->tmp,
            $this->tmp . '/junit.xml',
        );
    }



    #[DataProvider('defaultLocationProvider')]
    public function test_it_exposes_the_default_location_used(
        string $defaultLocation,
        string $expected,
    ): void {
        $coverageDirectory = '/path/to/random-coverage';

        $locator = JUnitReportLocator::create(
            $this->filesystem,
            $coverageDirectory,
            defaultJUnitPathname: $defaultLocation,
        );

        $actual = $locator->getDefaultLocation();

        $this->assertSame($expected, $actual);
    }

    public static function defaultLocationProvider(): iterable
    {
        yield 'canonical pathname' => [
            '/path/to/coverage/default-junit.xml',
            '/path/to/coverage/default-junit.xml',
        ];

        yield 'non-canonical pathname' => [
            '/path/to/coverage/dir/../default-junit.xml',
            '/path/to/coverage/default-junit.xml',
        ];
    }

    #[DataProvider('defaultCovergageDirectoryProvider')]
    public function test_it_infers_a_default_pathname_from_the_coverage_directory(
        string $coverageDirectory,
        string $expected,
    ): void {
        $locator = JUnitReportLocator::create(
            $this->filesystem,
            $coverageDirectory,
        );

        $actual = $locator->getDefaultLocation();

        $this->assertSame($expected, $actual);
    }

    public static function defaultCovergageDirectoryProvider(): iterable
    {
        yield 'canonical pathname' => [
            '/path/to/coverage',
            '/path/to/coverage/junit.xml',
        ];

        yield 'non-canonical pathname' => [
            '/path/to/coverage/dir/..',
            '/path/to/coverage/junit.xml',
        ];
    }

    public function test_it_cannot_find_the_report_if_the_source_directory_is_invalid(): void
    {
        $unknownDir = $this->tmp . '/unknown-dir';

        $locator = JUnitReportLocator::create(
            $this->filesystem,
            $unknownDir,
            '/path/to/unknown-file',
        );

        $this->expectExceptionObject(
            new InvalidReportSource(
                sprintf(
                    'Could not find the JUnit report in "%s": the pathname is not a valid or readable directory.',
                    $unknownDir,
                ),
            ),
        );

        $locator->locate();
    }

    public function test_it_cannot_find_the_report_if_there_is_more_than_one_valid_report(): void
    {
        $this->filesystem->touch('phpunit.junit.xml');
        $this->filesystem->touch('phpspec.junit.xml');

        $expectedReportsPathnames = [
            Path::normalize($this->tmp . '/phpspec.junit.xml'),
            Path::normalize($this->tmp . '/phpunit.junit.xml'),
        ];

        try {
            $this->locator->locate();

            $this->fail('Expected an exception to be thrown.');
        } catch (TooManyReportsFound $exception) {
            $this->assertEqualsCanonicalizing($expectedReportsPathnames, $exception->reportPathnames);
            $this->assertSame(
                sprintf(
                    'Could not find the JUnit report in "%s": more than one file with the pattern "%s" was found. Found: "%s", "%s".',
                    $this->tmp,
                    JUnitReportLocator::JUNIT_FILENAME_REGEX,
                    $expectedReportsPathnames[0],
                    $expectedReportsPathnames[1],
                ),
                $exception->getMessage(),
            );
        }
    }

    public function test_it_cannot_find_the_report_if_no_file_was_found(): void
    {
        $this->expectExceptionObject(
            new NoReportFound(
                sprintf(
                    'Could not find the JUnit report in "%s": no file with the pattern "%s" was found.',
                    $this->tmp,
                    JUnitReportLocator::JUNIT_FILENAME_REGEX,
                ),
            ),
        );

        $this->locator->locate();
    }

    #[DataProvider('jUnitPathnameProvider')]
    public function test_it_can_find_a_report_pathname(
        string $relativePathname,
        ?string $expectedRelativePathname = null,
    ): void {
        $expectedRelativePathname ??= $relativePathname;

        $this->filesystem->dumpFile($relativePathname, '');
        $expected = Path::normalize($this->tmp . DIRECTORY_SEPARATOR . $expectedRelativePathname);

        $actual = $this->locator->locate();

        $this->assertSame($expected, $actual);
    }

    public static function jUnitPathnameProvider(): iterable
    {
        yield 'default name' => ['junit.xml'];

        yield 'all caps default name' => [
            'JUNIT.XML',
            // On macOS (or case-insensitive systems), the default path will
            // match hence will be picked; Hence the case of the default path
            // is picked over the actual case of the file.
            OperatingSystem::isMacOs()
                ? 'junit.xml'
                : 'JUNIT.XML',
        ];

        yield 'from outdated documentation' => ['phpunit.junit.xml'];

        yield 'from outdated documentation in all caps' => ['PHPUNIT.JUNIT.XML'];

        yield 'non conventional name' => ['foo2.junit.xml'];

        yield 'in sub-directory' => ['sub-dir/junit.xml'];

        yield 'outdated doc in sub-directory' => ['sub-dir/phpunit.junit.xml'];

        yield 'non conventional name in sub-directory' => ['sub-dir/foo2.junit.xml'];

        yield 'all caps in sub-directory' => ['sub-dir/JUNIT.XML'];
    }

    #[DataProvider('invalidJunitPathnameProvider')]
    public function test_it_cannot_find_junit_files_with_invalid_names(string $relativeJUnitPathname): void
    {
        $this->filesystem->dumpFile($relativeJUnitPathname, '');

        $this->expectExceptionObject(
            new NoReportFound(
                sprintf(
                    'Could not find the JUnit report in "%s": no file with the pattern "%s" was found.',
                    $this->tmp,
                    JUnitReportLocator::JUNIT_FILENAME_REGEX,
                ),
            ),
        );

        $this->locator->locate();
    }

    public static function invalidJunitPathnameProvider(): iterable
    {
        yield 'with the wrong file ending' => ['junit.xml.dist'];
    }
}
