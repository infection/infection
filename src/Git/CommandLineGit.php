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

namespace Infection\Git;

use function array_map;
use function array_merge;
use function count;
use function explode;
use function implode;
use Infection\Differ\ChangedLinesRange;
use Infection\Process\ShellCommandLineExecutor;
use Infection\Source\Exception\NoSourceFound;
use InvalidArgumentException;
use const PHP_EOL;
use function Safe\preg_match;
use function Safe\preg_split;
use function sprintf;
use function str_starts_with;
use Symfony\Component\Process\Exception\ExceptionInterface as ProcessException;
use Webmozart\Assert\Assert;

/**
 * @internal
 *
 * Implementation of the Git contract leveraging the git binary via processes.
 */
final readonly class CommandLineGit implements Git
{
    // https://github.com/infection/infection/issues/2611
    private const DEFAULT_SYMBOLIC_REFERENCE = 'refs/remotes/origin/HEAD';

    private const DIFF_LINE_REGEX = '/diff.*a\/.*\sb\/(?<filePath>.*)/';

    private const DIFF_LINE_PATH_KEY = 'filePath';

    private const DIFF_LINE_RANGE_REGEX = '/\s\+(?<range>.*)\s@/';

    private const DIFF_LINE_RANGE_KEY = 'range';

    public function __construct(
        private ShellCommandLineExecutor $shellCommandLineExecutor,
    ) {
    }

    public function getDefaultBase(): string
    {
        return $this->readSymbolicReference(self::DEFAULT_SYMBOLIC_REFERENCE) ?? Git::FALLBACK_BASE;
    }

    public function getChangedFileRelativePaths(string $diffFilter, string $base, array $sourceDirectories): string
    {
        $filter = $this->shellCommandLineExecutor->execute(array_merge(
            [
                'git',
                'diff',
                $base,
                '--diff-filter=' . $diffFilter,
                '--name-only',
                '--',
            ],
            $sourceDirectories,
        ));

        if ($filter === '') {
            throw NoSourceFound::noFilesForGitDiff($diffFilter, $base);
        }

        return implode(',', explode(PHP_EOL, $filter));
    }

    public function getChangedLinesRangesByFileRelativePaths(string $diffFilter, string $base): array
    {
        return self::parsedChangedLines(
            $this->diffLines($diffFilter, $base),
        );
        return self::parsedChangedLines(
            $this->diffLines($diffFilter, $base),
        );
    }

    public function getBaseReference(string $base): string
    {
        try {
            return $this->shellCommandLineExecutor->execute([
                'git',
                'merge-base',
                $base,
                'HEAD',
            ]);
        } catch (ProcessException) {
            // TODO: could do some logging here...
        }

        // There is no common ancestor commit, or we are in a shallow checkout and do have a copy of it.
        // Fall back to direct diff.
        return $base;
    }

    /**
     * @return string[]
     */
    private function diffLines(string $diffFilter, string $base): array
    {
        $result = $this->shellCommandLineExecutor->execute([
        return preg_split('/\n|\r\n?/', $result);
    }

    /**
     * @param string[] $lines
     *
     * @return array<string, list<ChangedLinesRange>>
     */
    private static function parsedChangedLines(array $lines): array
    {
        $filePath = '';
        $result = [];

        foreach ($lines as $line) {
            if (str_starts_with($line, 'diff ')) {
                $filePath = self::parseFilePathFromLine($line);
            } elseif (str_starts_with($line, '@@ ')) {
                $changedLinesRange = self::parseChangedLinesRangeFromLine($line);

                if ($changedLinesRange !== null) {
                    $result[$filePath][] = $changedLinesRange;
                }
            }
        }

        // Do not use asser to avoid doing the imploding unless necessary.
        if ($filePath === '') {
            // TODO: throw an exception here.
            //   wait on https://github.com/infection/infection/pull/2648
            return [];

            throw new InvalidArgumentException(
                sprintf(
                    'Real path for file from diff can not be calculated. Diff: %s',
                    implode(PHP_EOL, $lines),
                ),
            );
        }

        return $result;
    }

    /**
     * @param string[] $lines
     *
     * @return array<string, list<ChangedLinesRange>>
     */
    private static function parsedChangedLines(array $lines): array
    {
        $filePath = '';
        $result = [];

        foreach ($lines as $line) {
            if (str_starts_with($line, 'diff ')) {
                $filePath = self::parseFilePathFromLine($line);
            } elseif (str_starts_with($line, '@@ ')) {
                $changedLinesRange = self::parseChangedLinesRangeFromLine($line);

                if ($changedLinesRange !== null) {
                    $result[$filePath][] = $changedLinesRange;
                }
            }
        }

        // Do not use asser to avoid doing the imploding unless necessary.
        if ($filePath === '') {
            if (count($resultMap) === 0) {
                throw NoSourceFound::noChangedLinesForGitDiff($diffFilter, $base, $diff);
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

            return new ChangedLinesRange($line, $line);
        }
        [$startLine, $newCount] = $lineParts;

        if ($newCount === 0) {
            return null;
        }

        $endLine = $startLine + $newCount - 1;

        return new ChangedLinesRange($startLine, $endLine);
    }

    private function readSymbolicReference(string $name): ?string
    {
        // see https://www.reddit.com/r/git/comments/jbdb7j/comment/lpdk30e/
        try {
            return $this->shellCommandLineExecutor->execute([
                'git',
                'symbolic-ref',
                $name,
            ]);
        } catch (ProcessException) {
            // e.g. no symbolic ref might be configured for a remote named "origin"
            // TODO: we could log the failure to figure it out somewhere...
        }

        return null;
    }

    /**
     * @return string[]
     */
    private function diffLines(string $diffFilter, string $base): array
    {
        $diff = $this->shellCommandLineExecutor->execute([
            'git',
            'diff',
            $base,
            '--unified=0',
            '--diff-filter=' . $diffFilter,
        ]);

        return preg_split('/\n|\r\n?/', $diff);
    }

    private function readSymbolicReference(string $name): ?string
    {
        // see https://www.reddit.com/r/git/comments/jbdb7j/comment/lpdk30e/
        try {
            return $this->shellCommandLineExecutor->execute([
                'git',
                'symbolic-ref',
                $name,
            ]);
        } catch (ProcessException) {
            // e.g. no symbolic ref might be configured for a remote named "origin"
            // TODO: we could log the failure to figure it out somewhere...
        }

        return null;
    }
}
