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

namespace Infection\TestFramework;

use Closure;
use function explode;
use Infection\AbstractTestFramework\TestFrameworkAdapter;
use Infection\Configuration\Configuration;
use Infection\Console\ConsoleOutput;
use Infection\Mutant\Mutant;
use Infection\Process\Factory\InitialTestsRunProcessFactory;
use Infection\Process\Factory\MutantProcessContainerFactory;
use Infection\Process\Runner\InitialTestsFailed;
use Infection\TestFramework\Contracts\InitialTestsResult;
use Infection\TestFramework\Contracts\MutantEvaluationPipe;
use Infection\TestFramework\Contracts\TestFramework;
use Infection\TestFramework\Coverage\CoverageChecker;
use Symfony\Component\Process\Process;

/**
 * @deprecated This is for the compatibility layer with the old AbstractTestFramework contract. To be removed.
 */
final readonly class LegacyTestFrameworkBridge implements TestFramework
{
    public function __construct(
        private TestFrameworkAdapter $adapter,
        private ConsoleOutput $consoleOutput,
        private CoverageChecker $coverageChecker,
        private InitialTestsRunProcessFactory $initialRunProcessFactory,
        private Configuration $config,
        private MutantProcessContainerFactory $processFactory,
    ) {
    }

    public function getName(): string
    {
        return $this->adapter->getName();
    }

    public function getVersion(): string
    {
        return $this->adapter->getVersion();
    }

    public function checkRequirements(): void
    {
        // TODO: check supported version

        if ($this->config->skipInitialTests) {
            $this->consoleOutput->logSkippingInitialTests();
            $this->coverageChecker->checkCoverageExists();
        }
    }

    public function executeInitialRun(?Closure $onProgress = null): InitialTestsResult
    {
        $initialTestSuiteProcess = $this->initialRunProcessFactory->createProcess(
            $this->config->testFrameworkExtraOptions,
            $this->getInitialTestsPhpOptionsArray(),
            $this->config->skipCoverage,
        );
        $initialTestSuiteProcess->run(static function (string $type) use ($initialTestSuiteProcess, $onProgress): void {
            if ($type === Process::ERR) {
                $initialTestSuiteProcess->stop();
            }

            $onProgress?->__invoke();
        });

        if (!$initialTestSuiteProcess->isSuccessful()) {
            throw InitialTestsFailed::fromProcessAndAdapter(
                $initialTestSuiteProcess,
                $this->adapter,
            );
        }

        $output = $initialTestSuiteProcess->getOutput();

        $this->coverageChecker->checkCoverageHasBeenGenerated(
            $initialTestSuiteProcess->getCommandLine(),
            $output,
        );

        return new InitialTestsResult($output);
    }

    public function test(Mutant $mutant): MutantEvaluationPipe
    {
        return $this->processFactory->create(
            $mutant,
            $this->config->testFrameworkExtraOptions,
        );
    }

    /**
     * @return string[]
     */
    private function getInitialTestsPhpOptionsArray(): array
    {
        return explode(' ', (string) $this->config->initialTestsPhpOptions);
    }
}
