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

namespace Infection;

use function explode;
use Infection\AbstractTestFramework\TestFrameworkAdapter;
use Infection\Configuration\Configuration;
use Infection\Console\ConsoleOutput;
use Infection\Event\ApplicationExecutionWasFinished;
use Infection\Event\EventDispatcher\EventDispatcher;
use Infection\Metrics\MetricsCalculator;
use Infection\Metrics\MinMsiChecker;
use Infection\Metrics\MinMsiCheckFailed;
use Infection\Mutation\MutationGenerator;
use Infection\PhpParser\Visitor\IgnoreNode\NodeIgnorer;
use Infection\Process\Runner\InitialTestsFailed;
use Infection\Process\Runner\InitialTestsRunner;
use Infection\Process\Runner\MutationTestingRunner;
use Infection\Resource\Memory\MemoryLimiter;
use Infection\TestFramework\Coverage\CoverageChecker;
use Infection\TestFramework\IgnoresAdditionalNodes;
use Infection\TestFramework\ProvidesInitialRunOnlyOptions;
use Infection\TestFramework\TestFrameworkExtraOptionsFilter;

/**
 * @internal
 */
final readonly class Engine
{
    public function __construct(private Configuration $config, private TestFrameworkAdapter $adapter, private CoverageChecker $coverageChecker, private EventDispatcher $eventDispatcher, private InitialTestsRunner $initialTestsRunner, private MemoryLimiter $memoryLimiter, private MutationGenerator $mutationGenerator, private MutationTestingRunner $mutationTestingRunner, private MinMsiChecker $minMsiChecker, private ConsoleOutput $consoleOutput, private MetricsCalculator $metricsCalculator, private TestFrameworkExtraOptionsFilter $testFrameworkExtraOptionsFilter)
    {
    }

    /**
     * @throws InitialTestsFailed
     * @throws MinMsiCheckFailed
     */
    public function execute(): void
    {
        $this->runInitialTestSuite();
        $this->runMutationAnalysis();

        try {
            $this->minMsiChecker->checkMetrics(
                $this->metricsCalculator->getTestedMutantsCount(),
                $this->metricsCalculator->getMutationScoreIndicator(),
                $this->metricsCalculator->getCoveredCodeMutationScoreIndicator(),
                $this->consoleOutput,
            );
        } finally {
            $this->eventDispatcher->dispatch(new ApplicationExecutionWasFinished());
        }
    }

    private function runInitialTestSuite(): void
    {
        if ($this->config->shouldSkipInitialTests()) {
            $this->consoleOutput->logSkippingInitialTests();
            $this->coverageChecker->checkCoverageExists();

            return;
        }

        $initialTestSuiteProcess = $this->initialTestsRunner->run(
            $this->config->getTestFrameworkExtraOptions(),
            $this->getInitialTestsPhpOptionsArray(),
            $this->config->shouldSkipCoverage(),
        );

        if (!$initialTestSuiteProcess->isSuccessful()) {
            throw InitialTestsFailed::fromProcessAndAdapter($initialTestSuiteProcess, $this->adapter);
        }

        $this->coverageChecker->checkCoverageHasBeenGenerated(
            $initialTestSuiteProcess->getCommandLine(),
            $initialTestSuiteProcess->getOutput(),
        );

        /*
         * Limit the memory used for the mutation processes based on the memory
         * used for the initial test run.
         */
        $this->memoryLimiter->limitMemory($initialTestSuiteProcess->getOutput(), $this->adapter);
    }

    /**
     * @return string[]
     */
    private function getInitialTestsPhpOptionsArray(): array
    {
        return explode(' ', (string) $this->config->getInitialTestsPhpOptions());
    }

    private function runMutationAnalysis(): void
    {
        $mutations = $this->mutationGenerator->generate(
            $this->config->mutateOnlyCoveredCode(),
            $this->getNodeIgnorers(),
        );

        $this->mutationTestingRunner->run(
            $mutations,
            $this->getFilteredExtraOptionsForMutant(),
        );
    }

    /**
     * @return NodeIgnorer[]
     */
    private function getNodeIgnorers(): array
    {
        if ($this->adapter instanceof IgnoresAdditionalNodes) {
            return $this->adapter->getNodeIgnorers();
        }

        return [];
    }

    private function getFilteredExtraOptionsForMutant(): string
    {
        if ($this->adapter instanceof ProvidesInitialRunOnlyOptions) {
            return $this->testFrameworkExtraOptionsFilter->filterForMutantProcess(
                $this->config->getTestFrameworkExtraOptions(),
                $this->adapter->getInitialRunOnlyOptions(),
            );
        }

        return $this->config->getTestFrameworkExtraOptions();
    }
}
