<?php
/**
 * This code is licensed under the BSD 3-Clause License.
 *
 * Copyright (c) 2017-2019, Maks Rafalko
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

namespace Infection\Console\Util;

use Composer\XdebugHandler\PhpConfig;
use Symfony\Component\Process\Process;

/**
 * @internal
 */
final class PhpProcess extends Process
{
    /**
     * Runs a PHP process with xdebug loaded
     *
     * If xdebug was loaded in the main process, it will have been restarted
     * without xdebug and configured to keep xdebug out of PHP sub-processes.
     *
     * This method allows a sub-process to run with xdebug enabled (if it was
     * originally loaded), then restores the xdebug-free environment.
     *
     * This means that we can use xdebug when it is required and not have to
     * worry about it for the bulk of other processes, which do not need it and
     * work better without it.
     *
     * {@inheritdoc}
     */
    public function start(callable $callback = null, array $env = null): void
    {
        $phpConfig = new PhpConfig();

        $phpConfig->useOriginal();

        // As of 1.3.2 xdebug-handler won't update $_ENV if it is in use.
        // But Symfony's Process will happily import everything from $_ENV,
        // hence we need to reset it just as xdebug-handler does
        $updateEnv = false !== stripos((string) ini_get('variables_order'), 'E');

        if ($updateEnv) {
            unset($_ENV['PHPRC']);
            unset($_ENV['PHP_INI_SCAN_DIR']);
        }

        parent::start($callback, $env ?? []);
        $phpConfig->usePersistent();

        if ($updateEnv) {
            $_ENV['PHPRC'] = $_SERVER['PHPRC'];
            $_ENV['PHP_INI_SCAN_DIR'] = $_SERVER['PHP_INI_SCAN_DIR'];
        }
    }
}
