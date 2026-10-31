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

use Infection\Tests\FileSystem\FileSystemTestCase;
use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RequiresOperatingSystem;
use PHPUnit\Framework\Attributes\WithEnvironmentVariable;
use function Safe\chmod;
use function Safe\file_get_contents;
use function Safe\putenv;

/**
 * Windows is excluded because Symfony 6.4 cannot reliably remove Git's read-only
 * object files when cleaning up these repositories.
 */
#[Group('integration')]
#[CoversTrait(GitRepositorySetup::class)]
#[RequiresOperatingSystem('Linux|Darwin')]
#[WithEnvironmentVariable('GIT_AUTHOR_NAME', 'Another author')]
#[WithEnvironmentVariable('GIT_AUTHOR_EMAIL', 'author@example.com')]
#[WithEnvironmentVariable('GIT_COMMITTER_NAME', 'Another committer')]
#[WithEnvironmentVariable('GIT_COMMITTER_EMAIL', 'committer@example.com')]
final class GitRepositorySetupTest extends FileSystemTestCase
{
    use GitRepositorySetup;

    #[WithEnvironmentVariable('GIT_CONFIG_SYSTEM')]
    #[WithEnvironmentVariable('GIT_CONFIG_NOSYSTEM', '0')]
    #[WithEnvironmentVariable('GIT_CONFIG_GLOBAL')]
    #[WithEnvironmentVariable('GIT_CONFIG_COUNT', '1')]
    #[WithEnvironmentVariable('GIT_CONFIG_KEY_0', 'invalid')]
    #[WithEnvironmentVariable('GIT_CONFIG_VALUE_0', 'invalid')]
    #[WithEnvironmentVariable('GIT_CONFIG_PARAMETERS', 'invalid')]
    #[WithEnvironmentVariable('GIT_TEMPLATE_DIR')]
    public function test_it_ignores_inherited_configuration_and_templates(): void
    {
        $config = $this->tmp . '/invalid-gitconfig';
        $templates = $this->tmp . '/templates';
        $repository = $this->tmp . '/repository';
        FS::dumpFile(
            $config,
            '[invalid configuration',
        );
        FS::dumpFile(
            $templates . '/template-marker',
            'This template must not be copied into the fixture repository.',
        );
        putenv('GIT_CONFIG_SYSTEM=' . $config);
        putenv('GIT_CONFIG_GLOBAL=' . $config);
        putenv('GIT_TEMPLATE_DIR=' . $templates);

        self::initializeGitRepository($repository);
        FS::dumpFile($repository . '/fixture.php', '<?php return 1;');
        self::commitGitChanges($repository, 'Fixture commit');

        $this->assertFileDoesNotExist($repository . '/.git/template-marker');
        $this->assertSame(
            'Infection|infection@infection.github.io|Infection|infection@infection.github.io',
            self::runGit(
                $repository,
                [
                    'log',
                    '--max-count=1',
                    '--format=%an|%ae|%cn|%ce',
                ],
            ),
        );
    }

    #[DataProvider('signingFormatsProvider')]
    public function test_it_disables_repository_signing_and_hooks(string $signingFormat): void
    {
        $repository = $this->tmp . '/repository';
        $hooks = $this->tmp . '/hooks';

        self::initializeGitRepository($repository);

        FS::dumpFile($hooks . '/pre-commit', "#!/bin/sh\nexit 1\n");
        chmod($hooks . '/pre-commit', 0755);

        FS::dumpFile(
            $repository . '/.git/config',
            file_get_contents($repository . '/.git/config') . <<<CONFIG

                [core]
                    hooksPath = "{$hooks}"
                [user]
                    name = Another user
                    email = user@example.com
                    signingKey = unavailable-key
                [commit]
                    gpgSign = true
                [gpg]
                    format = {$signingFormat}
                    program = "{$this->tmp}/unavailable-gpg-program"
                [gpg "ssh"]
                    program = "{$this->tmp}/unavailable-ssh-program"

                CONFIG,
        );
        FS::dumpFile($repository . '/fixture.php', '<?php return 1;');

        $commit = self::commitGitChanges($repository, 'Unsigned fixture commit');

        $this->assertStringNotContainsString(
            "\ngpgsig ",
            self::runGit(
                $repository,
                [
                    'cat-file',
                    'commit',
                    $commit,
                ],
            ),
        );
    }

    public static function signingFormatsProvider(): iterable
    {
        yield 'GPG signing' => ['openpgp'];

        yield 'SSH signing' => ['ssh'];
    }
}
