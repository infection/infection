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

namespace Infection\Composer;

use Infection\FileSystem\Finder\ComposerExecutableFinder;
use Symfony\Component\Process\Process;

/**
 * @internal
 */
class ComposerProcessFactory
{
    private const float REQUIRE_PACKAGE_TIMEOUT_IN_SECONDS = 120.0; // 2 minutes

    /**
     * @var list<string>|null
     */
    private ?array $composerExecutable = null;

    public function __construct(
        private readonly ComposerExecutableFinder $composerExecutableFinder,
    ) {
    }

    public function getVersionProcess(): Process
    {
        return $this->createProcess(
            [
                ...$this->getComposerExecutable(),
                '--version',
                '--no-ansi',
            ],
            ['SHELL_VERBOSITY' => 0],
        );
    }

    public function getVendorDirProcess(): Process
    {
        return $this->createProcess(
            [
                ...$this->getComposerExecutable(),
                'config',
                'vendor-dir',
                '--no-ansi',
            ],
            ['SHELL_VERBOSITY' => 0],
        );
    }

    public function getBinDirProcess(): Process
    {
        return $this->createProcess(
            [
                ...$this->getComposerExecutable(),
                'config',
                'bin-dir',
                '--no-ansi',
            ],
            ['SHELL_VERBOSITY' => 0],
        );
    }

    public function getRequireDevPackageProcess(string $package): Process
    {
        $process = $this->createProcess(
            [
                ...$this->getComposerExecutable(),
                'require',
                '--dev',
                $package,
            ],
        );

        $process->setTimeout(self::REQUIRE_PACKAGE_TIMEOUT_IN_SECONDS);

        return $process;
    }

    /**
     * @param list<string> $command
     * @param array<string, int|string> $environmentVariables
     */
    private function createProcess(
        array $command,
        array $environmentVariables = [],
    ): Process {
        return new Process(
            $command,
            env: $environmentVariables,
        );
    }

    /**
     * @return list<string>
     */
    private function getComposerExecutable(): array
    {
        return $this->composerExecutable ??= $this->composerExecutableFinder->find();
    }
}
