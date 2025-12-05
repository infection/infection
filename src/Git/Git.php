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

use Infection\Differ\ChangedLinesRange;
use Infection\Source\Exception\NoSourceFound;

/**
 * @internal
 *
 * Defines the contract for interacting with Git.
 *
 * This interface abstracts away the underlying Git implementation, whether it is spawning
 * git processes, using a native library or mocking.
 *
 * This aims at highlighting what API we use from git and allow the code to be more expressive and usable.
 */
interface Git
{
    // The default git base used. It can be a short branch name, full name or a
    // commit reference.
    public const FALLBACK_BASE = 'origin/master';

    public const DEFAULT_GIT_DIFF_FILTER = 'AM';

    /**
     * Retrieves the default base branch name for the repository.
     *
     * Examples of output:
     * - 'refs/remotes/origin/main'
     * - 'origin/main'
     * - 'origin/master'
     *
     * A branch may have two forms:
     * - full path: refs/remotes/origin/HEAD
     * - shorthand: origin/HEAD
     *
     * The order that git uses to resolve a shorthand notation is defined here:
     * https://git-scm.com/docs/gitrevisions#Documentation/gitrevisions.txt-refnameegmasterheadsmasterrefsheadsmaster
     *
     * Preferably, this method returns the full path which is less ambiguous. However, this is not always possible.
     *
     * @return non-empty-string
     */
    public function getDefaultBase(): string;

    /**
     * Finds the list of relative paths (relative to the current working directory) of the changed files that changed
     * compared to the base branch used and matching the given filter.
     *
     * Returns a comma-separated list of the relative paths.
     *
     * @param non-empty-string $diffFilter E.g. 'AM'.
     * @param non-empty-string $base E.g. 'origin/main' or a commit hash.
     * @param non-empty-string[] $sourceDirectories
     *
     * @throws NoSourceFound
     *
     * @return non-empty-string
     */
    public function getChangedFileRelativePaths(
        string $diffFilter,
        string $base,
        array $sourceDirectories,
    ): string;

    /**
     * Gets the modifications with their line numbers of the files that changed compared to the base branch used and
     * matching the given filter.
     *
     * Returned result example:
     *
     * ```php
     * [
     *     src/File1.php => [ChangedLinesRange(1, 2)],
     *     src/File2.php => [ChangedLinesRange(1, 20), ChangedLinesRange(33, 33)],
     * ]
     * ```
     *
     * @param non-empty-string $diffFilter E.g. 'AM'.
     * @param non-empty-string $base E.g. 'origin/main' or a commit hash.
     * @param non-empty-string[] $sourceDirectories
     *
     * @throws NoSourceFound
     *
     * @return non-empty-array<string, list<ChangedLinesRange>>
     */
    public function getChangedLinesRangesByFileRelativePaths(
        string $diffFilter,
        string $base,
        array $sourceDirectories,
    ): array;

    /**
     * Find as good common ancestors as possible with HEAD for a merge and falls back to the given base otherwise.
     *
     * Returns either the commit hash, e.g. '8af25a159143aadacf4d875a3114014e99053430' or the fallback value.
     *
     * @param non-empty-string $base
     *
     * @return non-empty-string
     */
    public function getBaseReference(string $base): string;
}
