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

namespace Infection\Differ;

use function array_map;
use function count;
use function explode;
use Infection\CannotBeInstantiated;
use function Safe\preg_match;
use function sprintf;
use function str_starts_with;
use Symfony\Component\Filesystem\Path;
use Webmozart\Assert\Assert;

/**
 * Reads the lines a unified diff adds or modifies, e.g. the output of "git diff --unified=0".
 *
 * @internal
 */
final class ChangedLinesParser
{
    use CannotBeInstantiated;

    private const string DIFF_LINE_REGEX = '/diff.*a\/.*\sb\/(?<filePath>.*)/';

    private const string DIFF_LINE_PATH_KEY = 'filePath';

    private const string DIFF_LINE_RANGE_REGEX = '/\s\+(?<range>.*)\s@/';

    private const string DIFF_LINE_RANGE_KEY = 'range';

    /**
     * @param string[] $lines
     * @param string $baseDirectory Directory the paths of the diff are relative to.
     *
     * @return array<string, list<ChangedLinesRange>> The changed lines ranges by absolute file path.
     */
    public static function parse(array $lines, string $baseDirectory): array
    {
        $filePath = '';
        $result = [];

        foreach ($lines as $line) {
            if (str_starts_with($line, 'diff ')) {
                $filePath = self::parseFilePathFromLine($line);
            } elseif (str_starts_with($line, '@@ ')) {
                $changedLinesRange = self::parseChangedLinesRangeFromLine($line);

                if ($changedLinesRange !== null) {
                    $result[Path::join($baseDirectory, $filePath)][] = $changedLinesRange;
                }
            }
        }

        return $result;
    }

    private static function parseFilePathFromLine(string $line): string
    {
        preg_match(self::DIFF_LINE_REGEX, $line, $matches);

        Assert::keyExists(
            $matches,
            self::DIFF_LINE_PATH_KEY,
            sprintf(
                'Source file can not be found in the following diff line: "%s"',
                $line,
            ),
        );

        return $matches[self::DIFF_LINE_PATH_KEY];
    }

    /**
     * Examples of possible forms for the input line:
     *
     * - '@@ -10,5 +12,7 @@ ...': lines added and removed, here 5 lines removed at L10 in the old file and 7 lines added from L12 in the new file
     * - '@@ -10,0 +11,5 @@ ...': only lines added, 0 lines from the old file at L10, 5 lines added starting at L11 in new file
     *
     * Check the test for more examples.
     */
    private static function parseChangedLinesRangeFromLine(string $line): ?ChangedLinesRange
    {
        preg_match(self::DIFF_LINE_RANGE_REGEX, $line, $matches);

        Assert::keyExists(
            $matches,
            self::DIFF_LINE_RANGE_KEY,
            sprintf(
                'Added/modified lines can not be found in the following diff line: "%s"',
                $line,
            ),
        );

        $range = $matches[self::DIFF_LINE_RANGE_KEY];

        $lineParts = array_map(
            intval(...),
            explode(',', $range),
        );

        Assert::countBetween($lineParts, 1, 2);

        if (count($lineParts) === 1) {
            [$line] = $lineParts;
            Assert::natural($line);

            return ChangedLinesRange::forLine($line);
        }

        [$startLine, $newCount] = $lineParts;
        Assert::natural($startLine);
        Assert::natural($newCount);

        if ($newCount === 0) {
            return null;
        }

        return ChangedLinesRange::forRange($startLine, $newCount);
    }
}
