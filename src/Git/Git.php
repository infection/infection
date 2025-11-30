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
    public const FALLBACK_BASE_BRANCH = 'origin/master';

    /**
     * Retrieves the default base branch name for the repository.
     *
     * Examples of output:
     * - 'origin/main'
     * - 'origin/master'
     */
    public function getDefaultBaseBranch(): string;

    /**
     * Finds the list of relative paths (relative to the current working directory) of the changed files that changed
     * compared to the base branch used and matching the given filter.
     *
     * Returns a comma-separated list of the relative paths.
     *
     * @param string $diffFilter E.g. 'AM'.
     * @param string $baseBranch E.g. 'origin.main'.
     * @param string[] $sourceDirectories
     *
     * @throws NoFilesInDiffToMutate
     */
    public function getChangedFileRelativePaths(
        string $diffFilter,
        string $baseBranch,
        array $sourceDirectories,
    ): string;

    /**
     * Gets the modifications with their line numbers of the files that changed compared to the base branch used and
     * matching the given filter.
     */
    public function provideWithLines(string $baseBranch): string;
}
