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

use function escapeshellarg;
use Infection\Process\ShellCommandLineExecutor;
use function Safe\sprintf;
use Symfony\Component\Process\Exception\ProcessFailedException;
use function trim;

/**
 * @final
 *
 * @internal
 */
class GitDiffFileProvider
{
    public const DEFAULT_BASE = 'origin/master';

    private ShellCommandLineExecutor $shellCommandLineExecutor;

    public function __construct(ShellCommandLineExecutor $shellCommandLineExecutor)
    {
        $this->shellCommandLineExecutor = $shellCommandLineExecutor;
    }

    public function provide(string $gitDiffFilter, string $gitDiffBase): string
    {
        $referenceCommit = $this->findReferenceCommit($gitDiffBase);

        $filter = $this->shellCommandLineExecutor->execute(sprintf(
            'git diff %s --diff-filter=%s --name-only | grep src/ | paste -s -d "," -',
            escapeshellarg($referenceCommit),
            escapeshellarg($gitDiffFilter)
        ));

        if ($filter === '') {
            throw NoFilesInDiffToMutate::create();
        }

        return $filter;
    }

    public function provideWithLines(string $gitDiffBase): string
    {
        $referenceCommit = $this->findReferenceCommit($gitDiffBase);

        return $this->shellCommandLineExecutor->execute(sprintf(
            "git diff %s --unified=0 --diff-filter=AM | grep -v -e '^[+-]' -e '^index'",
            escapeshellarg($referenceCommit)
        ));
    }

    private function findReferenceCommit(string $gitDiffBase): string
    {
        try {
            $comparisonCommit = trim($this->shellCommandLineExecutor->execute(sprintf(
                    'git merge-base %s HEAD',
                    escapeshellarg($gitDiffBase))
                )
            );
        } catch (ProcessFailedException $_e) {
            /**
             * there is no common ancestor commit, or we are in a shallow checkout and do have a copy of it.
             * Fall back to direct diff
             */
            $comparisonCommit = $gitDiffBase;
        }

        return $comparisonCommit;
    }
}
