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

namespace Infection\Resource\Performance;

use Composer\XdebugHandler\XdebugHandler;
use function extension_loaded;
use const PHP_EOL;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Configures file-based OPcache for mutation testing processes.
 *
 * When XdebugHandler restarts PHP without Xdebug, it creates a temporary php.ini
 * file and sets PHPRC to point to it. Child processes (PHPUnit runs) inherit this
 * via environment variables.
 *
 * This class appends OPcache settings to that temporary ini file, enabling
 * file-based bytecode caching for all mutation testing processes. This provides
 * ~15% performance improvement by avoiding repeated PHP parsing.
 *
 * @internal
 * @final
 */
class OpcacheConfigurer
{
    public function __construct(
        private readonly Filesystem $fileSystem,
        private readonly string $phpIniPath,
        private readonly string $opcachePath,
    ) {
    }

    public function configure(): void
    {
        // Skip if XdebugHandler didn't restart PHP - we can't modify the system php.ini
        if (XdebugHandler::getSkippedVersion() === '') {
            return;
        }

        // Skip if opcache extension is not loaded
        if (!extension_loaded('Zend OPcache')) {
            return;
        }

        // Skip if no writable ini file
        if ($this->phpIniPath === '' || !$this->fileSystem->exists($this->phpIniPath)) {
            return;
        }

        try {
            $this->fileSystem->mkdir($this->opcachePath);

            $this->fileSystem->appendToFile(
                $this->phpIniPath,
                PHP_EOL . 'opcache.enable_cli=1'
                . PHP_EOL . 'opcache.file_cache=' . $this->opcachePath
                . PHP_EOL . 'opcache.file_cache_only=1',
            );
        } catch (IOException) {
            // Cannot configure opcache: directory or file is not writable
        }
    }
}
