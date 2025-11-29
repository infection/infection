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

namespace Infection\Logger\GitHub;

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
 * @final
 *
 * @internal
 */
class GitDiffFileProvider
{
    public const FALLBACK_BASE_BRANCH = 'origin/master';

    // A branch may have two forms:
    // - full path: refs/remotes/origin/HEAD
    // - shorthand: origin/HEAD
    //
    // The order that git uses to resolve a shorthand notation is defined here:
    // https://git-scm.com/docs/gitrevisions#Documentation/gitrevisions.txt-refnameegmasterheadsmasterrefsheadsmaster
    private const BRANCH_SHORTHAND_NOTATION_PART_COUNT = 2;

    // https://github.com/infection/infection/issues/2611
    private const DEFAULT_SYMBOLIC_REFERENCE = 'refs/remotes/origin/HEAD';

    private ?string $defaultBase = null;

    public function __construct(
        private readonly ShellCommandLineExecutor $shellCommandLineExecutor,
    ) {
    }

    /**
     * Retrieves the default base branch name for the repository.
     *
     * Examples of output:
     * - 'origin/main'
     * - 'origin/master'
     */
    public function getDefaultBaseBranch(): string
    {
        if ($this->defaultBase !== null) {
            return $this->defaultBase;
        }

        // see https://www.reddit.com/r/git/comments/jbdb7j/comment/lpdk30e/
        try {
            $reference = $this->shellCommandLineExecutor->execute([
                'git',
                'symbolic-ref',
                self::DEFAULT_SYMBOLIC_REFERENCE,
            ]);

            $parts = explode('/', $reference);

            if (count($parts) > self::BRANCH_SHORTHAND_NOTATION_PART_COUNT) {
                // extract origin/branch from a string like 'refs/remotes/origin/master'
                return $this->defaultBase = implode('/', array_slice($parts, -self::BRANCH_SHORTHAND_NOTATION_PART_COUNT));
            }
        } catch (RuntimeException) {
            // e.g. no symbolic ref might be configured for a remote named "origin"
            // TODO: we could log the failure to figure it out somewhere...
        }

        // unable to figure it out, return the default
        return $this->defaultBase = self::FALLBACK_BASE_BRANCH;
    }

    /**
     * @param string[] $sourceDirectories
     */
    public function provide(string $gitDiffFilter, string $gitDiffBase, array $sourceDirectories): string
    {
        $referenceCommit = $this->findReferenceCommit($gitDiffBase);

        $filter = $this->shellCommandLineExecutor->execute(array_merge(
            [
                'git',
                'diff',
                $referenceCommit,
                '--diff-filter',
                $gitDiffFilter,
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

    public function provideWithLines(string $gitDiffBase): string
    {
        $referenceCommit = $this->findReferenceCommit($gitDiffBase);

        $filter = $this->shellCommandLineExecutor->execute([
            'git',
            'diff',
            $referenceCommit,
            '--unified=0',
            '--diff-filter=AM',
        ]);
        $lines = explode(PHP_EOL, $filter);
        $lines = array_filter($lines, static fn (string $line): bool => preg_match('/^(\\+|-|index)/', $line) === 0);

        return implode(PHP_EOL, $lines);
    }

    private function findReferenceCommit(string $gitDiffBase): string
    {
        try {
            $comparisonCommit = $this->shellCommandLineExecutor->execute([
                'git',
                'merge-base',
                $gitDiffBase,
                'HEAD',
            ]);
        } catch (ProcessFailedException) {
            /**
             * there is no common ancestor commit, or we are in a shallow checkout and do have a copy of it.
             * Fall back to direct diff
             */
            $comparisonCommit = $gitDiffBase;
        }

        return $comparisonCommit;
    }
}
