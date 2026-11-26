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

namespace Infection\Process\Runner;

use function extension_loaded;
use Infection\AbstractTestFramework\InvalidVersion;
use Infection\AbstractTestFramework\TestFrameworkAdapter;
use Infection\TestFramework\Contracts\CompletedProcess;
use Infection\TestFramework\Contracts\ShellCommandRunner;
use Infection\TestFramework\Contracts\TestFramework;
use Infection\TestFramework\Contracts\TestFrameworkLogger;
use const PHP_SAPI;

/**
 * @internal
 * @final
 */
class InitialTestsRunner
{
    public function __construct(
        private readonly ShellCommandRunner $shellCommandRunner,
    ) {
    }

    /**
     * @param string[] $phpExtraOptions
     */
    public function run(
        TestFrameworkAdapter $testFrameworkAdapter,
        string $testFrameworkExtraOptions,
        array $phpExtraOptions,
        bool $skipCoverage,
        TestFrameworkLogger $testFrameworkLogger,
    ): CompletedProcess {
        $command = $testFrameworkAdapter->getInitialTestRunCommandLine(
            $testFrameworkExtraOptions,
            $phpExtraOptions,
            $skipCoverage,
        );

        $testFrameworkName = $testFrameworkAdapter->getName();

        $testFrameworkLogger->initialRunStarted(
            $testFrameworkName,
            $this->getVersion($testFrameworkAdapter),
        );

        $env = [];

        if (
            !$skipCoverage
            && self::shouldEnableXdebugCoverage()
        ) {
            $env['XDEBUG_MODE'] = 'coverage';
        }

        $process = $this->shellCommandRunner->run(
            command: $command,
            callback: static fn () => $testFrameworkLogger->initialRunProgressed($testFrameworkName),
            env: $env,
            timeout: null,
            useOriginalPhpConfiguration: !$skipCoverage,
        );

        $testFrameworkLogger->initialRunFinished(
            $testFrameworkName,
            $process->command,
            $process->stdout,
        );

        return $process;
    }

    public function getVersion(TestFrameworkAdapter $testFrameworkAdapter): string
    {
        try {
            return $testFrameworkAdapter->getVersion();
        } catch (InvalidVersion) {
            return TestFramework::UNKNOWN_VERSION;
        }
    }

    private static function shouldEnableXdebugCoverage(): bool
    {
        // Prefer PCOV and phpdbg over enabling Xdebug for coverage.
        return !extension_loaded('pcov') && PHP_SAPI !== 'phpdbg';
    }
}
