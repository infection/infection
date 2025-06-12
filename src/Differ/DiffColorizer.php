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

use function array_filter;
use function array_map;
use function count;
use function explode;
use function implode;
use function mb_strlen;
use function mb_strpos;
use function mb_strrpos;
use function mb_substr;
use function Safe\preg_match_all;
use function sprintf;
use function str_starts_with;
use function substr;
use function substr_replace;
use Webmozart\Assert\Assert;

/**
 * @internal
 * @final
 */
class DiffColorizer
{
    public function colorize(string $diff): string
    {
        // fallback for cases when diff has multiple added new lines
        if ($this->isMultiLineDiff($diff)) {
            return $this->simpleMultilineColorize($diff);
        }

        $lines = explode("\n", $diff);

        foreach ($lines as $index => $line) {
            if (!str_starts_with($line, '+')) {
                continue;
            }

            Assert::greaterThan($index, 0);

            $prevIndex = $index - 1;
            $prevLine = $lines[$prevIndex];

            Assert::same($prevLine[0], '-');

            $lines[$prevIndex] = sprintf('<diff-del>-%s</diff-del>',
                $this->inlineDiff(substr($prevLine, 1), substr($line, 1), '<diff-del-inline>', '</diff-del-inline>'),
            );
            $lines[$index] = sprintf('<diff-add>+%s</diff-add>',
                $this->inlineDiff(substr($line, 1), substr($prevLine, 1), '<diff-add-inline>', '</diff-add-inline>'),
            );
        }

        return sprintf('<code>%s%s</code>', "\n", implode("\n", $lines));
    }

    private function inlineDiff(string $previousLine, string $nextLine, string $leftAddition, string $rightAddition): string
    {
        $previousLineLength = mb_strlen($previousLine);
        $nextLineLength = mb_strlen($nextLine);

        $start = $previousLineLength;

        while ($start !== 0 && mb_strpos($nextLine, mb_substr($previousLine, 0, $start)) !== 0) {
            --$start;
        }

        $end = $start;

        while ($end < $previousLineLength && mb_strrpos($nextLine, $t = mb_substr($previousLine, $end), $start) !== ($nextLineLength - mb_strlen($t))) {
            ++$end;
        }

        $return = $previousLine;

        if ($start < $end) {
            $return = substr_replace($return, $rightAddition, $end, 0);
            $return = substr_replace($return, $leftAddition, $start, 0);
        }

        return $return;
    }

    private function simpleMultilineColorize(string $diff): string
    {
        $lines = array_map(
            static function (string $line): string {
                if (str_starts_with($line, '-')) {
                    return sprintf('<diff-del>%s</diff-del>', $line);
                }

                if (str_starts_with($line, '+')) {
                    return sprintf('<diff-add>%s</diff-add>', $line);
                }

                return $line;
            },
            explode("\n", $diff),
        );

        return sprintf('<code>%s%s</code>', "\n", implode("\n", $lines));
    }

    private function isMultiLineDiff(string $diff): bool
    {
        preg_match_all('/(^\+.*$)|(^-.*$)/m', $diff, $matches);

        return count(array_filter($matches[1])) > 1
            || count(array_filter($matches[2])) > 1;
    }
}
