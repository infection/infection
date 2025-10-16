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
use function array_values;
use Infection\FileSystem\SourceFileCollector;
use function natcasesort;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use function Pipeline\take;
use SplFileInfo;
use Symfony\Component\Filesystem\Path;

#[CoversClass(SourceFileCollector::class)]
final class SourceFileCollectorTest extends TestCase
{
    private const FIXTURES_ROOT = __DIR__ . '/Fixtures';

    /**
     * @param string[] $sourceDirectories
     * @param string[] $excludedFilesOrDirectories
     */
    #[DataProvider('sourceFilesProvider')]
    public function test_it_can_collect_files(
        array $sourceDirectories,
        array $excludedFilesOrDirectories,
        array $expected,
    ): void {
        $actual = (new SourceFileCollector())->collectFiles(
            $sourceDirectories,
            $excludedFilesOrDirectories,
        );
        $normalizedActual = self::normalizePaths($actual, self::FIXTURES_ROOT);

        $this->assertSame($expected, $normalizedActual);
        $this->assertIsList(
            take($actual)->toAssoc(),
        );
    }

    public static function sourceFilesProvider(): iterable
    {
        yield 'empty' => [
            [],
            [],
            [],
        ];

        yield 'one directory' => [
            [self::FIXTURES_ROOT . '/case0'],
            [],
            [
                'case0/a.php',
                'case0/outside-symlink.php',
                'case0/sub-dir/b.php',
            ],
        ];

        yield 'multiple directories' => [
            [
                self::FIXTURES_ROOT . '/case0',
                self::FIXTURES_ROOT . '/case1',
            ],
            [],
            [
                'case0/a.php',
                'case0/outside-symlink.php',
                'case0/sub-dir/b.php',
                'case1/a.php',
                'case1/sub-dir/b.php',
            ],
        ];

        yield 'one directory with a child directory excluded via its base name' => [
            [self::FIXTURES_ROOT . '/case0'],
            ['sub-dir'],
            [
                'case0/a.php',
                'case0/outside-symlink.php',
            ],
        ];

        yield 'one directory with a child directory excluded via its full path' => [
            [self::FIXTURES_ROOT . '/case0'],
            [self::FIXTURES_ROOT . '/case0/sub-dir'],
            [
                'case0/a.php',
                'case0/outside-symlink.php',
                'case0/sub-dir/b.php',  // Does not work
            ],
        ];

        yield 'one directory with a child directory excluded via its path relative to the source root' => [
            [self::FIXTURES_ROOT . '/case0'],
            ['case0/sub-dir'],
            [
                'case0/a.php',
                'case0/outside-symlink.php',
                'case0/sub-dir/b.php',  // Does not work
            ],
        ];

        yield 'one directory with a directory excluded via its full path with the same name as an included child directory' => [
            [self::FIXTURES_ROOT . '/case0'],
            [self::FIXTURES_ROOT . '/sub-dir'],
            [
                'case0/a.php',
                'case0/outside-symlink.php',
                'case0/sub-dir/b.php',
            ],
        ];

        yield 'one directory with a directory excluded via its full path with the same name as an included directory' => [
            [self::FIXTURES_ROOT . '/case0'],
            [self::FIXTURES_ROOT . '/case0'],
            [
                // Does not work
                'case0/a.php',
                'case0/outside-symlink.php',
                'case0/sub-dir/b.php',
            ],
        ];

        yield 'one directory with a directory excluded via its base name with the same name as an included directory' => [
            [self::FIXTURES_ROOT . '/case0'],
            ['case0'],
            [
                // Does not work
                'case0/a.php',
                'case0/outside-symlink.php',
                'case0/sub-dir/b.php',
            ],
        ];

        yield 'multiple directories with a common child directory excluded via its base name' => [
            [
                self::FIXTURES_ROOT . '/case0',
                self::FIXTURES_ROOT . '/case1',
            ],
            ['sub-dir'],
            [
                'case0/a.php',
                'case0/outside-symlink.php',
                'case1/a.php',
            ],
        ];

        yield 'one directory with a child file excluded via its base name' => [
            [self::FIXTURES_ROOT . '/case1'],
            ['a.php'],
            [
                'case1/sub-dir/b.php',
            ],
        ];

        yield 'one directory with a child file and directory excluded via its base name' => [
            [self::FIXTURES_ROOT . '/case0'],
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
     * @param iterable<SplFileInfo> $fileInfos
     *
     * @return list<string> File real paths relative to the current temporary directory
     */
    private static function normalizePaths(iterable $fileInfos, string $root): array
    {
        $normalizedRoot = Path::normalize($root);

        $makePathRelativeToRoot = static fn (SplFileInfo $fileInfo) => Path::makeRelative(
            $fileInfo->getPathname(),
            $normalizedRoot,
        );

        $relativePaths = array_map(
            $makePathRelativeToRoot,
            take($fileInfos)->toList(),
        );

        natcasesort($relativePaths);

        return array_values($relativePaths);
    }

    private static function makePathRelativeToRoot(string $path): string
    {
        return Path::makeRelative($path, self::FIXTURES_ROOT);
    }
}
