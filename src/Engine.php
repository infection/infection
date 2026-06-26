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

use Infection\Configuration\Configuration;
use Infection\Console\ConsoleOutput;
use Infection\Event\EventDispatcher\EventDispatcher;
use Infection\Event\Events\Application\ApplicationExecutionWasFinished;
use Infection\Metrics\MaxTimeoutCountReached;
use Infection\Metrics\MaxTimeoutsChecker;
use Infection\Metrics\MetricsCalculator;
use Infection\Metrics\MinMsiChecker;
use Infection\Metrics\MinMsiCheckFailed;
use Infection\Mutation\MutationGenerator;
use Infection\PhpParser\UnparsableFile;
use Infection\Process\Runner\InitialStaticAnalysisRunFailed;
use Infection\Process\Runner\InitialTestsFailed;
use Infection\Process\Runner\MutationTestingRunner;
use Infection\Resource\Memory\MemoryLimiter;
use Infection\Source\Exception\NoSourceFound;
use Infection\TestFramework\Contracts\InitialRunResults;
use Infection\TestFramework\Contracts\TestFramework;
use Infection\TestFramework\Coverage\JUnit\TestNotFound;
use Infection\TestFramework\Coverage\Locator\Throwable\NoReportFound;
use Infection\TestFramework\Coverage\Locator\Throwable\ReportLocationThrowable;
use Infection\TestFramework\Coverage\Locator\Throwable\TooManyReportsFound;
use Infection\TestFramework\Coverage\XmlReport\InvalidCoverage;

/**
 * @internal
 */
final readonly class Engine
{
    public function __construct(
        private Configuration $config,
        private TestFramework $testFramework,
        private ?TestFramework $staticAnalysisTestFramework,
        private EventDispatcher $eventDispatcher,
        private MemoryLimiter $memoryLimiter,
        private MutationGenerator $mutationGenerator,
        private MutationTestingRunner $mutationTestingRunner,
        private MinMsiChecker $minMsiChecker,
        private MaxTimeoutsChecker $maxTimeoutsChecker,
        private ConsoleOutput $consoleOutput,
        private MetricsCalculator $metricsCalculator,
    ) {
    }

    /**
     * @throws InitialTestsFailed
     * @throws InitialStaticAnalysisRunFailed
     * @throws MinMsiCheckFailed
     * @throws MaxTimeoutCountReached
     * @throws UnparsableFile
     * @throws InvalidCoverage
     * @throws NoSourceFound
     * @throws NoReportFound
     * @throws TooManyReportsFound
     * @throws ReportLocationThrowable
     * @throws TestNotFound
     */
    public function execute(): void
    {
        $initialRunResults = $this->runInitialTestSuite();
        $this->runInitialStaticAnalysis();

        /*
         * Limit the memory used for the mutation processes based on the memory
         * used for the initial test run.
         * This is done AFTER static analysis to avoid restricting PHPStan's memory.
         */
        $this->memoryLimiter->limitMemory($initialRunResults);

        $this->runMutationAnalysis();

        try {
            $this->maxTimeoutsChecker->checkTimeouts(
                $this->metricsCalculator->getTimedOutCount(),
            );

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

    private function runInitialTestSuite(): ?InitialRunResults
    {
        $this->testFramework->checkRequirements();

        if ($this->config->skipInitialTests) {
            return null;
        }

        return $this->testFramework->executeInitialRun();
    }

    private function runInitialStaticAnalysis(): ?InitialRunResults
    {
        $this->staticAnalysisTestFramework?->checkRequirements();

        if ($this->config->skipInitialTests) {
            return null;
        }

        $this->staticAnalysisTestFramework?->executeInitialRun();

        // TODO: return the result!

        return null;
    }

    /**
     * @throws UnparsableFile
     * @throws InvalidCoverage
     * @throws NoSourceFound
     * @throws NoReportFound
     * @throws TooManyReportsFound
     * @throws ReportLocationThrowable
     * @throws TestNotFound
     */
    private function runMutationAnalysis(): void
    {
        $mutations = $this->mutationGenerator->generate(
            // TODO: inject it in the constructor instead
            $this->config->mutateOnlyCoveredCode(),
        );

        $this->mutationTestingRunner->run($mutations);
    }
}
