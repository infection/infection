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

namespace Infection\Tests\FileSystem\SplFileInfoFactory;

use function current;
use function dirname;
use Infection\FileSystem\SplFileInfoFactory;
use function iterator_to_array;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo as SymfonyFinderSplFileInfo;

#[CoversClass(SplFileInfoFactory::class)]
#[Group('integration')]
final class SplFileInfoFactoryTest extends TestCase
{
    public const FIXTURE_DIR = __DIR__ . '/Fixtures';

    #[DataProvider('fromPathProvider')]
    public function test_it_can_create_a_file_info_from_a_path(
        string $filePath,
        string $basePath,
        SymfonyFinderSplFileInfo|Finder $expected,
    ): void {
        if ($expected instanceof Finder) {
            $expected = self::getFileFromFinder($expected);
        }

        $actual = SplFileInfoFactory::fromPath($filePath, $basePath);

        self::assertSplFileInfoStateIs($expected, $actual);
    }

    public static function fromPathProvider(): iterable
    {
        yield 'file with same directory as base path' => [
            self::FIXTURE_DIR . '/test_file.php',
            self::FIXTURE_DIR,
            new SymfonyFinderSplFileInfo(
                Path::canonicalize(self::FIXTURE_DIR . '/test_file.php'),
                '',
                'test_file.php',
            ),
        ];

        yield 'file with same directory as base path (from Finder)' => [
            self::FIXTURE_DIR . '/test_file.php',
            self::FIXTURE_DIR,
            Finder::create()
                ->files()
                ->path('test_file.php')
                ->in(self::FIXTURE_DIR),
        ];

        yield 'file in subdirectory relative to base path' => [
            self::FIXTURE_DIR . '/sub/nested_file.php',
            self::FIXTURE_DIR,
            new SymfonyFinderSplFileInfo(
                Path::canonicalize(self::FIXTURE_DIR . '/sub/nested_file.php'),
                'sub',
                'sub/nested_file.php',
            ),
        ];

        yield 'deeply nested file relative to base path' => [
            self::FIXTURE_DIR . '/deep/nested/structure/deep_file.php',
            self::FIXTURE_DIR . '/deep',
            new SymfonyFinderSplFileInfo(
                Path::canonicalize(self::FIXTURE_DIR . '/deep/nested/structure/deep_file.php'),
                'nested/structure',
                'nested/structure/deep_file.php',
            ),
        ];

        yield 'deeply nested file relative to base path (from Finder)' => [
            self::FIXTURE_DIR . '/deep/nested/structure/deep_file.php',
            self::FIXTURE_DIR . '/deep',
            Finder::create()
                ->files()
                ->path('nested/structure/deep_file.php')
                ->in(self::FIXTURE_DIR . '/deep'),
        ];

        yield 'file with parent directory as base path' => [
            self::FIXTURE_DIR . '/sub/nested_file.php',
            dirname(self::FIXTURE_DIR),
            new SymfonyFinderSplFileInfo(
                Path::canonicalize(self::FIXTURE_DIR . '/sub/nested_file.php'),
                'Fixtures/sub',
                'Fixtures/sub/nested_file.php',
            ),
        ];
    }

    private static function assertSplFileInfoStateIs(
        SymfonyFinderSplFileInfo $expected,
        SymfonyFinderSplFileInfo $actual,
    ): void {
        self::assertSame(
            self::collectSplFileInfoState($expected),
            self::collectSplFileInfoState($actual),
        );
    }

    private static function collectSplFileInfoState(SymfonyFinderSplFileInfo $splFileInfo): array
    {
        return [
            'realPath' => $splFileInfo->getRealPath(),
            'relativePath' => $splFileInfo->getRelativePath(),
            'relativePathname' => $splFileInfo->getRelativePathname(),
        ];
    }

    private static function getFileFromFinder(Finder $finder): SymfonyFinderSplFileInfo
    {
        $files = iterator_to_array($finder);
        self::assertCount(1, $files);

        return current($files);
    }
}
