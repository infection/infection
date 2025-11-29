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
use function array_merge;
use function array_slice;
use function count;
use function explode;
use function implode;
use Infection\Process\ShellCommandLineExecutor;
use const PHP_EOL;
use RuntimeException;
use function Safe\preg_match;
use Symfony\Component\Process\Exception\ProcessFailedException;

/**
 * @internal
 *
 * Implementation of the Git contract leveraging the git binary via processes
 */
final readonly class CommandLineGit implements Git
{
    private const BRANCH_NAME_PART_COUNT = 2;

    // https://github.com/infection/infection/issues/2611
    private const DEFAULT_SYMBOLIC_REFERENCE = 'refs/remotes/origin/HEAD';

    public function __construct(
        private ShellCommandLineExecutor $shellCommandLineExecutor,
    ) {
    }

    public function getDefaultBaseBranch(): string
    {
        $reference = $this->readSymbolicReference(self::DEFAULT_SYMBOLIC_REFERENCE);
        // TODO:
        //  - check when can a reference have more parts
        //  - check what is the term of 'origin/main' in 'refs/remotes/origin/main'
        $parts = explode('/', $reference ?? '');

        if (count($parts) > self::BRANCH_NAME_PART_COUNT) {
            return implode(
                '/',
                array_slice($parts, -self::BRANCH_NAME_PART_COUNT),
            );
        }

        // TODO: we could log the failure to figure it out somewhere...

        // Unable to figure it out, return the default
        return Git::FALLBACK_BASE_BRANCH;
    }

    public function getChangedFileRelativePaths(
        string $diffFilter,
        string $baseBranch,
        array $sourceDirectories,
    ): array {
        $referenceCommit = $this->findReferenceCommit($baseBranch);

        return $this->diffFileRelativePaths(
            $referenceCommit,
            $diffFilter,
            $sourceDirectories,
        );
    }

    public function diffLines(string $baseBranch): string
    {
        $referenceCommit = $this->findReferenceCommit($baseBranch);

        $output = $this->shellCommandLineExecutor->execute([
            'git',
            'diff',
            $referenceCommit,
            '--unified=0',  // Remove any lines of context, only the modified lines are shown.
            // TODO: isn't it weird that the filter is different here?
            '--diff-filter=AM',
        ]);

        // TODO: this could be greatly simplified by not doing the array filter and instead
        //  provide the output of DiffChangedLinesParser::parse() directly here...
        //  The current implementation of this method could remain ::diffLines() and stay
        //  as a private method whilst the current public method would be renamed to a
        //  more meaningful name.
        $lines = explode(PHP_EOL, $output);
        $lines = array_filter($lines, static fn (string $line): bool => preg_match('/^(\\+|-|index)/', $line) === 0);

        return implode(PHP_EOL, $lines);
    }

    // TODO: review the example here; clarify why is refs/heads/master not valid for example although it is the one mentioned
    //   in the git docs.
    // TODO: add link to the git docs
    /**
     * Reads which branch head the given symbolic reference refers to and outputs its path, relative to the `.git/`
     * directory.
     *
     * A symbolic ref is a regular file that stores a string that begins with ref: `refs/.` For example, your
     * `.git/HEAD` is a regular file whose content is ref: `refs/heads/main`.
     *
     * Example of input:
     * - 'refs/remotes/origin/HEAD'
     *
     * Example of output:
     * - 'refs/remotes/origin/main'
     * - 'refs/remotes/origin/master'
     */
    private function readSymbolicReference(string $name): ?string
    {
        // see https://www.reddit.com/r/git/comments/jbdb7j/comment/lpdk30e/
        try {
            return $this->shellCommandLineExecutor->execute([
                'git',
                'symbolic-ref',
                $name,
            ]);
        } catch (RuntimeException) {
            // e.g. no symbolic ref might be configured for a remote named "origin"
            return null;
        }
    }

    /**
     * Give the list of changed files (relative paths) matching the filter. For example:
     *
     * ```
     * ['src/Git.php', 'src/CommandLineGit']
     * ```
     *
     * @param string $commit commit in the broad term, can be a branch name, a tag or commit hash
     *
     * @param string[] $paths
     */
    private function diffFileRelativePaths(
        string $commit,
        string $diffFilter,
        array $paths,
    ): array {
        $output = $this->shellCommandLineExecutor->execute(
            array_merge(
                [
                    'git',
                    'diff',
                    $commit,
                    '--diff-filter',
                    $diffFilter,
                    '--name-only',
                    '--',
                ],
                $paths,
            ),
        );

        return explode(PHP_EOL, $output);
    }

    /**
     * TODO: check if that should be part of the public API and/or cached. Maybe that should be moved to the gitSource/Configuration.
     * TODO: review the doc
     * TODO: review the name; if naming it mergeBase or differently based on the fact that it does mergeBase(commit, HEAD)
     *
     * Find as good common ancestors as possible for a merge.
     *
     * @return non-empty-string Commit hash, e.g. '8af25a159143aadacf4d875a3114014e99053430'.
     */
    private function findReferenceCommit(string $commit): string
    {
        try {
            return $this->shellCommandLineExecutor->execute([
                'git',
                'merge-base',
                $commit,
                'HEAD',
            ]);
        } catch (ProcessFailedException) {
            // There is no common ancestor commit, or we are in a shallow checkout and do have a copy of it. Fall back
            // to direct diff.
            // TODO: may be worth logging...
            return $commit;
        }
    }
}
