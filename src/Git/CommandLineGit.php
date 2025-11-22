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

use function array_slice;
use function count;
use function explode;
use function implode;
use Infection\Process\ShellCommandLineExecutor;
use RuntimeException;

final readonly class CommandLineGit implements Git
{
    private const NUM_ORIGIN_AND_BRANCH_PARTS = 2;

    // TODO: maybe the default base could be configured in the config file?
    private const DEFAULT_BASE = 'origin/master';

    public function __construct(
        private ShellCommandLineExecutor $shellCommandLineExecutor,
    ) {
    }

    public function getDefaultBase(): string
    {
        // see https://www.reddit.com/r/git/comments/jbdb7j/comment/lpdk30e/
        try {
            $gitRefs = $this->shellCommandLineExecutor->execute([
                'git',
                'symbolic-ref',
                'refs/remotes/origin/HEAD',
            ]);

            $parts = explode('/', $gitRefs);

            if (count($parts) > self::NUM_ORIGIN_AND_BRANCH_PARTS) {
                // extract origin/branch from a string like 'refs/remotes/origin/master'
                return implode(
                    '/',
                    array_slice($parts, -self::NUM_ORIGIN_AND_BRANCH_PARTS),
                );
            }
        } catch (RuntimeException) {
            // e.g. no symbolic ref might be configured for a remote named "origin"
        }

        // unable to figure it out, return the default
        return self::DEFAULT_BASE;
    }

    public function getDefaultBaseFilter(): string
    {
        return 'AM';
    }
}
