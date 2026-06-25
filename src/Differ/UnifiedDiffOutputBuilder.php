<?php
/**
 * This code is licensed under the BSD 3-Clause License.
 *
 * Copyright (c) 2002-2024, Sebastian Bergmann
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

use function array_splice;
use function count;
use function in_array;
use function is_array;
use function is_int;
use function is_string;
use function max;
use function min;
use function Safe\fclose;
use function Safe\fopen;
use function Safe\fwrite;
use function Safe\stream_get_contents;
use function substr;
use InvalidArgumentException;
use SebastianBergmann\Diff\Differ;
use SebastianBergmann\Diff\Output\DiffOutputBuilderInterface;

/**
 * Builds a diff string representation in unified diff format in chunks.
 *
 * @internal
 */
final readonly class UnifiedDiffOutputBuilder implements DiffOutputBuilderInterface
{
    private const int COMMON_LINE_THRESHOLD = 6;

    private const int CONTEXT_LINES = 3;

    private const int DIFF_ENTRY_SIZE = 2;

    /**
     * @param array<array-key, mixed> $diff
     */
    public function getDiff(array $diff): string
    {
        $diff = self::normalizeDiff($diff);
        $buffer = fopen('php://memory', 'r+b');

        if (0 !== count($diff)) {
            $this->writeDiffHunks($buffer, $diff);
        }

        $diff = stream_get_contents($buffer, -1, 0);

        fclose($buffer);

        // If the diff is non-empty and last char is not a linebreak: add it.
        // This might happen when both the `from` and `to` do not have a trailing linebreak
        $last = substr($diff, -1);

        return '' !== $diff && "\n" !== $last && "\r" !== $last
            ? $diff . "\n"
            : $diff;
    }

    /**
     * @param resource $output
     * @param list<array{string, Differ::*}> $diff
     */
    private function writeDiffHunks($output, array $diff): void
    {
        // detect "No newline at end of file" and insert into `$diff` if needed

        $upperLimit = count($diff);

        if (0 === $diff[$upperLimit - 1][1]) {
            $lc = substr($diff[$upperLimit - 1][0], -1);

            if ("\n" !== $lc) {
                array_splice($diff, $upperLimit, 0, [["\n\\ No newline at end of file\n", Differ::NO_LINE_END_EOF_WARNING]]);
            }
        } else {
            // search back for the last `+` and `-` line,
            // check if it has trailing linebreak, else add a warning under it
            $toFind = [
                Differ::ADDED => true,
                Differ::REMOVED => true,
            ];

            for ($i = $upperLimit - 1; $i >= 0; $i--) {
                $diffType = $diff[$i][1];

                if (isset($toFind[$diffType])) {
                    unset($toFind[$diffType]);
                    $lc = substr($diff[$i][0], -1);

                    if ("\n" !== $lc) {
                        array_splice($diff, $i + 1, 0, [["\n\\ No newline at end of file\n", Differ::NO_LINE_END_EOF_WARNING]]);
                    }

                    if (0 === count($toFind)) {
                        break;
                    }
                }
            }
        }

        // write hunks to output buffer

        $cutOff = max(self::COMMON_LINE_THRESHOLD, self::CONTEXT_LINES);
        $hunkCapture = false;
        $sameCount = $toRange = $fromRange = 0;

        $lastIndex = 0;

        foreach ($diff as $i => $entry) {
            $lastIndex = $i;

            if (0 === $entry[1]) { // same
                if (false === $hunkCapture) {
                    continue;
                }

                $sameCount++;
                $toRange++;
                $fromRange++;

                if ($sameCount === $cutOff) {
                    $contextStartOffset = ($hunkCapture - self::CONTEXT_LINES) < 0
                        ? $hunkCapture
                        : self::CONTEXT_LINES;

                    // note: $contextEndOffset = self::CONTEXT_LINES;
                    //
                    // because we never go beyond the end of the diff.
                    // with the cutoff/contextlines here the follow is never true;
                    //
                    // if ($i - $cutOff + self::CONTEXT_LINES + 1 > \count($diff)) {
                    //    $contextEndOffset = count($diff) - 1;
                    // }
                    //
                    // ; that would be true for a trailing incomplete hunk case which is dealt with after this loop

                    $this->writeHunk(
                        $diff,
                        $hunkCapture - $contextStartOffset,
                        $i - $cutOff + self::CONTEXT_LINES + 1,
                        $output,
                    );

                    $hunkCapture = false;
                    $sameCount = $toRange = $fromRange = 0;
                }

                continue;
            }

            $sameCount = 0;

            if ($entry[1] === Differ::NO_LINE_END_EOF_WARNING) {
                continue;
            }

            if (false === $hunkCapture) {
                $hunkCapture = $i;
            }

            if (Differ::ADDED === $entry[1]) {
                $toRange++;
            }

            if (Differ::REMOVED === $entry[1]) {
                $fromRange++;
            }
        }

        if (false === $hunkCapture) {
            return;
        }

        // we end here when cutoff (commonLineThreshold) was not reached, but we were capturing a hunk,
        // do not render hunk till end automatically because the number of context lines might be less than the commonLineThreshold

        $contextStartOffset = $hunkCapture - self::CONTEXT_LINES < 0
            ? $hunkCapture
            : self::CONTEXT_LINES;

        // prevent trying to write out more common lines than there are in the diff _and_
        // do not write more than configured through the context lines
        $contextEndOffset = min($sameCount, self::CONTEXT_LINES);

        $this->writeHunk(
            diff: $diff,
            diffStartIndex: $hunkCapture - $contextStartOffset,
            diffEndIndex: $lastIndex - $sameCount + $contextEndOffset + 1,
            output: $output,
        );
    }

    /**
     * @param array<array-key, mixed> $diff
     *
     * @return list<array{string, Differ::*}>
     */
    private static function normalizeDiff(array $diff): array
    {
        $normalizedDiff = [];
        $validDiffTypes = [
            Differ::OLD,
            Differ::ADDED,
            Differ::REMOVED,
            Differ::DIFF_LINE_END_WARNING,
            Differ::NO_LINE_END_EOF_WARNING,
        ];

        foreach ($diff as $entry) {
            if (!is_array($entry) || count($entry) !== self::DIFF_ENTRY_SIZE) {
                throw new InvalidArgumentException('Diff entries must be pairs of token and diff type.');
            }

            [$token, $diffType] = $entry;

            if (!is_string($token) || !is_int($diffType) || !in_array($diffType, $validDiffTypes, true)) {
                throw new InvalidArgumentException('Diff entries must be pairs of token and diff type.');
            }

            $normalizedDiff[] = [$token, $diffType];
        }

        return $normalizedDiff;
    }

    /**
     * @param list<array{string, Differ::*}> $diff
     * @param resource $output
     */
    private function writeHunk(
        array $diff,
        int $diffStartIndex,
        int $diffEndIndex,
        $output
    ): void {
        fwrite($output, "@@ @@\n");

        for ($i = $diffStartIndex; $i < $diffEndIndex; $i++) {
            if ($diff[$i][1] === Differ::ADDED) {
                fwrite($output, '+' . $diff[$i][0]);
            } elseif ($diff[$i][1] === Differ::REMOVED) {
                fwrite($output, '-' . $diff[$i][0]);
            } elseif ($diff[$i][1] === Differ::OLD) {
                fwrite($output, ' ' . $diff[$i][0]);
            } elseif ($diff[$i][1] === Differ::NO_LINE_END_EOF_WARNING) {
                fwrite($output, "\n"); // $diff[$i][0]
            } else { /* Not changed (old) Differ::OLD or Warning Differ::DIFF_LINE_END_WARNING */
                fwrite($output, ' ' . $diff[$i][0]);
            }
        }
    }
}
