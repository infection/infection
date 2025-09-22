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

namespace Infection\Tests\TestFramework\NewCoverage\JUnit;

use Infection\TestFramework\NewCoverage\JUnit\JUnitReportLocator;
use Infection\TestFramework\NewCoverage\Locator\NoReportFound;
use PHPUnit\Framework\Attributes\RequiresOperatingSystemFamily;
use Symfony\Component\Filesystem\Path;
use const DIRECTORY_SEPARATOR;
use Infection\FileSystem\Locator\FileNotFound;
use Infection\Tests\FileSystem\FileSystemTestCase;
use function Infection\Tests\normalizePath;
use const PHP_OS_FAMILY;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use function Safe\chdir;
use function Safe\realpath;
use function Safe\touch;
use function sprintf;
use Symfony\Component\Filesystem\Filesystem;

#[Group('integration')]
#[CoversClass(JUnitReportLocator::class)]
final class JUnitReportLocatorTest extends FileSystemTestCase
{
    private JUnitReportLocator $locator;

    protected function setUp(): void
    {
        parent::setUp();

        // Move to the temporary directory: we want to make sure the setUp closures are executed
        // there since they do not have access to the tmp yet, so their paths are relative
        chdir($this->tmp);

        $this->locator = JUnitReportLocator::create(
            $this->filesystem,
            $this->tmp,
            $this->tmp . '/junit.xml',
        );
    }

    protected function tearDown(): void
    {
        chdir($this->cwd);

        parent::tearDown();
    }

    public function test_it_can_locate_the_default_junit_file(): void
    {
        $this->filesystem->touch('junit.xml');

        $expected = Path::normalize($this->tmp . '/junit.xml');

        self::assertSame($expected, $this->locator->locate());
        // Call second time to check the cached result
        self::assertSame($expected, $this->locator->locate());
    }

    // OSX is not case-sensitive
    #[RequiresOperatingSystemFamily('/^(?!Darwin)$/')]
    public function test_it_can_locate_the_default_junit_file_with_the_wrong_case(): void
    {
        $this->filesystem->touch('JUNIT.XML');

        $expected = Path::normalize($this->tmp . '/junit.xml');

        $actual = $this->locator->locate();

        self::assertSame($expected, $actual);
    }

    #[DataProvider('jUnitPathsProvider')]
    public function test_it_can_find_more_exotic_junit_file_names(string $jUnitRelativePaths): void
    {
        $this->filesystem->dumpFile($jUnitRelativePaths, '');
        $expected = Path::normalize($this->tmp . DIRECTORY_SEPARATOR . $jUnitRelativePaths);

        $actual = $this->locator->locate();

        self::assertSame($expected, $actual);
    }

    public function test_it_cannot_locate_the_junit_file_if_the_result_is_ambiguous(): void
    {
        $this->filesystem->touch('phpunit.junit.xml');
        $this->filesystem->touch('phpspec.junit.xml');

        $this->expectExceptionObject(
            new NoReportFound(
                sprintf(
                    'Could not find a JUnit report in "%s": more than one file with the pattern "/^(.+\.)?junit\.xml$/i" has been found. Found: "%s", "%s".',
                    $this->tmp,
                    Path::normalize($this->tmp . DIRECTORY_SEPARATOR . 'phpspec.junit.xml'),
                    Path::normalize($this->tmp . DIRECTORY_SEPARATOR . 'phpunit.junit.xml'),
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
                    'Could not find a JUnit report in "%s": no file with the pattern "/^(.+\.)?junit\.xml$/i" has been found.',
                    $this->tmp,
                ),
            ),
        );

        $this->locator->locate();
    }

    public function test_it_cannot_locate_the_junit_file_in_a_non_existent_coverage_directory(): void
    {
        $locator = new JUnitReportLocator(
            $this->filesystem,
            $this->tmp . '/unknown-dir',
            $this->tmp . '/junit.xml',
        );

        $this->expectExceptionObject(
            new NoReportFound(
                sprintf(
                    'Could not find a JUnit report in "%s": the directory does not exist or is not readable.',
                    Path::canonicalize($this->tmp . '/unknown-dir',),
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
