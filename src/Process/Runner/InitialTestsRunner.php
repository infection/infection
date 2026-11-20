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

use Infection\AbstractTestFramework\TestFrameworkAdapter;
use Infection\Process\Factory\InitialTestsRunProcessFactory;
use Infection\TestFramework\Contracts\TestFrameworkLogger;
use InvalidArgumentException;
use Symfony\Component\Process\Process;

/**
 * @internal
 * @final
 */
class InitialTestsRunner
{
    public function __construct(
        private readonly InitialTestsRunProcessFactory $processBuilder,
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
    ): Process {
        $process = $this->processBuilder->createProcess(
            $testFrameworkAdapter,
            $testFrameworkExtraOptions,
            $phpExtraOptions,
            $skipCoverage,
        );

        $testFrameworkName = $testFrameworkAdapter->getName();

        $testFrameworkLogger->initialRunStarted(
            $testFrameworkName,
            $this->getVersion($testFrameworkAdapter),
            $process->getCommandLine(),
        );

        $process->run(static function (string $type) use ($process, $testFrameworkName, $testFrameworkLogger): void {
            if ($type === Process::ERR) {
                // Stop on the first error encountered
                $process->stop();
            }

            $testFrameworkLogger->initialRunProgressed($testFrameworkName);
        });

        $testFrameworkLogger->initialRunFinished($testFrameworkName, $process->getOutput());

        return $process;
    }

    public function getVersion(TestFrameworkAdapter $testFrameworkAdapter): string
    {
        try {
            return $testFrameworkAdapter->getVersion();
        } catch (InvalidArgumentException) {
            return 'unknown';
        }
    }
}
