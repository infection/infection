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

namespace Infection\Tests\Testing;

use function dirname;
use Infection\Testing\SingletonContainer;
use Infection\Tests\FileSystem\FileSystemTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\WithEnvironmentVariable;
use Psr\Log\NullLogger;
use function Safe\chdir;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Filesystem\Path;

#[CoversClass(SingletonContainer::class)]
#[Group('integration')]
#[WithEnvironmentVariable('GITHUB_ACTIONS', 'true')]
#[WithEnvironmentVariable('GITLAB_CI', 'true')]
#[WithEnvironmentVariable('GITHUB_WORKSPACE', 'relative')]
#[WithEnvironmentVariable('CI_PROJECT_DIR', 'relative')]
final class SingletonContainerTest extends FileSystemTestCase
{
    public function test_it_ignores_ci_environment_variables(): void
    {
        $container = SingletonContainer::getContainer()->withValues(
            logger: new NullLogger(),
            output: new NullOutput(),
        );
        $detector = $container->getCiDetector();

        $this->assertFalse($detector->isCiDetected());
    }

    public function test_it_keeps_local_defaults_when_configuring_values_in_ci(): void
    {
        $container = SingletonContainer::getContainer()
            ->withValues(
                logger: new NullLogger(),
                output: new NullOutput(),
                configFile: __DIR__ . '/../../../infection.json5',
            )
        ;
        $configuration = $container->getConfiguration();

        $this->assertFalse($configuration->noProgress);
        $this->assertFalse($configuration->logs->getUseGitHubAnnotationsLogger());
        $this->assertSame($this->tmp, $configuration->projectDirectory);
    }

    public function test_it_uses_the_git_project_root_from_a_subdirectory(): void
    {
        chdir(__DIR__);

        $container = SingletonContainer::getContainer()
            ->withValues(
                logger: new NullLogger(),
                output: new NullOutput(),
                configFile: __DIR__ . '/../../../infection.json5',
            )
        ;
        $configuration = $container->getConfiguration();
        $expected = Path::canonicalize(dirname(__DIR__, 3));

        $this->assertSame($expected, $configuration->projectDirectory);
    }
}
