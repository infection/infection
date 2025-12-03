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

use function array_filter;
use function array_map;
use function array_merge;
use function count;
use function explode;
use function implode;
use Infection\Differ\ChangedLinesRange;
use Infection\Process\ShellCommandLineExecutor;
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

    private const MATCH_INDEX = 1;

    public function __construct(
        private ShellCommandLineExecutor $shellCommandLineExecutor,
    ) {
    }

    public function getDefaultBase(): string
    {
        // see https://www.reddit.com/r/git/comments/jbdb7j/comment/lpdk30e/
        try {
            return $this->shellCommandLineExecutor->execute([
                'git',
                'symbolic-ref',
                self::DEFAULT_SYMBOLIC_REFERENCE,
            ]);
        } catch (ProcessException) {
            // e.g. no symbolic ref might be configured for a remote named "origin"

            // TODO: we could log the failure to figure it out somewhere...

            // unable to figure it out, return the default
            return Git::FALLBACK_BASE;
        }
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
            throw NoFilesInDiffToMutate::create();
        }

        return implode(',', explode(PHP_EOL, $filter));
    }

    public function getChangedLinesRangesByFileRelativePaths(string $diffFilter, string $base): array
    {
        $filter = $this->shellCommandLineExecutor->execute([
            'git',
            'diff',
            $base,
            '--unified=0',
            '--diff-filter=' . $diffFilter,
        ]);

        $lines = explode(PHP_EOL, $filter);
        $lines = array_filter($lines, static fn (string $line): bool => preg_match('/^(\\+|-|index)/', $line) === 0);
        $linesWithoutIndex = implode(PHP_EOL, $lines);

        $splitLines = preg_split('/\n|\r\n?/', $linesWithoutIndex);

        $filePath = null;
        $resultMap = [];

        foreach ($splitLines as $line) {
            if (str_starts_with((string) $line, 'diff ')) {
                preg_match('/diff.*a\/.*\sb\/(.*)/', $line, $matches);

                Assert::keyExists(
                    $matches,
                    self::MATCH_INDEX,
                    sprintf('Source file can not be found in the following diff line: "%s"', $line),
                );

                $filePath = $matches[self::MATCH_INDEX];
            } elseif (str_starts_with((string) $line, '@@ ')) {
                Assert::string(
                    $filePath,
                    sprintf(
                        'Real path for file from diff can not be calculated. Diff: %s',
                        $linesWithoutIndex,
                    ),
                );

                preg_match('/\s\+(.*)\s@/', $line, $matches);

                Assert::keyExists(
                    $matches,
                    self::MATCH_INDEX,
                    sprintf(
                        'Added/modified lines can not be found in the following diff line: "%s"',
                        $line,
                    ),
                );

                // can be "523,12", meaning from 523 lines new 12 are added; or just "532"
                $linesText = $matches[self::MATCH_INDEX];

                $lineParts = array_map(intval(...), explode(',', $linesText));

                Assert::countBetween($lineParts, 1, 2);

                if (count($lineParts) === 1) {
                    [$line] = $lineParts;

                    $changedLinesRange = new ChangedLinesRange($line, $line);
                } else {
                    [$startLine, $newCount] = $lineParts;

                    if ($newCount === 0) {
                        continue;
                    }

                    $endLine = $startLine + $newCount - 1;

                    $changedLinesRange = new ChangedLinesRange($startLine, $endLine);
                }

                $resultMap[$filePath][] = $changedLinesRange;
            }
        }

        if (count($resultMap) === 0) {
            throw NoFilesInDiffToMutate::create();
        }

        return $resultMap;
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

        /**
         * there is no common ancestor commit, or we are in a shallow checkout and do have a copy of it.
         * Fall back to direct diff
         */
        return $base;
    }
}
