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

namespace Infection\Tests\Source\Collector;

use Infection\Source\Collector\SchemaSourceCollector;
use Infection\TestFramework\Coverage\Trace;
use Infection\Tests\Fixtures\MockSplFileInfo;
use function ksort;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use function Pipeline\take;
use SplFileInfo;
use Symfony\Component\Filesystem\Path;
use Traversable;

#[CoversClass(SchemaSourceCollector::class)]
final class SchemaSourceCollectorTest extends TestCase
{
    private const FIXTURES_ROOT = __DIR__ . '/Fixtures';

    /**
     * @param string[] $expectedFilters
     */
    #[DataProvider('filterProvider')]
    public function test_it_can_parse_and_normalize_string_filter(
        string $filter,
        array $expectedFilters,
    ): void {
        $fileFilter = SchemaSourceCollector::create(
            filter: $filter,
            sourceDirectories: [],
            excludedDirectoriesOrFiles: [],
        );

        $actual = $fileFilter->filters;

        $this->assertEqualsCanonicalizing($expectedFilters, $actual);
    }

    public static function filterProvider(): iterable
    {
        yield 'empty' => ['', []];

        yield 'nominal' => [
            'src/Foo.php, src/Bar.php',
            [
                'src/Foo.php',
                'src/Bar.php',
            ],
        ];

        yield 'spaces & untrimmed string' => [
            '  src/Foo.php,, , src/Bar.php  ',
            [
                'src/Foo.php',
                'src/Bar.php',
            ],
        ];
    }

    /**
     * @param string[] $sourceDirectories
     * @param string[] $excludedFilesOrDirectories
     * @param list<string> $expectedList
     */
    #[DataProvider('sourceFilesProvider')]
    public function test_it_can_collect_files(
        ?string $filter,
        array $sourceDirectories,
        array $excludedFilesOrDirectories,
        array $expectedList,
    ): void {
        $collector = SchemaSourceCollector::create(
            $filter,
            $sourceDirectories,
            $excludedFilesOrDirectories,
        );

        $files = $collector->collect();

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
            null,
            [],
            [],
            [],
        ];

        yield 'one directory' => [
            null,
            [self::FIXTURES_ROOT . '/case0'],
            [],
            [
                'case0/a.php',
                'case0/outside-symlink.php',
                'case0/sub-dir/b.php',
            ],
        ];

        yield 'multiple directories' => [
            null,
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
            null,
            [self::FIXTURES_ROOT . '/case0'],
            ['sub-dir'],
            [
                'case0/a.php',
                'case0/outside-symlink.php',
            ],
        ];

        yield 'one directory with a child directory excluded via its full path' => [
            null,
            [self::FIXTURES_ROOT . '/case0'],
            [self::FIXTURES_ROOT . '/case0/sub-dir'],
            [
                'case0/a.php',
                'case0/outside-symlink.php',
                'case0/sub-dir/b.php',  // Does not work; https://github.com/infection/infection/issues/2594
            ],
        ];

        yield 'one directory with a child directory excluded via its path relative to the source root' => [
            null,
            [self::FIXTURES_ROOT . '/case0'],
            ['case0/sub-dir'],
            [
                'case0/a.php',
                'case0/outside-symlink.php',
                'case0/sub-dir/b.php',  // Does not work; https://github.com/infection/infection/issues/2594
            ],
        ];

        yield 'one directory with a directory excluded via its full path with the same name as an included child directory' => [
            null,
            [self::FIXTURES_ROOT . '/case0'],
            [self::FIXTURES_ROOT . '/sub-dir'],
            [
                'case0/a.php',
                'case0/outside-symlink.php',
                'case0/sub-dir/b.php',
            ],
        ];

        yield 'one directory with a directory excluded via its full path with the same name as an included directory' => [
            null,
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
            null,
            [self::FIXTURES_ROOT . '/case0'],
            ['case0'],
            [
                // Does not work
                // https://github.com/infection/infection/issues/2594
                'case0/a.php',
                'case0/outside-symlink.php',
                'case0/sub-dir/b.php',
            ],
        ];

        yield 'multiple directories with a common child directory excluded via its base name' => [
            null,
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
            null,
            [self::FIXTURES_ROOT . '/case1'],
            ['a.php'],
            [
                'case1/sub-dir/b.php',
            ],
        ];

        yield 'one directory with a child file and directory excluded via its base name' => [
            null,
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
     * @param non-empty-string|null $filter
     * @param string[] $filePaths
     * @param string[] $expectedFilePaths
     */
    #[DataProvider('fileListProvider')]
    public function test_it_filters_spl_file_info_files_traversable(
        ?string $filter,
        array $filePaths,
        array $expectedFilePaths,
    ): void {
        $collector = SchemaSourceCollector::create(
            filter: $filter,
            sourceDirectories: [],
            excludedDirectoriesOrFiles: [],
        );

        $files = self::createSplFileInfosTraversable($filePaths);

        $actual = take($collector->filter($files))
            ->map(self::mapFileInfoOrTraceToRealPath(...))
            ->toList();

        $this->assertSame($expectedFilePaths, $actual);
    }

    /**
     * @param string[] $filePaths
     * @param string[] $expectedFilePaths
     */
    #[DataProvider('fileListProvider')]
    public function test_it_filters_traces_traversable(
        string $filter,
        array $filePaths,
        array $expectedFilePaths,
    ): void {
        $collector = SchemaSourceCollector::create(
            filter: $filter,
            sourceDirectories: [],
            excludedDirectoriesOrFiles: [],
        );

        $traces = $this->createTracesTraversable($filePaths);

        $actual = take($collector->filter($traces))
            ->map(self::mapFileInfoOrTraceToRealPath(...))
            ->toList();

        $this->assertSame($expectedFilePaths, $actual);
    }

    public static function fileListProvider(): iterable
    {
        yield [
            'src/Example',
            [
                'src/Example/Test.php',
            ],
            [
                'src/Example/Test.php',
            ],
        ];

        yield [
            'src/Foo',
            [
                'src/Example/Test.php',
            ],
            [],
        ];

        yield [
            '',
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
            'src/Foo,src/Bar',
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

    private static function mapFileInfoOrTraceToRealPath(SplFileInfo|Trace $fileInfoOrTrace): string
    {
        return $fileInfoOrTrace->getRealPath();
    }

    /**
     * @param string[] $filePaths
     *
     * @return Traversable<MockSplFileInfo>
     */
    private static function createSplFileInfosTraversable(array $filePaths): Traversable
    {
        return take($filePaths)
            ->map(static fn (string $realPath): MockSplFileInfo => new MockSplFileInfo([
                'realPath' => $realPath,
                'type' => 'file',
                'mode' => 'r+',
            ]));
    }

    /**
     * @param string[] $filePaths
     *
     * @return Traversable<Trace>
     */
    private function createTracesTraversable(array $filePaths): Traversable
    {
        return take($filePaths)
            ->map(function (string $filename): Trace {
                $traceMock = $this->createMock(Trace::class);
                $traceMock
                    ->method('getRealPath')
                    ->willReturn($filename)
                ;

                return $traceMock;
            })
        ;
    }

    /**
     * @param list<string> $expectedList
     * @param array<string, SplFileInfo> $actual
     */
    private static function assertIsEqualCanonicalizing(
        array $expectedList,
        array $actual,
    ): void {
        $root = self::FIXTURES_ROOT;

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

        $result = [];

        foreach ($files as $pathName => $fileInfo) {
            $result[Path::normalize($pathName)] = Path::makeRelative(
                $fileInfo->getPathname(),
                $root,
            );
        }

        return $result;
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
