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
use function explode;
use function implode;
use Infection\Process\ShellCommandLineExecutor;
use const PHP_EOL;
use function Safe\preg_match;
use Symfony\Component\Process\Exception\ProcessFailedException;

/**
 * @final
 *
 * @internal
 */
class GitDiffFileProvider
{
    final public const DEFAULT_BASE = 'origin/master';

    public function __construct(private readonly ShellCommandLineExecutor $shellCommandLineExecutor)
    {
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
        $lines = array_filter($lines, static fn ($line): bool => preg_match('/^(\\+|-|index)/', $line) === 0);

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
