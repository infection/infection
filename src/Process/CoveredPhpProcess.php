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

namespace Infection\Process;

use function array_merge;
use Composer\XdebugHandler\XdebugHandler;
use function ini_get as ini_get_unsafe;
use Symfony\Component\Process\Process;

/**
 * @internal
 *
 * Process which setups necessary environment variables to make coverage reporting happen
 * without any extra user interaction.
 *
 * As of now we only cover Xdebug, adding XDEBUG_MODE environment variable to ensure it
 * is properly activated. We add this variable if we know that Xdebug was offloaded, or
 * if we know Xdebug is loaded since we can't know it coverage option is enabled (setting
 * XDEBUG_MODE won't change xdebug.mode).
 */
final class CoveredPhpProcess extends Process
{
    /**
     * @param array<string|bool>|null $env
     */
    public function start(?callable $callback = null, ?array $env = null): void
    {
        if (
            XdebugHandler::getSkippedVersion() !== '' ||
            // Any other value but false means Xdebug 3 is loaded. Xdebug 2 didn't have
            // it too, but it has coverage enabled at all times.
            ini_get_unsafe('xdebug.mode') !== false
        ) {
            $env = array_merge($env ?? [], [
                'XDEBUG_MODE' => 'coverage',
            ]);
        }

        parent::start($callback, $env ?? []);
    }
}
