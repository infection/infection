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

namespace Infection\Tests\FileSystem\SourceFileCollector;

use function array_map;
use const DIRECTORY_SEPARATOR;
use Infection\FileSystem\SourceFileCollector;
use function ksort;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use function Pipeline\take;
use SplFileInfo;
use Symfony\Component\Filesystem\Path;

#[CoversClass(SourceFileCollector::class)]
final class SourceFileCollectorTest extends TestCase
{
    private const FIXTURES = __DIR__ . '/Fixtures';

    /**
     * @param string[] $sourceDirectories
     * @param string[] $excludedFilesOrDirectories
     * @param list<string> $expectedList
     */
    #[DataProvider('sourceFilesProvider')]
    public function test_it_can_collect_files(array $sourceDirectories, array $excludedFilesOrDirectories, array $expectedList): void
    {
        $files = (new SourceFileCollector())->collectFiles($sourceDirectories, $excludedFilesOrDirectories);

        self::assertIsEqualCanonicalizing(
            $expectedList,
            take($files)->toAssoc(),
        );
    }

    /**
     * @return iterable<string, array{string[], string[], list<string>}>
     */
    public static function sourceFilesProvider(): iterable
    {
        yield 'empty' => [
            [],
            [],
            [],
        ];

        yield 'one directory, no filter, no excludes' => [
            [self::FIXTURES . '/case0'],
            [],
            [
                'case0/a.php',
                'case0/outside-symlink.php',
                'case0/sub-dir/b.php',
            ],
        ];

        yield 'multiple directories, no filter, no excludes' => [
            [self::FIXTURES . '/case0', self::FIXTURES . '/case1'],
            [],
            [
                'case0/a.php',
                'case0/outside-symlink.php',
                'case0/sub-dir/b.php',
                'case1/a.php',
                'case1/sub-dir/b.php',
            ],
        ];

        yield 'one directory, no filter, one excludes' => [
            [self::FIXTURES . '/case0'],
            ['sub-dir'],
            [
                'case0/a.php',
                'case0/outside-symlink.php',
            ],
        ];

        yield 'one directory, no filter, absolute path excludes' => [
            [self::FIXTURES . '/case0'],
            [self::FIXTURES . '/sub-dir'],
            [
                'case0/a.php',
                'case0/outside-symlink.php',
                'case0/sub-dir/b.php',
            ],
        ];

        yield 'one directory, no filter, relative path excludes relative to source root' => [
            [self::FIXTURES . '/case0'],
            ['case0/sub-dir'],
            [
                'case0/a.php',
                'case0/outside-symlink.php',
                'case0/sub-dir/b.php',
            ],
        ];

        yield 'multiple directories, no filter, one common excludes' => [
            [self::FIXTURES . '/case0', self::FIXTURES . '/case1'],
            ['sub-dir'],
            [
                'case0/a.php',
                'case0/outside-symlink.php',
                'case1/a.php',
            ],
        ];

        yield 'exclude file by its name' => [
            [self::FIXTURES . '/case1'],
            ['a.php'],
            [
                'case1/sub-dir/b.php',
            ],
        ];

        yield 'one directory, no filter, one common excludes and one file exclude' => [
            [self::FIXTURES . '/case0'],
            [
                'sub-dir',
                'a.php',
            ],
            [
                'case0/outside-symlink.php',
            ],
        ];
    }

    /**
     * @param list<string> $expectedList
     * @param array<string, SplFileInfo> $actual
     */
    private static function assertIsEqualCanonicalizing(
        array $expectedList,
        array $actual,
    ): void {
        $root = self::FIXTURES;

        $normalizedExpected = self::createExpected($expectedList, $root);
        $normalizedActual = self::normalizePaths($actual, $root);

        ksort($normalizedExpected);
        ksort($normalizedActual);

        self::assertSame($normalizedExpected, $normalizedActual);
    }

    /**
     * @param array<string, SplFileInfo> $files
     *
     * @return array<string, string> File real paths relative to the current temporary directory
     */
    private static function normalizePaths(array $files, string $root): array
    {
        $root = Path::normalize($root);

        return array_map(
            static fn (SplFileInfo $fileInfo): string => Path::makeRelative($fileInfo->getPathname(), $root),
            $files,
        );
    }

    /**
     * @param list<string> $expectedList
     *
     * @return array<string, string> File real paths relative to the current temporary directory
     */
    private static function createExpected(array $expectedList, string $root): array
    {
        $expected = [];

        foreach ($expectedList as $path) {
            $expected[$root . DIRECTORY_SEPARATOR . $path] = $path;
        }

        return $expected;
    }
}
