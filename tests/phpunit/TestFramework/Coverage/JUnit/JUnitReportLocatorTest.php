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
use Infection\TestFramework\Coverage\JUnit\JUnitReportLocator;
use Infection\TestFramework\Coverage\Locator\Throwable\InvalidReportSource;
use Infection\TestFramework\Coverage\Locator\Throwable\NoReportFound;
use Infection\TestFramework\Coverage\Locator\Throwable\TooManyReportsFound;
use Infection\Tests\FileSystem\FileSystemTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use function sprintf;
use function strtoupper;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;

#[Group('integration')]
#[CoversClass(JUnitReportLocator::class)]
final class JUnitReportLocatorTest extends FileSystemTestCase
{
    private const DEFAULT_JUNIT = 'test-junit.xml';

    private Filesystem $filesystem;

    private JUnitReportLocator $locator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->filesystem = new Filesystem();

        $this->locator = JUnitReportLocator::create(
            $this->tmp,
            $this->tmp . DIRECTORY_SEPARATOR . self::DEFAULT_JUNIT,
        );
    }

    #[DataProvider('defaultLocationProvider')]
    public function test_it_exposes_the_default_location_used(
        string $defaultLocation,
        string $expected,
    ): void {
        $coverageDirectory = '/path/to/random-coverage';

        $locator = JUnitReportLocator::create(
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
        $locator = JUnitReportLocator::create($coverageDirectory);

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

    public function test_it_returns_the_default_path_if_it_exists(): void
    {
        $default = $this->filesystem->tempnam($this->tmp, 'default-');

        $locator = new JUnitReportLocator(
            '/path/to/unknown-dir',
            $default,
        );

        // Note that here we can't really check that we do not use the FS
        // since we use `file_exists()` directly.
        $actual = $locator->locate();

        $this->assertSame($default, $actual);
    }

    public function test_it_caches_the_result_found(): void
    {
        $default = $this->filesystem->tempnam($this->tmp, 'default-');

        $this->locator = new JUnitReportLocator(
            '/path/to/unknown-dir',
            $default,
        );

        // Note that here we can't really check that we do not use a Filesystem
        // object which we could mock.
        $actual1 = $this->locator->locate();
        $actual2 = $this->locator->locate();

        $this->assertSame($default, $actual1);
        $this->assertSame($default, $actual2);
    }

    #[DataProvider('reportPathnameProvider')]
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

    public static function reportPathnameProvider(): iterable
    {
        yield 'outdated doc' => ['phpunit.junit.xml'];

        yield 'non conventional name' => ['foo2.junit.xml'];

        yield 'in sub-directory' => ['sub-dir/junit.xml'];

        yield 'outdated doc in sub-directory' => ['sub-dir/phpunit.junit.xml'];

        yield 'non conventional name in sub-directory' => ['sub-dir/foo2.junit.xml'];

        yield 'all caps in sub-directory' => ['sub-dir/JUNIT.XML'];
    }

    public function test_it_can_locate_the_default_report_with_the_wrong_case(): void
    {
        $this->filesystem->dumpFile(strtoupper(self::DEFAULT_JUNIT), '');

        $expected = Path::normalize($this->tmp . DIRECTORY_SEPARATOR . self::DEFAULT_JUNIT);

        $actual = $this->locator->locate();

        $this->assertSame($expected, $actual);
    }

    public function test_it_can_locate_the_report_with_the_wrong_case(): void
    {
        $expected = Path::normalize($this->tmp . DIRECTORY_SEPARATOR . strtoupper(self::DEFAULT_JUNIT));
        $this->filesystem->dumpFile($expected, '');

        $locator = JUnitReportLocator::create(
            $this->tmp,
            $this->tmp . '/unknown-file.xml',
        );

        $actual = $locator->locate();

        $this->assertSame($expected, $actual);
    }

    public function test_it_cannot_find_the_report_if_there_is_more_than_one_valid_report(): void
    {
        $this->filesystem->touch('phpunit.junit.xml');
        $this->filesystem->touch('phpspec.junit.xml');

        $expectedReportsPathnames = [
            Path::normalize($this->tmp . '/phpspec.junit.xml'),
            Path::normalize($this->tmp . '/phpunit.junit.xml'),
        ];

        $this->expectExceptionObject(
            new TooManyReportsFound(
                sprintf(
                    'Could not find the JUnit report in "%s": more than one file with the pattern "%s" was found. Found: "%s", "%s".',
                    $this->tmp,
                    JUnitReportLocator::JUNIT_FILENAME_REGEX,
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
                    'Could not find the JUnit report in "%s": no file with the pattern "%s" was found.',
                    $this->tmp,
                    JUnitReportLocator::JUNIT_FILENAME_REGEX,
                ),
            ),
        );

        $this->locator->locate();
    }

    public function test_it_cannot_find_the_report_no_suitable_file_was_found(): void
    {
        $this->filesystem->touch('not-a-matching-file.txt');

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

    public function test_it_cannot_find_the_report_if_the_source_directory_is_invalid(): void
    {
        $unknownDir = $this->tmp . '/unknown-dir';

        $locator = new JUnitReportLocator(
            $unknownDir,
            $this->tmp . '/junit.xml',
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

    public function test_it_cannot_locate_the_report_if_the_source_directory_is_not_a_directory(): void
    {
        $file = $this->filesystem->tempnam($this->tmp, 'default-');

        $locator = new JUnitReportLocator(
            $file,
            $this->tmp . '/junit.xml',
        );

        $this->expectExceptionObject(
            new InvalidReportSource(
                sprintf(
                    'Could not find the JUnit report in "%s": the pathname is not a valid or readable directory.',
                    $file,
                ),
            ),
        );

        $locator->locate();
    }
}
