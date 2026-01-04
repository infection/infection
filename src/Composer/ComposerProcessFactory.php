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

use Closure;
use function getenv;
use const PHP_OS_FAMILY;
use RuntimeException;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

/**
 * This file is taken from the `box-project/box` project.
 *
 *  (c) 2013 Kevin Herrera <kevin@herrera.io>
 *           Théo Fidry <theo.fidry@gmail.com>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies
 * of the Software, and to permit persons to whom the Software is furnished to do
 * so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
 * FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
 * COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
 * IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
 * CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 *
 * @final
 * @private
 */
class ComposerProcessFactory
{
    private string $composerExecutable;

    /**
     * @param Closure():string $composerExecutableFactory
     */
    public function __construct(
        private readonly Closure $composerExecutableFactory,
        private readonly ?string $verbosity,
        private readonly bool $ansi,
        private readonly array $defaultEnvironmentVariables,
    ) {
    }

    public static function create(
        ?string $composerExecutable = null,
        ?IO $io = null,
    ): self {
        $io ??= IO::createNull();

        return new self(
            $composerExecutable === null
                ? self::retrieveComposerExecutable(...)
                : static fn () => $composerExecutable,
            self::retrieveSubProcessVerbosity($io),
            $io->isDecorated(),
            self::getDefaultEnvVars(),
        );
    }

    public function getVersionProcess(): Process
    {
        return $this->createProcess(
            [
                $this->getComposerExecutable(),
                '--version',
                // Never use ANSI support here as we want to parse the raw output.
                '--no-ansi',
            ],
            // Ensure that even if this command gets executed within the app with --quiet it still
            // works.
            ['SHELL_VERBOSITY' => 0],
        );
    }

    public function getDumpAutoloaderProcess(bool $noDev): Process
    {
        $composerCommand = [$this->getComposerExecutable(), 'dump-autoload', '--classmap-authoritative'];

        if ($noDev === true) {
            $composerCommand[] = '--no-dev';
        }

        if ($this->verbosity !== null) {
            $composerCommand[] = $this->verbosity;
        }

        if ($this->ansi) {
            $composerCommand[] = '--ansi';
        }

        return $this->createProcess($composerCommand);
    }

    public function getVendorDirProcess(): Process
    {
        return $this->createProcess(
            [
                $this->getComposerExecutable(),
                'config',
                'vendor-dir',
                // Never use ANSI support here as we want to parse the raw output.
                '--no-ansi',
            ],
            // Ensure that even if this command gets executed within the app with --quiet it still
            // works.
            ['SHELL_VERBOSITY' => 0],
        );
    }

    private function createProcess(array $command, array $environmentVariables = []): Process
    {
        return new Process(
            $command,
            env: [
                ...$this->defaultEnvironmentVariables,
                ...$environmentVariables,
            ],
        );
    }

    private function getComposerExecutable(): string
    {
        if (!isset($this->composerExecutable)) {
            $this->composerExecutable = ($this->composerExecutableFactory)();
        }

        return $this->composerExecutable;
    }

    private static function retrieveSubProcessVerbosity(IO $io): ?string
    {
        if ($io->isDebug()) {
            return '-vvv';
        }

        if ($io->isVeryVerbose()) {
            return '-v';
        }

        return null;
    }

    private static function getDefaultEnvVars(): array
    {
        $vars = ['COMPOSER_ORIGINAL_INIS' => ''];

        if ((string) getenv(Constants::ALLOW_XDEBUG) === '1') {
            $vars['COMPOSER_ALLOW_XDEBUG'] = '1';
        }

        return $vars;
    }

    private static function retrieveComposerExecutable(): string
    {
        $executableFinder = new ExecutableFinder();

        if (self::isWindows()) {
            $executableFinder->setSuffixes(['.exe', '.bat', '.cmd', '.com']);
        } else {
            $executableFinder->addSuffix('.phar');
        }

        if (null === $composer = $executableFinder->find('composer')) {
            throw new RuntimeException('Could not find a Composer executable.');
        }

        return $composer;
    }

    private static function isWindows(): bool
    {
        return PHP_OS_FAMILY === 'Windows';
    }
}
