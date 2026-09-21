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

namespace Infection\FileSystem\Finder;

use function explode;
use function is_executable;
use function is_file;

/**
 * @internal
 * @final
 */
class ComposerBinExecutableFinder
{
    private const string WINDOWS = 'Windows';

    private const string WINDOWS_PATH_SEPARATOR = ';';

    private const array DEFAULT_WINDOWS_EXECUTABLE_SUFFIXES = ['.exe', '.bat', '.cmd', '.com'];

    /**
     * @param list<string> $candidates
     */
    public function find(
        array $candidates,
        string $composerBinDir,
        string $operatingSystemFamily,
        string|false $pathExtension,
    ): ?string {
        $suffixes = [''];

        if ($operatingSystemFamily === self::WINDOWS) {
            $windowsSuffixes = $pathExtension === false || $pathExtension === ''
                ? self::DEFAULT_WINDOWS_EXECUTABLE_SUFFIXES
                : explode(self::WINDOWS_PATH_SEPARATOR, $pathExtension);

            $suffixes = ['', ...$windowsSuffixes];
        }

        foreach ($candidates as $name) {
            foreach ($suffixes as $suffix) {
                $composerCandidate = $composerBinDir . '/' . $name . $suffix;

                if (
                    is_file($composerCandidate)
                    && ($operatingSystemFamily === self::WINDOWS || is_executable($composerCandidate))
                ) {
                    return $composerCandidate;
                }
            }
        }

        return null;
    }
}
