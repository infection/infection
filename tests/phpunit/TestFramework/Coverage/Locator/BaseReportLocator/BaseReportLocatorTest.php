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

namespace Infection\Tests\TestFramework\Coverage\Locator\BaseReportLocator;

use Infection\FileSystem\FileSystem;
use Infection\TestFramework\Coverage\Locator\BaseReportLocator;
use Infection\TestFramework\Coverage\Locator\ReportLocator;
use Infection\TestFramework\Coverage\Locator\Throwable\InvalidReportSource;
use Infection\TestFramework\Coverage\Locator\Throwable\NoReportFound;
use Infection\TestFramework\Coverage\Locator\Throwable\TooManyReportsFound;
use Infection\Tests\FileSystem\FileSystemTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use function sprintf;
use Symfony\Component\Filesystem\Path;

#[Group('integration')]
#[CoversClass(BaseReportLocator::class)]
#[CoversClass(DemoReportLocator::class)]
final class BaseReportLocatorTest extends FileSystemTestCase
{
    private FileSystem $filesystem;

    private ReportLocator $locator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->filesystem = new FileSystem();

        $this->locator = new DemoReportLocator(
            $this->filesystem,
            $this->tmp,
            '/path/to/unknown-file',
        );
    }

    public function test_it_returns_the_default_path_if_it_exists(): void
    {
        $default = Path::canonicalize('/path/to/default-file');

        $filesystemMock = $this->createMock(FileSystem::class);

        $filesystemMock
            ->expects($this->once())
            ->method('isReadableFile')
            ->willReturn(true);

        $this->locator = new DemoReportLocator(
            $filesystemMock,
            '/path/to/unknown-dir',
            $default,
        );

        $actual = $this->locator->locate();

        $this->assertSame($default, $actual);
    }

    public function test_it_cannot_find_the_report_if_the_source_directory_is_invalid(): void
    {
        $unknownDir = $this->tmp . '/unknown-dir';

        $locator = new DemoReportLocator(
            $this->filesystem,
            $unknownDir,
            '/path/to/unknown-file',
        );

        $this->expectExceptionObject(
            new InvalidReportSource(
                sprintf(
                    'The pathname "%s" is not a valid or readable directory.',
                    $unknownDir,
                ),
            ),
        );

        $locator->locate();
    }

    public function test_it_cannot_find_the_report_if_there_is_more_than_one_valid_report(): void
    {
        $this->filesystem->touch('file1.demo');
        $this->filesystem->touch('file2.demo');

        $expectedReportsPathnames = [
            Path::canonicalize($this->tmp . '/file1.demo'),
            Path::canonicalize($this->tmp . '/file2.demo'),
        ];

        try {
            $this->locator->locate();
        } catch (TooManyReportsFound $exception) {
            $this->assertEqualsCanonicalizing($expectedReportsPathnames, $exception->reportPathnames);
            $this->assertSame(
                sprintf(
                    'Found "%s", "%s".',
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
                    'No report found in "%s".',
                    $this->tmp,
                ),
            ),
        );

        $this->locator->locate();
    }

    public function test_it_cannot_find_the_report_not_suitable_file_was_found(): void
    {
        $this->filesystem->touch('not-a-matching-file.txt');

        $this->expectExceptionObject(
            new NoReportFound(
                sprintf(
                    'No report found in "%s".',
                    $this->tmp,
                ),
            ),
        );

        $this->locator->locate();
    }

    public function test_it_can_find_a_report_pathname(): void
    {
        $this->filesystem->touch('report.demo');
        $expected = Path::canonicalize($this->tmp . '/report.demo');

        $actual = $this->locator->locate();

        $this->assertSame($expected, $actual);
    }
}
