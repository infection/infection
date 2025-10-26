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
use Composer\XdebugHandler\PhpConfig;
use Composer\XdebugHandler\XdebugHandler;
use function extension_loaded;
use const PHP_SAPI;
use Symfony\Component\Process\Process;

/**
 * @internal
 *
 * Process which is aware of the XdebugHandler configuration. This allows to start the sub-process
 * with the original configuration.
 *
 * For example, if infection is launched with Xdebug, we usually restart the process without xdebug.
 * However, we may still require Xdebug for getting the coverage reports from the initial test run.
 */
final class OriginalPhpProcess extends Process
{
    /**
     * @param array<string|bool>|null $env
     */
    public function start(?callable $callback = null, ?array $env = null): void
    {
        $phpConfig = new PhpConfig();
        $phpConfig->useOriginal();

        if (self::shallExtendEnvironmentWithXdebugMode()) {
            $env = array_merge($env ?? [], [
                'XDEBUG_MODE' => 'coverage',
            ]);
        }

        parent::start($callback, $env ?? []);

        $phpConfig->usePersistent();
    }

    private static function shallExtendEnvironmentWithXdebugMode(): bool
    {
        // Most obvious cases when we don't want to add XDEBUG_MODE=coverage:
        // - PCOV is loaded
        // - PHPDBG is in use
        if (extension_loaded('pcov') || PHP_SAPI === 'phpdbg') {
            return false;
        }

        // The last case: Xdebug 3+ running inactive.
        return true;

        // Why going through all the trouble above? We don't want to enable
        // Xdebug when there are more compelling choices. In the end the user is
        // still in control: they can provide XDEBUG_MODE=coverage on their own.
    }
}
