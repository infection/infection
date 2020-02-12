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

namespace Infection\Tests\FileSystem;

use function array_map;
use function array_values;
use Generator;
use Infection\FileSystem\SourceFileCollector;
use PHPUnit\Framework\TestCase;
use function Safe\natcasesort;
use Webmozart\PathUtil\Path;

/**
 * @covers \Infection\FileSystem\Finder\FilterableFinder
 * @covers \Infection\FileSystem\SourceFileCollector
 */
final class SourceFileCollectorTest extends TestCase
{
    private const FIXTURES = __DIR__ . '/../Fixtures/Files/SourceFileCollector';

    /**
     * @dataProvider sourceFilesProvider
     */
    public function test_it_can_collect_files(array $sourceDirectories, array $excludedFiles, string $filter, array $expected): void
    {
        $root = self::FIXTURES;

        $files = (new SourceFileCollector())->collectFiles($sourceDirectories, $excludedFiles, $filter);

        $this->assertSame(
            $expected,
            self::normalizePaths($files, $root)
        );

        if ($files !== []) {
            $this->assertSame(
                range(0, count($files) - 1),
                array_keys($files),
                'Expected the collected files to be a list'
            );
        }
    }

    public function sourceFilesProvider(): Generator
    {
        yield 'empty' => [
            [],
            [],
            '',
            [],
        ];

        yield 'one directory, no filter, no excludes' => [
            [self::FIXTURES . '/case0'],
            [],
            '',
            [
                'case0/a.php',
                'case0/outside-symlink.php',
                'case0/sub-dir/b.php',
            ],
        ];

        yield 'multiple directories, no filter, no excludes' => [
            [self::FIXTURES . '/case0', self::FIXTURES . '/case1'],
            [],
            '',
            [
                'case0/a.php',
                'case0/outside-symlink.php',
                'case0/sub-dir/b.php',
                'case1/a.php',
                'case1/sub-dir/b.php',
            ],
        ];

        yield 'filter by file name, no excludes' => [
            [self::FIXTURES . '/case0'],
            [],
            'a.php',
            [
                'case0/a.php',
            ],
        ];

        yield 'filter by file name with multiple sources, no excludes' => [
            [self::FIXTURES . '/case0', self::FIXTURES . '/case1'],
            [],
            'a.php',
            [
                'case0/a.php',
                'case1/a.php',
            ],
        ];

        yield 'filter by file name file in sub-dir, no excludes' => [
            [self::FIXTURES . '/case0'],
            [],
            'b.php',
            [
                'case0/sub-dir/b.php',
            ],
        ];

        yield 'filter by file name relative from source root, no excludes' => [
            [self::FIXTURES . '/case0'],
            [],
            'case0/a.php',
            [
                'case0/a.php',
            ],
        ];

        yield 'filter by file name relative from source root which excludes other possible match, no excludes' => [
            [self::FIXTURES . '/case0', self::FIXTURES . '/case1'],
            [],
            'case0/a.php',
            [
                'case0/a.php',
            ],
        ];

        yield 'filter by file name relative from file outside of source roo, no excludes' => [
            [self::FIXTURES . '/case0'],
            [],
            './../Fixtures/Files/SourceFileCollector/case0/a.php',
            [],
        ];

        yield 'filter by absolute file name, no excludes' => [
            [self::FIXTURES . '/case0'],
            [],
            self::FIXTURES . '/case0/a.php',
            [],
        ];

        yield 'filter by comma separated file names, no excludes' => [
            [self::FIXTURES . '/case0'],
            [],
            'a.php,b.php',
            [
                'case0/a.php',
                'case0/sub-dir/b.php',
            ],
        ];

        yield 'filter by comma separated (with spaces) file names, no excludes' => [
            [self::FIXTURES . '/case0'],
            [],
            'a.php, b.php',
            [
                'case0/a.php',
                'case0/sub-dir/b.php',
            ],
        ];

        yield 'filter by comma separated (with spaces & empty) file names, no excludes' => [
            [self::FIXTURES . '/case0'],
            [],
            'a.php, ,, b.php',
            [
                'case0/a.php',
                'case0/sub-dir/b.php',
            ],
        ];

        yield 'filter by unknown file' => [
            [self::FIXTURES . '/case0'],
            [],
            'unknown',
            [],
        ];

        yield 'one directory, no filter, one excludes' => [
            [self::FIXTURES . '/case0'],
            ['sub-dir'],
            '',
            [
                'case0/a.php',
                'case0/outside-symlink.php',
            ],
        ];

        yield 'one directory, no filter, absolute path excludes' => [
            [self::FIXTURES . '/case0'],
            [self::FIXTURES . '/sub-dir'],
            '',
            [
                'case0/a.php',
                'case0/outside-symlink.php',
                'case0/sub-dir/b.php',
            ],
        ];

        yield 'one directory, no filter, relative path excludes relative to source root' => [
            [self::FIXTURES . '/case0'],
            ['case0/sub-dir'],
            '',
            [
                'case0/a.php',
                'case0/outside-symlink.php',
                'case0/sub-dir/b.php',
            ],
        ];

        yield 'multiple directories, no filter, one common excludes' => [
            [self::FIXTURES . '/case0', self::FIXTURES . '/case1'],
            ['sub-dir'],
            '',
            [
                'case0/a.php',
                'case0/outside-symlink.php',
                'case1/a.php',
            ],
        ];

        yield 'nominal' => [
            [self::FIXTURES . '/case0', self::FIXTURES . '/case1'],
            ['sub-dir'],
            'a.php',
            [
                'case0/a.php',
                'case1/a.php',
            ],
        ];
    }

    /**
     * @param string[] $files
     *
     * @return string[] File real paths relative to the current temporary directory
     */
    private static function normalizePaths(array $files, string $root): array
    {
        $root = Path::normalize($root);

        $files = array_values(
            array_map(
                static function (string $file) use ($root): string {
                    return Path::makeRelative($file, $root);
                },
                $files
            )
        );

        natcasesort($files);

        return array_values($files);
    }
}
