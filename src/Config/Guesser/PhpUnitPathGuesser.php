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

use stdClass;
use function str_contains;
use function trim;

/**
 * @internal
 */
final readonly class PhpUnitPathGuesser
{
    private const CURRENT_DIR_PATH = '.';

    public function __construct(private stdClass $composerJsonContent)
    {
    }

    public function guess(): string
    {
        if (!isset($this->composerJsonContent->autoload)) {
            return self::CURRENT_DIR_PATH;
        }

        $autoload = $this->composerJsonContent->autoload;

        if (isset($autoload->{'psr-4'})) {
            return $this->getPhpUnitDir((array) $autoload->{'psr-4'});
        }

        if (isset($autoload->{'psr-0'})) {
            return $this->getPhpUnitDir((array) $autoload->{'psr-0'});
        }

        return self::CURRENT_DIR_PATH;
    }

    /**
     * @param array<string, string> $parsedPaths
     */
    private function getPhpUnitDir(array $parsedPaths): string
    {
        foreach ($parsedPaths as $namespace => $parsedPath) {
            // for old Symfony projects (<=2.7) phpunit.xml is located in ./app folder
            if (str_contains($namespace, 'SymfonyStandard') && trim($parsedPath, '/') === 'app') {
                return 'app';
            }
        }

        return self::CURRENT_DIR_PATH;
    }
}
