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
use Infection\Framework\OperatingSystem;
use Infection\TestFramework\Coverage\JUnit\JUnitReportLocator;
use Infection\TestFramework\Coverage\Locator\Throwable\InvalidReportSource;
use Infection\TestFramework\Coverage\Locator\Throwable\NoReportFound;
use Infection\TestFramework\Coverage\Locator\Throwable\TooManyReportsFound;
use Infection\Tests\FileSystem\FileSystemTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use function Safe\touch;
use function sprintf;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;

#[Group('integration')]
#[CoversClass(JUnitReportLocator::class)]
final class JUnitReportLocatorTest extends FileSystemTestCase
{
    private JUnitReportLocator $locator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->locator = new JUnitReportLocator(
            $this->tmp,
            $this->tmp . '/junit.xml',
        );
    }

    public function test_it_can_locate_the_default_junit_file(): void
    {
        touch('junit.xml');

        $expected = Path::canonicalize($this->tmp . '/junit.xml');

        $this->assertSame($expected, $this->locator->locate());
        // Call second time to check the cached result
        $this->assertSame($expected, $this->locator->locate());
    }

    public function test_it_can_locate_the_default_junit_file_with_the_wrong_case(): void
    {
        if (!OperatingSystem::isMacOs()) {
            $this->markTestSkipped('Cannot test this on case-sensitive OS');
        }

        touch('JUNIT.XML');

        $expected = Path::canonicalize($this->tmp . '/junit.xml');

        $actual = $this->locator->locate();

        $this->assertSame($expected, $actual);
    }

    #[DataProvider('jUnitPathsProvider')]
    public function test_it_can_find_more_exotic_junit_file_names(string $jUnitRelativePaths): void
    {
        (new Filesystem())->dumpFile($jUnitRelativePaths, '');

        $expected = Path::canonicalize($this->tmp . DIRECTORY_SEPARATOR . $jUnitRelativePaths);

        $this->assertSame($expected, $this->locator->locate());
        // Call second time to check the cached result
        $this->assertSame($expected, $this->locator->locate());
    }

    public function test_it_cannot_locate_the_junit_file_if_the_result_is_ambiguous(): void
    {
        touch('phpunit.junit.xml');
        touch('phpspec.junit.xml');

        $expectedReportsPathnames = [
            Path::canonicalize($this->tmp . '/phpspec.junit.xml'),
            Path::canonicalize($this->tmp . '/phpunit.junit.xml'),
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

    public function test_it_cannot_locate_the_junit_file_if_none_found(): void
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

    public function test_it_cannot_locate_the_junit_file_in_a_non_existent_coverage_directory(): void
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

    public function test_it_cannot_locate_the_junit_file_if_the_coverage_directory_is_not_a_directory(): void
    {
        $file = $this->tmp . '/file';
        touch($file);

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

    public static function jUnitPathsProvider(): iterable
    {
        yield 'outdated doc' => ['phpunit.junit.xml'];

        yield 'non conventional name' => ['foo2.junit.xml'];

        yield 'in sub-directory' => ['sub-dir/junit.xml'];

        yield 'outdated doc in sub-directory' => ['sub-dir/phpunit.junit.xml'];

        yield 'non conventional name in sub-directory' => ['sub-dir/foo2.junit.xml'];

        yield 'all caps in sub-directory' => ['sub-dir/JUNIT.XML'];
    }
}
