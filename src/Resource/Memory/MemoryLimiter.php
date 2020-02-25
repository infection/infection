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

namespace Infection\Resource\Memory;

use Composer\XdebugHandler\XdebugHandler;
use function file_exists;
use Infection\AbstractTestFramework\MemoryUsageAware;
use Infection\AbstractTestFramework\TestFrameworkAdapter;
use function is_string;
use function is_writable;
use const PHP_EOL;
use const PHP_SAPI;
use function Safe\ini_get;
use function Safe\sprintf;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;
use Webmozart\Assert\Assert;

/**
 * @internal
 */
final class MemoryLimiter
{
    private $fileSystem;
    private $iniLocation;

    /**
     * @param string|false $iniLocation
     */
    public function __construct(Filesystem $fileSystem, $iniLocation)
    {
        if (!is_string($iniLocation)) {
            Assert::false(
                $iniLocation,
                'Expected the iniLocation to either be a string or false. Got "%s"'
            );
        }

        $this->fileSystem = $fileSystem;
        $this->iniLocation = (string) $iniLocation;
    }

    public function applyMemoryLimitFromProcess(Process $process, TestFrameworkAdapter $adapter): void
    {
        if (!$adapter instanceof MemoryUsageAware || $this->hasMemoryLimitSet() || $this->isUsingSystemIni()) {
            return;
        }

        $tmpConfigPath = $this->iniLocation;

        if ($tmpConfigPath === '' || !$this->fileSystem->exists($tmpConfigPath)) {
            // Cannot add a memory limit: there is no php.ini file
            return;
        }

        $memoryLimit = $adapter->getMemoryUsed($process->getOutput());

        if ($memoryLimit < 0) {
            // Cannot detect memory used, not setting any limits
            return;
        }

        /*
         * Since we know how much memory the initial test suite used, and only if we know, we can
         * enforce a memory limit upon all mutation processes. Limit is set to be twice the known
         * amount, because if we know that a normal test suite used X megabytes, if a mutants uses a
         * lot more, this is a definite error.
         *
         * By default we let a mutant process use twice as much more memory as an initial test suite
         * consumed.
         */
        $memoryLimit *= 2;

        try {
            $this->fileSystem->appendToFile(
                $tmpConfigPath,
                PHP_EOL . sprintf('memory_limit = %dM', $memoryLimit)
            );
        } catch (IOException $e) {
            // Cannot add a memory limit: file is not writable
            return;
        }
    }

    private function hasMemoryLimitSet(): bool
    {
        // -1 means no memory limit. Anything else means the user has set their own limits, which we
        // don't want to mess with
        return ini_get('memory_limit') !== '-1';
    }

    private function isUsingSystemIni(): bool
    {
        // Under phpdbg we're using a system php.ini and we can't add a memory limit there. If there
        // is no skipped version of xdebug handler we are also using the system php ini
        return PHP_SAPI === 'phpdbg' || XdebugHandler::getSkippedVersion() === '';
    }
}
