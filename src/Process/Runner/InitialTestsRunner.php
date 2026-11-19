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

use Closure;
use Infection\AbstractTestFramework\TestFrameworkAdapter;
use Infection\Event\EventDispatcher\EventDispatcher;
use Infection\Event\Events\ArtefactCollection\InitialTestExecution\InitialTestCaseWasCompleted;
use Infection\Event\Events\ArtefactCollection\InitialTestExecution\InitialTestSuiteWasFinished;
use Infection\Event\Events\ArtefactCollection\InitialTestExecution\InitialTestSuiteWasStarted;
use Infection\Process\Factory\InitialTestsRunProcessFactory;
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
        private readonly EventDispatcher $eventDispatcher,
    ) {
    }

    /**
     * @param string[] $phpExtraOptions
     * @param Closure(): void|null $onProgress
     */
    public function run(
        TestFrameworkAdapter $testFrameworkAdapter,
        string $testFrameworkExtraOptions,
        array $phpExtraOptions,
        bool $skipCoverage,
        ?Closure $onProgress = null,
    ): Process {
        $process = $this->processBuilder->createProcess(
            $testFrameworkAdapter,
            $testFrameworkExtraOptions,
            $phpExtraOptions,
            $skipCoverage,
        );

        $testFrameworkName = $testFrameworkAdapter->getName();

        $this->eventDispatcher->dispatch(
            new InitialTestSuiteWasStarted(
                commandLine: $process->getCommandLine(),
                testFrameworkName: $testFrameworkName,
                testFrameworkVersion: $this->getVersion($testFrameworkAdapter),
            ),
        );

<<<<<<< HEAD
        $process->run(function (string $type) use ($process, $testFrameworkName): void {
=======
        $process->run(function (string $type) use ($process, $onProgress): void {
>>>>>>> 84c4a5f6b (refactor(test-framework): Add an optional progress callback to initial runs)
            if ($type === Process::ERR) {
                // Stop on the first error encountered
                $process->stop();
            }

<<<<<<< HEAD
            $this->eventDispatcher->dispatch(new InitialTestCaseWasCompleted($testFrameworkName));
=======
            $onProgress?->__invoke();

            $this->eventDispatcher->dispatch(new InitialTestCaseWasCompleted(isStaticAnalysis: false));
>>>>>>> 84c4a5f6b (refactor(test-framework): Add an optional progress callback to initial runs)
        });

        $this->eventDispatcher->dispatch(new InitialTestSuiteWasFinished($testFrameworkName, $process->getOutput()));

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
