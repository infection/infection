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

namespace Infection\TestFramework;

use function array_filter;
use function array_merge;
use Infection\FileSystem\Finder\Exception\FinderException;
use function is_executable;
use const PHP_SAPI;
use function shell_exec;
use function substr;
use Symfony\Component\Process\PhpExecutableFinder;

/**
 * @internal
 * @final
 */
class CommandLineBuilder
{
    private const BAT_EXTENSION_LENGTH = 4;

    /** @var string[]|null */
    private ?array $cachedPhpCmdLine = null;

    /**
     * @param string[] $frameworkArgs
     * @param string[] $phpExtraArgs
     *
     * @return string[]
     */
    public function build(string $testFrameworkExecutable, array $phpExtraArgs, array $frameworkArgs): array
    {
        if ($this->isBatchFile($testFrameworkExecutable)) {
            return array_merge([$testFrameworkExecutable], $frameworkArgs);
        }

        /*
         * That's an empty options list by all means, we need to see it as such
         */
        $phpExtraArgs = array_filter($phpExtraArgs);

        /*
         * Run an executable as it is if we're using a standard CLI and
         * there's a standard interpreter available on PATH.
         *
         * This lets folks use, say, a bash wrapper over phpunit.
         */
        if ('cli' === PHP_SAPI && $phpExtraArgs === [] && is_executable($testFrameworkExecutable) && shell_exec('command -v php') !== null) {
            return array_merge([$testFrameworkExecutable], $frameworkArgs);
        }

        /*
         * In all other cases run it with a chosen PHP interpreter
         */
        $commandLineArgs = array_merge(
            $this->findPhp(),
            $phpExtraArgs,
            [$testFrameworkExecutable],
            $frameworkArgs,
        );

        return array_filter($commandLineArgs);
    }

    /**
     * @return string[]
     */
    private function findPhp(): array
    {
        $cachedPhpCmdLine = $this->cachedPhpCmdLine;

        if ($cachedPhpCmdLine !== null) {
            return $cachedPhpCmdLine;
        }

        $phpExec = (new PhpExecutableFinder())->find(false);

        if ($phpExec === false) {
            throw FinderException::phpExecutableNotFound();
        }

        $cachedPhpCmdLine[] = $phpExec;

        if (PHP_SAPI === 'phpdbg') {
            $cachedPhpCmdLine[] = '-qrr';
        }

        $this->cachedPhpCmdLine = $cachedPhpCmdLine;

        return $cachedPhpCmdLine;
    }

    private function isBatchFile(string $path): bool
    {
        return substr($path, -self::BAT_EXTENSION_LENGTH) === '.bat';
    }
}
