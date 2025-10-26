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

/**
 * Provides value for pcov.directory configuration option. Can be injected with a configuration object to provide a better, precise, value.
 *
 * @internal
 * @final
 */
class PCOVDirectoryProvider
{
    private ?string $phpConfiguredPcovDirectory = null;

    public function __construct(?string $iniValue = null)
    {
        try {
            $this->phpConfiguredPcovDirectory = $iniValue ?? ini_get('pcov.directory');
        } catch (InfoException) {
            // Probably not using PCOV
            $this->phpConfiguredPcovDirectory = null;
        }
    }

    public function shallProvide(): bool
    {
        // That's the default value as per:
        // https://github.com/krakjoe/pcov/blob/57e143363aa6ba3c4d1e1b0a2e68556e28f38950/pcov.c#L80-L83
        return $this->phpConfiguredPcovDirectory === '';
    }

    public function getDirectory(): string
    {
        // Returning CWD simplicity's sake.
        return '.';
    }
}
