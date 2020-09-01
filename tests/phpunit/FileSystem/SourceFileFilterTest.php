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

use function array_values;
use Infection\FileSystem\SourceFileFilter;
use Infection\TestFramework\Coverage\Trace;
use IteratorIterator;
use PHPUnit\Framework\TestCase;
use function Pipeline\take;
use SplFileInfo;
use Traversable;

final class SourceFileFilterTest extends TestCase
{
    /**
     * @dataProvider filterProvider
     *
     * @param string[] $expectedFilters
     */
    public function test_it_can_parse_and_normalize_string_filter(
        string $filter,
        array $expectedFilters
    ): void {
        $fileFilter = new SourceFileFilter($filter, []);

        $this->assertSame($expectedFilters, array_values($fileFilter->getFilters()));
    }

    /**
     * @dataProvider fileListProvider
     *
     * @param string[] $filePaths
     * @param string[] $expectedFilePaths
     */
    public function test_it_filters_spl_file_info_files_traversable(
        string $filter,
        array $filePaths,
        array $expectedFilePaths
    ): void {
        $filePaths = $this->createSplFileInfosTraversable($filePaths);

        $this->assertFiltersExpectedInput($filter, $filePaths, $expectedFilePaths);
    }

    /**
     * @dataProvider fileListProvider
     *
     * @param string[] $filePaths
     * @param string[] $expectedFilePaths
     */
    public function test_it_filters_traces_traversable(
        string $filter,
        array $filePaths,
        array $expectedFilePaths
    ): void {
        $filePaths = $this->createTracesTraversable($filePaths);

        $this->assertFiltersExpectedInput($filter, $filePaths, $expectedFilePaths);
    }

    /**
     * @dataProvider fileListProvider
     *
     * @param string[] $filePaths
     * @param string[] $expectedFilePaths
     */
    public function test_it_filters_spl_file_info_iterator(
        string $filter,
        array $filePaths,
        array $expectedFilePaths
    ): void {
        $filePaths = $this->createSplFileInfosTraversable($filePaths);

        $filePaths = new IteratorIterator($filePaths);

        $this->assertFiltersExpectedInput($filter, $filePaths, $expectedFilePaths);
    }

    /**
     * @dataProvider fileListProvider
     *
     * @param string[] $filePaths
     * @param string[] $expectedFilePaths
     */
    public function test_it_filters_trace_iterator(
        string $filter,
        array $filePaths,
        array $expectedFilePaths
    ): void {
        $filePaths = $this->createTracesTraversable($filePaths);

        $filePaths = new IteratorIterator($filePaths);

        $this->assertFiltersExpectedInput($filter, $filePaths, $expectedFilePaths);
    }

    public function filterProvider(): iterable
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

    /**
     * @param iterable<Trace> $input
     * @param string[] $expectedFilePaths
     */
    private function assertFiltersExpectedInput(
        string $filter,
        iterable $input,
        array $expectedFilePaths
    ): void {
        $actual = (new SourceFileFilter($filter, []))->filter($input);

        $actual = take($actual)
            ->map(static function ($traceOrFileInfo) {
                /* @var Trace|SplFileInfo */
                return $traceOrFileInfo->getRealPath();
            })
            ->toArray();

        $this->assertSame($expectedFilePaths, $actual);
    }

    /**
     * @param string[] $filePaths
     *
     * @return Traversable<SplFileInfo>
     */
    private function createSplFileInfosTraversable(array $filePaths): Traversable
    {
        return take($filePaths)
            ->map(function (string $filename) {
                $traceMock = $this->createMock(SplFileInfo::class);
                $traceMock
                    ->method('getRealPath')
                    ->willReturn($filename)
                ;

                return $traceMock;
            })
        ;
    }

    /**
     * @param string[] $filePaths
     *
     * @return Traversable<Trace>
     */
    private function createTracesTraversable(array $filePaths): Traversable
    {
        return take($filePaths)
            ->map(function (string $filename) {
                $traceMock = $this->createMock(Trace::class);
                $traceMock
                    ->method('getRealPath')
                    ->willReturn($filename)
                ;

                return $traceMock;
            })
        ;
    }
}
