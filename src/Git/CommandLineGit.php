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
use function Safe\realpath;
use function sprintf;
use function str_starts_with;
use Symfony\Component\Process\Exception\ExceptionInterface as ProcessException;
use Webmozart\Assert\Assert;

/**
 * @internal
 *
 * Implementation of the Git contract leveraging the git binary via processes.
 */
final class CommandLineGit implements Git
{
    // https://github.com/infection/infection/issues/2611
    private const DEFAULT_SYMBOLIC_REFERENCE = 'refs/remotes/origin/HEAD';

    private const DIFF_FILE_PATH_PATTERN = '/diff.*a\/.*\sb\/(.*)$/';
    private const DIFF_LINE_RANGE_PATTERN = '/\s\+(.*)\s@/';

    private ?string $defaultBase = null;

    public function __construct(
        private readonly ShellCommandLineExecutor $shellCommandLineExecutor,
    ) {
    }

    public function getDefaultBase(): string
    {
        if ($this->defaultBase !== null) {
            return $this->defaultBase;
        }

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
        }

        // unable to figure it out, return the default
        return $this->defaultBase = Git::FALLBACK_BASE;
    }

    public function getChangedFileRelativePaths(string $diffFilter, string $base, array $sourceDirectories): string
    {
        $filter = $this->shellCommandLineExecutor->execute(array_merge(
            [
                'git',
                'diff',
                $base,
                '--diff-filter',
                $diffFilter,
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

    public function provideWithLines(string $base): array
    {
        $diff = $this->shellCommandLineExecutor->execute([
            'git',
            'diff',
            $base,
            '--unified=0',
            '--diff-filter=AM',
        ]);

        $diffLines = explode(PHP_EOL, $diff);
        $currentFilePath = null;
        $changedLinesMap = [];

        foreach ($diffLines as $line) {
            if (str_starts_with($line, 'diff ')) {
                $currentFilePath = $this->parseFilePathFromDiffLine($line, $diff);
            } elseif (str_starts_with($line, '@@ ')) {
                Assert::string(
                    $currentFilePath,
                    sprintf('Real path for file from diff can not be calculated. Diff: %s', $diff),
                );

                $changedLinesMap[$currentFilePath][] = $this->parseChangedLinesRange($line);
            }
        }

        return $changedLinesMap;
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

    private function parseFilePathFromDiffLine(string $diffLine, string $fullDiff): string
    {
        preg_match(self::DIFF_FILE_PATH_PATTERN, $diffLine, $matches);

        Assert::keyExists(
            $matches,
            1,
            sprintf('Source file can not be found in the following diff line: "%s"', $diffLine),
        );

        return realpath($matches[1]);
    }

    private function parseChangedLinesRange(string $rangeLine): ChangedLinesRange
    {
        preg_match(self::DIFF_LINE_RANGE_PATTERN, $rangeLine, $matches);

        Assert::keyExists(
            $matches,
            1,
            sprintf('Added/modified lines can not be found in the following diff line: "%s"', $rangeLine),
        );

        $linesText = $matches[1];
        $lineParts = array_map('\intval', explode(',', $linesText));

        Assert::minCount($lineParts, 1);

        $startLine = $lineParts[0];
        $endLine = count($lineParts) > 1 ? $lineParts[0] + $lineParts[1] - 1 : $startLine;

        return new ChangedLinesRange($startLine, $endLine);
    }
}
