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

namespace Infection\TestFramework\Common;

use Closure;
use Stringable;
use function extension_loaded;
use Infection\AbstractTestFramework\InvalidVersion;
use Infection\TestFramework\Common\Throwable\InitialTestsFailed;
use Infection\TestFramework\Contracts\CompletedProcess;
use Infection\TestFramework\Contracts\ShellCommandRunner;
use Infection\TestFramework\Contracts\TestFramework;
use Infection\TestFramework\Contracts\TestFrameworkLogger;
use const PHP_SAPI;
use Symfony\Component\Process\Exception\RuntimeException;

/**
 * @internal
 * @final
 */
readonly class InitialTestsRunner
{
    public function __construct(
        private ShellCommandRunner $shellCommandRunner,
        private TestFrameworkLogger $testFrameworkLogger,
    ) {
    }

    /**
     * @param string[] $command
     * @param (Closure(string): string)|null $buildFailureRecommendations
     *
     * @throws InitialTestsFailed
     * @throws RuntimeException When the initial command cannot complete.
     */
    public function run(
        TestFramework $testFramework,
        array $command,
        bool $skipCoverage = true,
        string $testFrameworkType = 'tests',
        ?Closure $buildFailureRecommendations = null,
    ): CompletedProcess {
        $testFrameworkName = $testFramework->getName();
        $buildFailureRecommendations ??= static fn (string $commandLine) => '';

        $this->testFrameworkLogger->initialRunStarted(
            $testFrameworkName,
            $this->getVersion($testFramework),
        );

        $result = $this->shellCommandRunner->run(
            command: $command,
            callback: fn () => $this->testFrameworkLogger->initialRunProgressed($testFrameworkName),
            env: $this->buildProcessEnvironmentOverrides($skipCoverage),
            timeout: null,
            useOriginalPhpConfiguration: !$skipCoverage,
        );

        $this->testFrameworkLogger->initialRunFinished(
            $testFrameworkName,
            $result->command,
            $result->stdout,
        );

        if (!$result->isSuccessful()) {
            throw InitialTestsFailed::fromCompletedProcess(
                $result,
                $testFrameworkName,
                $testFrameworkType,
                $buildFailureRecommendations($result->commandLine),
            );
        }

        return $result;
    }

    private function getVersion(TestFramework $testFramework): string
    {
        try {
            return $testFramework->getVersion();
        } catch (InvalidVersion) {
            return TestFramework::UNKNOWN_VERSION;
        }
    }

    private static function shouldEnableXdebugCoverage(): bool
    {
        // Prefer PCOV and phpdbg over enabling Xdebug for coverage.
        return !extension_loaded('pcov') && PHP_SAPI !== 'phpdbg';
    }

    /**
     * @param bool $skipCoverage
     *
     * @return array<string, string|Stringable|false>
     */
    public function buildProcessEnvironmentOverrides(bool $skipCoverage): array
    {
        return !$skipCoverage && self::shouldEnableXdebugCoverage()
            ? ['XDEBUG_MODE' => 'coverage']
            : [];
    }
}
