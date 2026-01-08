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

namespace Infection\Tests\Source\Collector\BasicSourceCollector;

use function array_map;
use Exception;
use Infection\Configuration\SourceFilter\PlainFilter;
use Infection\Source\Collector\BasicSourceCollector;
use Infection\Source\Exception\NoSourceFound;
use Infection\Tests\FileSystem\FileSystemTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use function Pipeline\take;
use function sort;
use SplFileInfo;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Finder\SplFileInfo as FinderSplFileInfo;

#[Group('integration')]
#[CoversClass(BasicSourceCollector::class)]
final class BasicSourceCollectorTest extends FileSystemTestCase
{
    private const FIXTURES_ROOT = __DIR__ . '/Fixtures';

    private Filesystem $filesystem;

    protected function setUp(): void
    {
        parent::setUp();

        $this->filesystem = new Filesystem();
    }

    /**
     * @param non-empty-string[] $sourceDirectories
     * @param non-empty-string[] $excludedFilesOrDirectories
     * @param list<non-empty-string>|Exception $expected
     */
    #[DataProvider('sourceFilesProvider')]
    public function test_it_can_collect_files(
        array $sourceDirectories,
        array $excludedFilesOrDirectories,
        array|Exception $expected,
    ): void {
        $collector = new BasicSourceCollector(
            $sourceDirectories,
            $excludedFilesOrDirectories,
            null,
        );

        if ($expected instanceof Exception) {
            $this->expectExceptionObject($expected);
        }

        $files = $collector->collect();

        if (!($expected instanceof Exception)) {
            self::assertIsEqualUnorderedLists($expected, $files);
        }
    }

    public static function sourceFilesProvider(): iterable
    {
        yield 'empty' => [
            [],
            [],
            new NoSourceFound(
                isSourceFiltered: false,
                message: 'No source file found for the configured sources.',
            ),
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
     * @param string[] $filePaths
     * @param string[]|Exception $expected
     */
    #[DataProvider('filteredFilesProvider')]
    public function test_it_filters_the_collected_files(
        ?PlainFilter $filter,
        array $filePaths,
        array|Exception $expected,
    ): void {
        foreach ($filePaths as $filePath) {
            $this->filesystem->dumpFile($filePath, '');
        }

        $collector = BasicSourceCollector::create(
            configurationPathname: '/path/to/project',
            sourceDirectories: [$this->tmp],
            excludedFilesOrDirectories: [],
            filter: $filter,
        );

        if ($expected instanceof Exception) {
            $this->expectExceptionObject($expected);
        }

        /** @var FinderSplFileInfo[] $fileInfos */
        $fileInfos = $collector->collect();

        $actual = take($fileInfos)
            ->map(static fn (FinderSplFileInfo $fileInfo) => $fileInfo->getRelativePathname())
            ->toList();

        if (!($expected instanceof Exception)) {
            $this->assertEqualsCanonicalizing($expected, $actual);
        }
    }

    public static function filteredFilesProvider(): iterable
    {
        yield [
            new PlainFilter(['src/Example']),
            [
                'src/Example/Test.php',
            ],
            [
                'src/Example/Test.php',
            ],
        ];

        yield [
            new PlainFilter(['src/Foo']),
            [
                'src/Example/Test.php',
            ],
            new NoSourceFound(
                isSourceFiltered: true,
                message: 'No source file found for the filter applied to the configured sources. The filter used was: "src/Foo".',
            ),
        ];

        yield [
            null,
            [
                'src/Foo/Test.php',
                'src/Bar/Baz.php',
                'src/Example/Test.php',
            ],
            [
                'src/Foo/Test.php',
                'src/Bar/Baz.php',
                'src/Example/Test.php',
            ],
        ];

        yield [
            new PlainFilter([
                'src/Foo',
                'src/Bar',
            ]),
            [
                'src/Foo/Test.php',
                'src/Bar/Baz.php',
                'src/Example/Test.php',
            ],
            [
                'src/Foo/Test.php',
                'src/Bar/Baz.php',
            ],
        ];
    }

    /**
     * @param list<string> $expectedList
     * @param SplFileInfo[] $actual
     */
    private static function assertIsEqualUnorderedLists(
        array $expectedList,
        array $actual,
    ): void {
        $root = self::FIXTURES_ROOT;

        $normalizedExpected = self::createExpected($expectedList, $root);
        $normalizedActual = self::normalizePaths($actual, $root);

        sort($normalizedExpected);
        sort($normalizedActual);

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
            static fn (SplFileInfo $fileInfo): string => Path::makeRelative(
                $fileInfo->getPathname(),
                $root,
            ),
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
            $pathname = Path::normalize($root . '/' . $path);

            $expected[$pathname] = Path::normalize($path);
        }

        return $expected;
    }
}
