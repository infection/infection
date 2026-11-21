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

use Infection\AbstractTestFramework\InvalidVersion;
use Infection\TestFramework\Common\Throwable\InitialTestsFailed;
use Infection\TestFramework\Contracts\InitialRunResults;
use Infection\TestFramework\Contracts\ShellCommandRunner;
use Infection\TestFramework\Contracts\TestFramework;
use Infection\TestFramework\Contracts\TestFrameworkLogger;

/**
 * This is needed for 2 purposes:
 *
 * 1. To warm up the SA tool's cache
 * 2. To make sure SA passes before using it inside Infection to kill Mutants
 *
 * @internal
 * @final
 */
readonly class InitialStaticAnalysisRunner
{
    public function __construct(
        private ShellCommandRunner $shellCommandRunner,
        private TestFrameworkLogger $testFrameworkLogger,
    ) {
    }

    /**
     * @param string[] $command
     *
     * @throws InitialTestsFailed
     */
    public function run(TestFramework $testFramework, array $command): InitialRunResults
    {
        $testFrameworkName = $testFramework->getName();

        $this->testFrameworkLogger->initialRunStarted(
            $testFrameworkName,
            $this->getVersion($testFramework),
        );

        $process = $this->shellCommandRunner->run(
            command: $command,
            callback: fn () => $this->testFrameworkLogger->initialRunProgressed($testFrameworkName),
            timeout: null,
        );

        $this->testFrameworkLogger->initialRunFinished(
            $testFrameworkName,
            $process->command,
            $process->stdout,
        );

        if (!$process->isSuccessful()) {
            throw InitialTestsFailed::fromCompletedProcess(
                $process,
                $testFramework->getName(),
                'static analysis',
                '',
            );
        }

        return new InitialRunResults($process->stdout, null);
    }

    private function getVersion(TestFramework $testFramework): string
    {
        try {
            return $testFramework->getVersion();
        } catch (InvalidVersion) {
            return TestFramework::UNKNOWN_VERSION;
        }
    }
}
