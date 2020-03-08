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

use Generator;
use Infection\FileSystem\SourceFileFilter;
use IteratorIterator;
use PHPUnit\Framework\TestCase;
use function Pipeline\take;
use Symfony\Component\Finder\SplFileInfo;
use Traversable;

/**
 * @covers \Infection\FileSystem\SourceFileFilter
 */
final class SourceFileFilterTest extends TestCase
{
    public function test_it_parses_empty_filters(): void
    {
        $fileFilter = new SourceFileFilter('');
        $this->assertSame([], $fileFilter->getFilters());
    }

    public function test_it_parses_filters(): void
    {
        $fileFilter = new SourceFileFilter('src/Foo.php, src/Bar.php');
        $this->assertSame([
            'src/Foo.php',
            'src/Bar.php',
        ], $fileFilter->getFilters());
    }

    /**
     * @dataProvider fileListProvider
     */
    public function test_it_filters_traversable(string $filter, array $input, array $expected): void
    {
        $input = self::arrayToSplFileInfoTraversable($input);

        $this->assertFilter($filter, $input, $expected);
    }

    /**
     * @dataProvider fileListProvider
     */
    public function test_it_filters_iterator(string $filter, array $input, array $expected): void
    {
        $input = self::arrayToSplFileInfoTraversable($input);

        $input = new IteratorIterator($input);

        $this->assertFilter($filter, $input, $expected);
    }

    public static function fileListProvider(): Generator
    {
        yield ['src/Example', ['src/Example/Test.php'], ['src/Example/Test.php']];

        yield ['src/Foo', ['src/Example/Test.php'], []];

        yield ['', [
            'src/Foo/Test.php',
            'src/Bar/Baz.php',
            'src/Example/Test.php',
        ], [
            'src/Foo/Test.php',
            'src/Bar/Baz.php',
            'src/Example/Test.php',
        ]];

        yield ['src/Foo,src/Bar', [
            'src/Foo/Test.php',
            'src/Bar/Baz.php',
            'src/Example/Test.php',
        ], [
            'src/Foo/Test.php',
            'src/Bar/Baz.php',
        ]];
    }

    private function assertFilter(string $filter, iterable $input, array $expected): void
    {
        $fileFilter = new SourceFileFilter($filter);
        $actual = $fileFilter->filter($input);

        $actual = take($actual)
            ->map(static function (SplFileInfo $fileInfo) {
                return $fileInfo->getRealPath();
            })
            ->toArray();

        $this->assertSame($expected, $actual);
    }

    private function arrayToSplFileInfoTraversable(array $input): Traversable
    {
        return take($input)
            ->map(function (string $filename) {
                $splFileInfoMock = $this->createMock(SplFileInfo::class);
                $splFileInfoMock
                    ->method('getRealPath')
                    ->willReturn($filename);

                return $splFileInfoMock;
            });
    }
}
