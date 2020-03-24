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
use Infection\FileSystem\Locator\FileNotFound;
use Infection\TestFramework\Coverage\JUnit\JUnitReportLocator;
use Infection\Tests\FileSystem\FileSystemTestCase;
use function Infection\Tests\normalizePath;
use const PHP_OS_FAMILY;
use function Safe\chdir;
use function Safe\realpath;
use function Safe\sprintf;
use function Safe\touch;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @group integration
 */
final class JUnitReportLocatorTest extends FileSystemTestCase
{
    /**
     * @var JUnitReportLocator
     */
    private $locator;

    protected function setUp(): void
    {
        parent::setUp();

        // Move to the temporary directory: we want to make sure the setUp closures are executed
        // there since they do not have access to the tmp yet, so their paths are relative
        chdir($this->tmp);

        $this->locator = new JUnitReportLocator(
            $this->tmp . '/coverage-xml',
            $this->tmp . '/junit.xml'
        );
    }

    protected function tearDown(): void
    {
        chdir($this->cwd);

        parent::tearDown();
    }

    public function test_it_can_locate_the_default_JUnit_file(): void
    {
        touch('junit.xml');

        $expected = normalizePath(realpath($this->tmp . '/junit.xml'));

        $this->assertSame($expected, $this->locator->locate());
        // Call second time to check the cached result
        $this->assertSame($expected, $this->locator->locate());
    }

    public function test_it_can_locate_the_default_JUnit_file_with_the_wrong_case(): void
    {
        if (PHP_OS_FAMILY !== 'Darwin') {
            $this->markTestSkipped('Cannot test this on case-sensitive OS');
        }

        touch('JUNIT.XML');

        $expected = normalizePath(realpath($this->tmp . '/junit.xml'));

        $actual = $this->locator->locate();

        $this->assertSame($expected, $actual);
    }

    /**
     * @dataProvider jUnitPathsProvider
     */
    public function test_it_can_find_more_exotic_JUnit_file_names(string $jUnitRelativePaths): void
    {
        (new Filesystem())->dumpFile($jUnitRelativePaths, '');

        $expected = normalizePath(realpath($this->tmp . DIRECTORY_SEPARATOR . $jUnitRelativePaths));

        $this->assertSame($expected, $this->locator->locate());
        // Call second time to check the cached result
        $this->assertSame($expected, $this->locator->locate());
    }

    public function test_it_cannot_locate_the_JUnit_file_if_the_result_is_ambiguous(): void
    {
        touch('phpunit.junit.xml');
        touch('phpspec.junit.xml');

        $this->expectException(FileNotFound::class);
        $this->expectExceptionMessage(sprintf(
            'Could not locate the JUnit file: more than one file has been found with the pattern "*.junit.xml": "%s", "%s"',
            normalizePath(realpath($this->tmp . DIRECTORY_SEPARATOR . 'phpspec.junit.xml')),
            normalizePath(realpath($this->tmp . DIRECTORY_SEPARATOR . 'phpunit.junit.xml'))
        ));

        $this->locator->locate();
    }

    public function test_it_cannot_locate_the_JUnit_file_if_none_found(): void
    {
        $this->expectException(FileNotFound::class);
        $this->expectExceptionMessage(sprintf(
            'Could not find any file with the pattern "*.junit.xml" in "%s"',
            $this->tmp
        ));

        $this->locator->locate();
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
