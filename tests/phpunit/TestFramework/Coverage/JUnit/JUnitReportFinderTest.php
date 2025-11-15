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
use Infection\TestFramework\Coverage\Locator\Exception\InvalidReportSource;
use Infection\TestFramework\Coverage\Locator\Exception\NoReportFound;
use Infection\TestFramework\Coverage\Locator\Exception\TooManyReportsFound;
use Infection\TestFramework\Coverage\Locator\FileReportFinder\FileReportFinder;
use Infection\Tests\FileSystem\FileSystemTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use function Safe\chdir;
use function sprintf;
use Symfony\Component\Filesystem\Path;

#[Group('integration')]
#[CoversClass(JUnitReportLocator::class)]
final class JUnitReportFinderTest extends FileSystemTestCase
{
    private FileReportFinder $finder;

    protected function setUp(): void
    {
        parent::setUp();

        chdir($this->tmp);

        $this->finder = new JUnitReportLocator($this->filesystem);
    }

    protected function tearDown(): void
    {
        chdir($this->cwd);

        parent::tearDown();
    }

    public function test_it_cannot_find_the_report_if_the_source_directory_is_invalid(): void
    {
        $unknownDir = $this->tmp . '/unknown-dir';

        $this->expectExceptionObject(
            new InvalidReportSource(
                sprintf(
                    'Could not find a JUnit report in "%s": the pathname is not a valid or readable directory.',
                    $unknownDir,
                ),
            ),
        );

        $this->finder->lookup($unknownDir);
    }

    public function test_it_cannot_find_the_report_if_there_is_more_than_one_valid_report(): void
    {
        $this->filesystem->touch('phpunit.junit.xml');
        $this->filesystem->touch('phpspec.junit.xml');

        $expectedReportsPathnames = [
            // The order is not guaranteed!
            Path::normalize($this->tmp . '/phpunit.junit.xml'),
            Path::normalize($this->tmp . '/phpspec.junit.xml'),
        ];

        try {
            $this->finder->lookup($this->tmp);
        } catch (TooManyReportsFound $exception) {
            $this->assertSame($expectedReportsPathnames, $exception->reportPathnames);
            $this->assertSame(
                sprintf(
                    'Could not find a JUnit report in "%s": more than one file with the pattern "%s" has been found. Found: "%s", "%s".',
                    $this->tmp,
                    '/^(.+\.)?junit\.xml$/i',
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
                    'Could not find a JUnit report in "%s": no file with the pattern "%s" has been found.',
                    $this->coverageDirectory,
                    FileReportFinder::JUNIT_FILENAME_REGEX,
                ),
            ),
        );

        $this->finder->lookup($this->tmp);
    }

    public function test_it_cannot_find_the_report_not_suitable_file_was_found(): void
    {
        $this->filesystem->touch('not-a-matching-file.txt');

        $this->expectExceptionObject(
            new NoReportFound(''),
        );

        $this->finder->lookup($this->tmp);
    }

    public function test_it_can_find_a_report_pathname(): void
    {
        $this->filesystem->touch('report.demo');
        $expected = Path::canonicalize($this->tmp . '/report.demo');

        $actual = $this->finder->lookup($this->tmp);

        $this->assertSame($expected, $actual);
    }

    #[DataProvider('validJunitPathsProvider')]
    public function test_it_find_junit_files_with_various_names(string $relativeJUnitPathname): void
    {
        $this->filesystem->dumpFile($relativeJUnitPathname, '');
        $expected = [Path::canonicalize($this->tmp . DIRECTORY_SEPARATOR . $relativeJUnitPathname)];

        $actual = $this->finder->lookup($this->tmp);

        $this->assertSame($expected, $actual);
    }

    public static function validJunitPathsProvider(): iterable
    {
        yield 'conventional name' => ['junit.xml'];

        yield 'all caps name' => ['JUNIT.XML'];

        yield 'from outdated documentation' => ['phpunit.junit.xml'];

        yield 'non conventional name' => ['foo2.junit.xml'];

        yield 'in sub-directory' => ['sub-dir/junit.xml'];

        yield 'outdated doc in sub-directory' => ['sub-dir/phpunit.junit.xml'];

        yield 'non conventional name in sub-directory' => ['sub-dir/foo2.junit.xml'];

        yield 'all caps in sub-directory' => ['sub-dir/JUNIT.XML'];
    }

    #[DataProvider('invalidJunitPathsProvider')]
    public function test_it_cannot_find_junit_files_with_invalid_names(string $relativeJUnitPathname): void
    {
        $this->filesystem->dumpFile($relativeJUnitPathname, '');

        $actual = $this->finder->lookup($this->tmp);

        $this->assertSame([], $actual);
    }

    public static function invalidJunitPathsProvider(): iterable
    {
        yield 'with the wrong file ending' => ['junit.xml.dist'];
    }

    public function test_it_can_find_multiple_junit_files(): void
    {
        $this->filesystem->touch('phpunit.junit.xml');
        $this->filesystem->touch('phpspec.junit.xml');

        $expected = [
            // The order matters!
            Path::normalize($this->tmp . '/phpspec.junit.xml'),
            Path::normalize($this->tmp . '/phpunit.junit.xml'),
        ];

        $actual = $this->finder->lookup($this->tmp);

        $this->assertSame($expected, $actual);
    }

    public function test_it_cannot_find_files_if_none_matches_the_searched_pattern(): void
    {
        $this->filesystem->touch('not-a-junit-report.xml');

        $actual = $this->finder->lookup($this->tmp);

        $this->assertSame([], $actual);
    }

    public function test_it_cannot_locate_the_junit_file_in_a_non_existent_coverage_directory(): void
    {
        $this->expectExceptionObject(
            new NoReportFound(
                sprintf(
                    'Could not find a JUnit report in "%s": the directory does not exist or is not readable.',
                    Path::canonicalize($this->tmp . '/unknown-dir'),
                ),
            ),
        );

        $this->finder->lookup($this->tmp . '/unknown-dir');
    }
}
