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

use function array_map;
use Generator;
use Infection\Differ\ChangedLinesRange;
use Infection\Differ\DiffChangedLinesParser;
use PHPUnit\Framework\TestCase;
use function Safe\realpath;

/**
 * @group integration
 */
final class DiffChangedLinesParserTest extends TestCase
{
    /**
     * @dataProvider provideDiffs
     */
    public function test_it_converts_diff_to_files_and_changed_lines_map(string $diff, array $expectedMap): void
    {
        $collector = new DiffChangedLinesParser();

        $resultMap = $collector->parse($diff);

        $this->assertSame($this->convertToArray($expectedMap), $this->convertToArray($resultMap));
    }

    public static function provideDiffs(): Generator
    {
        yield 'one file with added lines in different places' => [
            <<<'DIFF'
                diff --git a/src/Container.php b/src/Container.php
                @@ -37,0 +38 @@ namespace Infection;
                @@ -533 +534,2 @@ final class Container
                @@ -535,0 +538,3 @@ final class Container
                @@ -1207,0 +1213,5 @@ final class Container
                DIFF,
            [
                realpath('src/Container.php') => [
                    new ChangedLinesRange(38, 38),
                    new ChangedLinesRange(534, 535),
                    new ChangedLinesRange(538, 540),
                    new ChangedLinesRange(1213, 1217),
                ],
            ],
        ];

        yield 'two files, second one is new created' => [
            <<<'DIFF'
                diff --git a/src/Container.php b/src/Container.php
                @@ -37,0 +38 @@ namespace Infection;
                @@ -533 +534,2 @@ final class Container
                @@ -535,0 +538,3 @@ final class Container
                @@ -1207,0 +1213,5 @@ final class Container
                diff --git a/src/Differ/FilesDiffChangedLines.php b/src/Differ/FilesDiffChangedLines.php
                new file mode 100644
                @@ -0,0 +1,18 @@
                DIFF,
            [
                realpath('src/Container.php') => [
                    new ChangedLinesRange(38, 38),
                    new ChangedLinesRange(534, 535),
                    new ChangedLinesRange(538, 540),
                    new ChangedLinesRange(1213, 1217),
                ],
                realpath('src/Differ/FilesDiffChangedLines.php') => [
                    new ChangedLinesRange(1, 18),
                ],
            ],
        ];
    }

    /**
     * @param array<string, array<int, ChangedLinesRange>> $map
     */
    private function convertToArray(array $map): array
    {
        $convertedMap = [];

        foreach ($map as $filePath => $changedLinesRanges) {
            $convertedMap[$filePath] = array_map(
                static function (ChangedLinesRange $changedLinesRange): array {
                    return [$changedLinesRange->getStartLine(), $changedLinesRange->getEndLine()];
                },
                $changedLinesRanges,
            );
        }

        return $convertedMap;
    }
}
