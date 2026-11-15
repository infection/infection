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

namespace Infection\Tests\Source\MatcherLine;

use const DIRECTORY_SEPARATOR;
use Infection\Differ\ChangedLinesRange;
use Infection\Source\MatcherLine\ChangedLinesSourceLineMatcher;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use function str_replace;

#[CoversClass(ChangedLinesSourceLineMatcher::class)]
final class ChangedLinesSourceLineMatcherTest extends TestCase
{
    /**
     * @param array<string, list<ChangedLinesRange>> $changedLinesRangesByFilePath
     * @param positive-int $mutationStartLine
     * @param positive-int $mutationEndLine
     */
    #[DataProvider('linesProvider')]
    public function test_it_tells_if_the_mutation_touches_any_of_the_changed_lines(
        array $changedLinesRangesByFilePath,
        string $fileRealPath,
        int $mutationStartLine,
        int $mutationEndLine,
        bool $expected,
    ): void {
        $matcher = new ChangedLinesSourceLineMatcher($changedLinesRangesByFilePath);

        $actual = $matcher->touches($fileRealPath, $mutationStartLine, $mutationEndLine);

        $this->assertSame($expected, $actual);
    }

    public static function linesProvider(): iterable
    {
        yield 'the mutation touches no changed line' => [
            [
                '/path/to/src/File.php' => [ChangedLinesRange::forLine(3)],
            ],
            '/path/to/src/File.php',
            1,
            1,
            false,
        ];

        yield 'the mutation touches a changed line' => [
            [
                '/path/to/src/File.php' => [ChangedLinesRange::forLine(3)],
            ],
            '/path/to/src/File.php',
            2,
            5,
            true,
        ];

        yield 'the mutation touches one of the changed lines' => [
            [
                '/path/to/src/File.php' => [
                    ChangedLinesRange::forLine(10),
                    ChangedLinesRange::create(30, 50),
                ],
            ],
            '/path/to/src/File.php',
            40,
            41,
            true,
        ];

        yield 'the mutation is in a file without changed lines' => [
            [
                '/path/to/src/File1.php' => [ChangedLinesRange::forLine(10)],
            ],
            '/path/to/src/File2.php',
            10,
            10,
            false,
        ];

        yield 'the Windows real path is normalized before looking up changed lines' => [
            [
                'C:/path/to/src/File.php' => [ChangedLinesRange::forLine(3)],
            ],
            str_replace('/', DIRECTORY_SEPARATOR, 'C:/path/to/src/File.php'),
            3,
            3,
            true,
        ];
    }
}
