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

use Infection\Tests\FileSystem\FileSystemTestCase;
use const DIRECTORY_SEPARATOR;
use Fidry\FileSystem\FS;
use Infection\FileSystem\Locator\FileNotFound;
use Infection\Framework\OperatingSystem;
use Infection\TestFramework\Coverage\XmlReport\IndexXmlCoverageLocator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use function Safe\chdir;
use function sprintf;
use Symfony\Component\Filesystem\Path;

#[Group('integration')]
#[CoversClass(IndexXmlCoverageLocator::class)]
final class IndexXmlCoverageLocatorTest extends FileSystemTestCase
{
    /**
     * @var IndexXmlCoverageLocator
     */
    private $locator;

    protected function setUp(): void
    {
        parent::setUp();

        // Move to the temporary directory: we want to make sure the setUp closures are executed
        // there since they do not have access to the tmp yet, so their paths are relative
        chdir($this->tmp);

        $this->locator = new IndexXmlCoverageLocator($this->tmp);
    }

    protected function tearDown(): void
    {
        chdir($this->cwd);

        parent::tearDown();
    }

    public function test_it_can_locate_the_default_index_file(): void
    {
        FS::dumpFile('coverage-xml/index.xml', '');

        $expected = Path::canonicalize($this->tmp . '/coverage-xml/index.xml');

        $this->assertSame($expected, $this->locator->locate());
        // Call second time to check the cached result
        $this->assertSame($expected, $this->locator->locate());
    }

    public function test_it_can_locate_the_default_index_file_with_the_wrong_case(): void
    {
        if (!OperatingSystem::isMacOs()) {
            $this->markTestSkipped('Cannot test this on case-sensitive OS');
        }

        FS::dumpFile('coverage-xml/INDEX.XML', '');

        $expected = Path::canonicalize($this->tmp . '/coverage-xml/index.xml');

        $actual = $this->locator->locate();

        $this->assertSame($expected, $actual);
    }

    #[DataProvider('indexPathsProvider')]
    public function test_it_can_find_more_exotic_index_file_names(string $indexRelativePath): void
    {
        FS::dumpFile($indexRelativePath, '');

        $expected = Path::canonicalize($this->tmp . DIRECTORY_SEPARATOR . $indexRelativePath);

        $this->assertSame($expected, $this->locator->locate());
        // Call second time to check the cached result
        $this->assertSame($expected, $this->locator->locate());
    }

    public function test_it_cannot_locate_the_index_file_if_the_result_is_ambiguous(): void
    {
        touch('index.xml');
        FS::dumpFile('sub-dir/index.xml', '');

        $this->expectException(FileNotFound::class);
        $this->expectExceptionMessage(sprintf(
            'Could not locate the XML coverage index file. More than one file has been found: "%s", "%s"',
            Path::canonicalize($this->tmp . DIRECTORY_SEPARATOR . 'index.xml'),
            Path::canonicalize($this->tmp . DIRECTORY_SEPARATOR . 'sub-dir/index.xml'),
        ));

        $this->locator->locate();
    }

    public function test_it_cannot_locate_the_index_file_if_none_found(): void
    {
        $this->expectException(FileNotFound::class);
        $this->expectExceptionMessage(sprintf(
            'Could not find any "index.xml" file in "%s"',
            $this->tmp,
        ));

        $this->locator->locate();
    }

    public function test_it_cannot_locate_the_index_file_in_a_non_existent_coverage_directory(): void
    {
        $this->locator = new IndexXmlCoverageLocator($this->tmp . '/unknown-dir');

        $this->expectException(FileNotFound::class);
        $this->expectExceptionMessage(sprintf(
            'Could not find any "index.xml" file in "%s"',
            $this->tmp . '/unknown-dir',
        ));

        $this->locator->locate();
    }

    public static function indexPathsProvider(): iterable
    {
        yield 'nominal' => ['coverage-xml/index.xml'];

        yield 'sub-dir' => ['coverage-xml/sub-dir/index.xml'];
    }
}
