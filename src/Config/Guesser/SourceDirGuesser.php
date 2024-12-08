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

namespace Infection\Config\Guesser;

use function array_walk_recursive;
use const DIRECTORY_SEPARATOR;
use function in_array;
use function is_array;
use function is_string;
use LogicException;
use stdClass;
use function trim;

/**
 * @internal
 */
class SourceDirGuesser
{
    public function __construct(private readonly stdClass $composerJsonContent)
    {
    }

    /**
     * @return string[]|null
     */
    public function guess(): ?array
    {
        if (!isset($this->composerJsonContent->autoload)) {
            return null;
        }

        $autoload = $this->composerJsonContent->autoload;

        if (isset($autoload->{'psr-4'})) {
            return $this->getValues('psr-4');
        }

        if (isset($autoload->{'psr-0'})) {
            return $this->getValues('psr-0');
        }

        return null;
    }

    /**
     * @return string[]
     */
    private function getValues(string $psr): array
    {
        $dirs = $this->parsePsrSection((array) $this->composerJsonContent->autoload->{$psr});

        // we don't want to mix different framework's folders like "app" for Symfony
        if (in_array('src', $dirs, true)) {
            return ['src'];
        }

        return $dirs;
    }

    /**
     * @param array<string[]|string|mixed> $autoloadDirs
     *
     * @return string[]
     */
    private function parsePsrSection(array $autoloadDirs): array
    {
        $dirs = [];

        foreach ($autoloadDirs as $path) {
            if (!is_array($path) && !is_string($path)) {
                throw new LogicException('autoload section does not match the expected JSON schema');
            }

            $this->parsePath($path, $dirs);
        }

        return $dirs;
    }

    /**
     * @param string[]|string $path
     * @param string[] $dirs
     */
    private function parsePath(array|string $path, array &$dirs): void
    {
        if (is_array($path)) {
            array_walk_recursive(
                $path,
                function ($el) use (&$dirs): void {
                    $this->parsePath($el, $dirs);
                },
            );
        }

        if (is_string($path)) {
            $dirs[] = trim($path, DIRECTORY_SEPARATOR);
        }
    }
}
