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

namespace Infection\Testing\TestFramework\Debug;

use function array_filter;
use function array_merge;
use function array_values;
use function base64_encode;
use Infection\FileSystem\Finder\Exception\FinderException;
use function json_encode;
use const JSON_THROW_ON_ERROR;
use const PHP_SAPI;
use function sprintf;
use function str_starts_with;
use Symfony\Component\Process\PhpExecutableFinder;
use function var_export;

/**
 * @internal
 */
final class DebugCommandLine
{
    /** @var string[]|null */
    private ?array $cachedPhpCommandLine = null;

    public function __construct(
        private readonly PhpExecutableFinder $phpExecutableFinder,
    ) {
    }

    /**
     * @param string[] $phpArguments
     * @param array<string, string> $options
     *
     * @throws FinderException
     *
     * @return string[]
     */
    public function create(
        string $runtime,
        array $phpArguments,
        array $options,
    ): array {
        $command = array_merge(
            $this->findPhp(),
            self::filterEmptyArguments($phpArguments),
            self::createRuntimeArguments($runtime),
        );

        foreach ($options as $name => $value) {
            $command[] = '--' . $name;
            $command[] = $value;
        }

        $command[] = '--command';
        $command[] = base64_encode(json_encode($command, JSON_THROW_ON_ERROR));

        return $command;
    }

    /**
     * @param string[] $phpArguments
     *
     * @return list<non-empty-string>
     */
    private static function filterEmptyArguments(array $phpArguments): array
    {
        return array_values(
            array_filter(
                $phpArguments,
                static fn (string $argument): bool => $argument !== '',
            ),
        );
    }

    /**
     * @return non-empty-list<string>
     */
    private static function createRuntimeArguments(string $runtime): array
    {
        // We cannot have `php phar:///project/dist/infection.phar/resources/debug-runtime.php`
        // as PHP's CLI does not accept `phar://`.
        // However, we can have:
        // php -r "require 'phar:///project/dist/infection.phar/resources/debug-runtime.php';"
        return str_starts_with($runtime, 'phar://')
            ? [
                '-r',
                sprintf('require %s;', var_export($runtime, true)),
                '--',
            ]
            : [$runtime];
    }

    /**
     * TODO: Consolidate this with TestFramework\Common\CommandLineBuilder::findPhp().
     *
     * @throws FinderException
     *
     * @return string[]
     */
    private function findPhp(): array
    {
        $cachedPhpCommandLine = $this->cachedPhpCommandLine;

        if ($cachedPhpCommandLine !== null) {
            return $cachedPhpCommandLine;
        }

        $phpExecutable = $this->phpExecutableFinder->find(false);

        if ($phpExecutable === false) {
            throw FinderException::phpExecutableNotFound();
        }

        $cachedPhpCommandLine[] = $phpExecutable;

        if (PHP_SAPI === 'phpdbg') {
            $cachedPhpCommandLine[] = '-qrr';
        }

        $this->cachedPhpCommandLine = $cachedPhpCommandLine;

        return $cachedPhpCommandLine;
    }
}
