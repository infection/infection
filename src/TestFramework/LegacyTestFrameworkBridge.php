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

use function explode;
use Infection\AbstractTestFramework\MemoryUsageAware;
use Infection\AbstractTestFramework\TestFrameworkAdapter;
use Infection\Configuration\Configuration;
use Infection\Console\ConsoleOutput;
use Infection\Mutant\Mutant as CoreMutant;
use Infection\Process\Factory\MutantProcessContainerFactory;
use Infection\Process\Runner\InitialTestsFailed;
use Infection\Process\Runner\InitialTestsRunner;
use Infection\TestFramework\Contracts\InitialRunResults;
use Infection\TestFramework\Contracts\Mutant;
use Infection\TestFramework\Contracts\MutantEvaluationPipe;
use Infection\TestFramework\Contracts\TestFramework;
use Infection\TestFramework\Coverage\CoverageChecker;
use Webmozart\Assert\Assert;

/**
 * @internal
 *
 * @deprecated This is for the compatibility layer with the old AbstractTestFramework contract. To be removed.
 */
final readonly class LegacyTestFrameworkBridge implements TestFramework
{
    public function __construct(
        private TestFrameworkAdapter $adapter,
        private ConsoleOutput $consoleOutput,
        private CoverageChecker $coverageChecker,
        private InitialTestsRunner $initialTestsRunner,
        private Configuration $config,
        private MutantProcessContainerFactory $processFactory,
        private TestFrameworkExtraOptionsFilter $testFrameworkExtraOptionsFilter,
    ) {
    }

    public function getName(): string
    {
        return $this->adapter->getName();
    }

    public function checkRequirements(): void
    {
        // TODO: check supported version

        if ($this->config->skipInitialTests) {
            $this->consoleOutput->logSkippingInitialTests();
            $this->coverageChecker->checkCoverageExists();
        }
    }

    public function executeInitialRun(): InitialRunResults
    {
        $initialTestSuiteProcess = $this->initialTestsRunner->run(
            $this->config->testFrameworkExtraOptions,
            $this->getInitialTestsPhpOptionsArray(),
            $this->config->skipCoverage,
        );

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

        $memoryUsage = $this->adapter instanceof MemoryUsageAware
            ? $this->adapter->getMemoryUsed($output)
            : null;

        return new InitialRunResults(
            memoryUsage: $memoryUsage === -1. ? null : $memoryUsage,
        );
    }

    public function test(Mutant $mutant): MutantEvaluationPipe
    {
        Assert::isInstanceOf($mutant, CoreMutant::class);

        return $this->processFactory->create(
            $mutant,
            $this->getFilteredExtraOptionsForMutant(),
        );
    }

    /**
     * @return string[]
     */
    private function getInitialTestsPhpOptionsArray(): array
    {
        return explode(' ', (string) $this->config->initialTestsPhpOptions);
    }

    private function getFilteredExtraOptionsForMutant(): string
    {
        if ($this->adapter instanceof ProvidesInitialRunOnlyOptions) {
            return $this->testFrameworkExtraOptionsFilter->filterForMutantProcess(
                $this->config->testFrameworkExtraOptions,
                $this->adapter->getInitialRunOnlyOptions(),
            );
        }

        return $this->config->testFrameworkExtraOptions;
    }
}
