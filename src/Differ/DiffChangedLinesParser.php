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
use function Safe\preg_match;
use function Safe\preg_split;
use function Safe\realpath;
use function sprintf;
use function str_starts_with;
use Webmozart\Assert\Assert;

/**
 * @internal
 * @final
 */
class DiffChangedLinesParser
{
    private const MATCH_INDEX = 1;

    /**
     * Returned result example:
     *   [
     *       src/File1.php => [ChangedLinesRange(1, 2)]
     *       src/File2.php => [ChangedLinesRange(1, 20), ChangedLinesRange(33, 33),]
     *   ]
     *
     * Diff provided by command line: `git diff --unified=0 --diff-filter=AM master | grep -v -e '^[+-]' -e '^index'`
     *
     * @return array<string, array<int, ChangedLinesRange>>
     */
    public function parse(string $unifiedGreppedDiff): array
    {
        $lines = preg_split('/\n|\r\n?/', $unifiedGreppedDiff);

        $filePath = null;

        $resultMap = [];

        foreach ($lines as $line) {
            if (str_starts_with((string) $line, 'diff ')) {
                preg_match('/diff.*a\/.*\sb\/(.*)/', $line, $matches);

                Assert::keyExists(
                    $matches,
                    self::MATCH_INDEX,
                    sprintf('Source file can not be found in the following diff line: "%s"', $line),
                );

                $filePath = realpath($matches[self::MATCH_INDEX]);
            } elseif (str_starts_with((string) $line, '@@ ')) {
                Assert::string($filePath, sprintf('Real path for file from diff can not be calculated. Diff: %s', $unifiedGreppedDiff));

                preg_match('/\s\+(.*)\s@/', $line, $matches);

                Assert::keyExists(
                    $matches,
                    self::MATCH_INDEX,
                    sprintf('Added/modified lines can not be found in the following diff line: "%s"', $line),
                );

                // can be "523,12", meaning from 523 lines new 12 are added; or just "532"
                $linesText = $matches[self::MATCH_INDEX];

                $lineParts = array_map('\intval', explode(',', $linesText));

                Assert::minCount($lineParts, 1);

                $startLine = $lineParts[0];
                $endLine = count($lineParts) > 1 ? $lineParts[0] + $lineParts[1] - 1 : $startLine;

                $resultMap[$filePath][] = new ChangedLinesRange($startLine, $endLine);
            }
        }

        return $resultMap;
    }
}
