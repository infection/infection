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

namespace Infection\Config\ValueProvider;

use Safe\Exceptions\InfoException;
use function Safe\ini_get;
use Symfony\Component\Filesystem\Path;

/**
 * When used as the coverage driver, PCOV selects the first existing directory
 * from `src`, `lib`, `app`, and the current working directory by default. This
 * may exclude source code located outside the selected directory.
 *
 * The `pcov.directory` setting overrides this behaviour.
 *
 * Infection knows which source code it targets and can therefore refine this
 * setting to provide a safer default.
 *
 * @see https://github.com/krakjoe/pcov
 * @see https://github.com/krakjoe/pcov/blob/57e143363aa6ba3c4d1e1b0a2e68556e28f38950/pcov.c#L357-L387
 *
 * @internal
 * @final
 */
readonly class PCOVDirectoryProvider
{
    // https://github.com/krakjoe/pcov/blob/57e143363aa6ba3c4d1e1b0a2e68556e28f38950/pcov.c#L80-L83
    private const string DEFAULT_DIRECTORY = '';

    private ?string $phpConfiguredPcovDirectory;

    /**
     * @param list<string> $sourceDirectoryPaths
     */
    public function __construct(
        private array $sourceDirectoryPaths,
        ?string $iniValue = null,
    ) {
        try {
            $this->phpConfiguredPcovDirectory = $iniValue ?? ini_get('pcov.directory');
        } catch (InfoException) {
            // Probably not using PCOV
            $this->phpConfiguredPcovDirectory = null;
        }
    }

    public function shouldProvide(): bool
    {
        return $this->phpConfiguredPcovDirectory === self::DEFAULT_DIRECTORY;
    }

    public function getDirectory(): string
    {
        if ($this->sourceDirectoryPaths === []) {
            return '.';
        }

        $longestCommonBasePath = Path::getLongestCommonBasePath(...$this->sourceDirectoryPaths);

        return $longestCommonBasePath ?? '.';
    }
}
