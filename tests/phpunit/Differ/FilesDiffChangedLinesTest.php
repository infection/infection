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

namespace Infection\Tests\Differ;

use Generator;
use Infection\Differ\ChangedLinesRange;
use Infection\Differ\DiffChangedLinesParser;
use Infection\Differ\FilesDiffChangedLines;
use Infection\Git\Git;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use function sprintf;

#[CoversClass(FilesDiffChangedLines::class)]
final class FilesDiffChangedLinesTest extends TestCase
{
    public function test_it_memoizes_parsed_results(): void
    {
        [$parser, $git] = $this->prepareServices([]);

        $filesDiffChangedLines = new FilesDiffChangedLines(
            $parser,
            $git,
        );

        $filesDiffChangedLines->contains('/path/to/File.php', 1, 1, 'master');

        // the second call should reuse memoized results cached previously
        $filesDiffChangedLines->contains('/path/to/File.php', 1, 1, 'master');
    }

    /**
     * @param array<string, list<ChangedLinesRange>> $changedLinesRangesByFilePathname
     */
    #[DataProvider('provideLines')]
    public function test_it_finds_line_in_changed_lines_from_diff(
        bool $expectedIsFound,
        array $changedLinesRangesByFilePathname,
        int $mutationStartLine,
        int $mutationEndLine,
    ): void {
        [$parser, $diffProvider] = $this->prepareServices($changedLinesRangesByFilePathname);

        $filesDiffChangedLines = new FilesDiffChangedLines(
            $parser,
            $diffProvider,
        );

        $isLineFoundInDiff = $filesDiffChangedLines->contains('/path/to/File.php', $mutationStartLine, $mutationEndLine, 'master');

        $this->assertSame($expectedIsFound, $isLineFoundInDiff, sprintf('Line %d was not found in diff', $mutationStartLine));
    }

    public static function provideLines(): Generator
    {
        yield 'not found line in one-line range before' => [
            false,
            [
                '/path/to/File.php' => [new ChangedLinesRange(3, 3)],
            ],
            1,
            1,
        ];

        yield 'not found line in one-line range after' => [
            false,
            [
                '/path/to/File.php' => [new ChangedLinesRange(3, 3)],
            ],
            5,
            5,
        ];

        yield 'line in one-line range' => [
            true,
            [
                '/path/to/File.php' => [new ChangedLinesRange(3, 3)],
            ],
            3,
            3,
        ];

        yield 'line in multi-line range in the beginning' => [
            true,
            [
                '/path/to/File.php' => [new ChangedLinesRange(3, 5)],
            ],
            3,
            3,
        ];

        yield 'line in multi-line range in the middle' => [
            true,
            [
                '/path/to/File.php' => [new ChangedLinesRange(1, 5)],
            ],
            3,
            3,
        ];

        yield 'line in multi-line range in the end' => [
            true,
            [
                '/path/to/File.php' => [new ChangedLinesRange(1, 3)],
            ],
            3,
            3,
        ];

        yield 'line in the second range' => [
            true,
            [
                '/path/to/File.php' => [new ChangedLinesRange(1, 1), new ChangedLinesRange(3, 5)],
            ],
            4,
            4,
        ];

        yield 'mutation range in one-line range, around' => [
            true,
            [
                '/path/to/File.php' => [new ChangedLinesRange(3, 3)],
            ],
            1,
            4,
        ];

        yield 'mutation range in one-line range, before' => [
            true,
            [
                '/path/to/File.php' => [new ChangedLinesRange(3, 3)],
            ],
            1,
            3,
        ];

        yield 'mutation range in one-line range, after' => [
            true,
            [
                '/path/to/File.php' => [new ChangedLinesRange(3, 3)],
            ],
            3,
            5,
        ];

        yield 'mutation range in one-line range, inside' => [
            true,
            [
                '/path/to/File.php' => [new ChangedLinesRange(1, 30)],
            ],
            3,
            5,
        ];
    }

    /**
     * @param array<string, list<ChangedLinesRange>> $changedLinesRangesByFilePathname
     *
     * @return array{DiffChangedLinesParser, Git}
     */
    private function prepareServices(array $changedLinesRangesByFilePathname): array
    {
        /** @var DiffChangedLinesParser&MockObject $parser */
        $parser = $this->createMock(DiffChangedLinesParser::class);
        $parser
            ->expects($this->once())
            ->method('parse')
            ->willReturn($changedLinesRangesByFilePathname);

        /** @var Git&MockObject $git */
        $git = $this->createMock(Git::class);
        $git
            ->expects($this->once())
            ->method('provideWithLines')
            ->with('master')
            ->willReturn('');

        return [$parser, $git];
    }
}
