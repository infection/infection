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

namespace Infection\Tests\TestingUtility;

use Infection\Process\SymfonyProcessShellCommandRunner;
use Webmozart\Assert\Assert;

trait GitRepositorySetup
{
    private static function initializeGitRepository(string $directory): void
    {
        self::runGit(
            '.',
            [
                'init',
                '--quiet',
                '--initial-branch=main',
                $directory,
            ],
        );
    }

    /**
     * @param non-empty-string $message
     *
     * @return non-empty-string
     */
    private static function commitGitChanges(
        string $directory,
        string $message,
    ): string {
        self::runGit(
            $directory,
            [
                'add',
                '.',
            ],
        );
        self::runGit(
            $directory,
            [
                'commit',
                '--quiet',
                '--no-gpg-sign',
                '--message',
                $message,
            ],
        );

        $commit = self::runGit(
            $directory,
            [
                'rev-parse',
                'HEAD',
            ],
        );

        Assert::stringNotEmpty(
            $commit,
            'The fixture repository must have a HEAD commit after committing.',
        );

        return $commit;
    }

    /**
     * @param non-empty-list<string> $arguments
     */
    private static function runGit(
        string $directory,
        array $arguments,
    ): string {
        // Git has no long equivalents for -C and -c. Disable hooks for every
        // command, including hooks installed in the fixture repository itself.
        return (new SymfonyProcessShellCommandRunner())->mustRun(
            [
                'git',
                '--no-pager',
                '-C',
                $directory,
                '-c',
                'core.hooksPath=/dev/null',
                ...$arguments,
            ],
            env: [
                'GIT_CONFIG_NOSYSTEM' => '1',
                'GIT_CONFIG_GLOBAL' => '/dev/null',
                'GIT_CONFIG_COUNT' => '0',
                'GIT_CONFIG_PARAMETERS' => false,
                'GIT_TEMPLATE_DIR' => '',
                'GIT_AUTHOR_NAME' => 'Infection',
                'GIT_AUTHOR_EMAIL' => 'infection@infection.github.io',
                'GIT_COMMITTER_NAME' => 'Infection',
                'GIT_COMMITTER_EMAIL' => 'infection@infection.github.io',
            ],
        );
    }
}
