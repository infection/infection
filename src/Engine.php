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

use function dirname;
use function explode;
use function file_exists;
use Infection\AbstractTestFramework\TestFrameworkAdapter;
use Infection\Configuration\Configuration;
use Infection\Console\ConsoleOutput;
use Infection\Event\ApplicationExecutionWasFinished;
use Infection\Event\EventDispatcher\EventDispatcher;
use Infection\Mutant\MetricsCalculator;
use Infection\Mutation\MutationGenerator;
use Infection\Process\Runner\InitialTestsFailed;
use Infection\Process\Runner\InitialTestsRunner;
use Infection\Process\Runner\MutationTestingRunner;
use Infection\Process\Runner\TestRunConstraintChecker;
use Infection\Resource\Memory\MemoryLimiter;
use Infection\TestFramework\Coverage\CoverageDoesNotExistException;
use Infection\TestFramework\Coverage\XmlReport\PhpUnitXmlCoverageFactory;
use Infection\TestFramework\IgnoresAdditionalNodes;
use Infection\TestFramework\ProvidesInitialRunOnlyOptions;
use Infection\TestFramework\TestFrameworkExtraOptionsFilter;
use const PHP_EOL;
use function Safe\sprintf;
use Symfony\Component\Process\Process;

/**
 * @internal
 */
final class Engine
{
    private $config;
    private $adapter;
    private $eventDispatcher;
    private $initialTestsRunner;
    private $memoryLimitApplier;
    private $mutationGenerator;
    private $mutationTestingRunner;
    private $constraintChecker;
    private $consoleOutput;
    private $metricsCalculator;
    private $testFrameworkExtraOptionsFilter;

    public function __construct(
        Configuration $config,
        TestFrameworkAdapter $adapter,
        EventDispatcher $eventDispatcher,
        InitialTestsRunner $initialTestsRunner,
        MemoryLimiter $memoryLimitApplier,
        MutationGenerator $mutationGenerator,
        MutationTestingRunner $mutationTestingRunner,
        TestRunConstraintChecker $constraintChecker,
        ConsoleOutput $consoleOutput,
        MetricsCalculator $metricsCalculator,
        TestFrameworkExtraOptionsFilter $testFrameworkExtraOptionsFilter
    ) {
        $this->config = $config;
        $this->adapter = $adapter;
        $this->eventDispatcher = $eventDispatcher;
        $this->initialTestsRunner = $initialTestsRunner;
        $this->memoryLimitApplier = $memoryLimitApplier;
        $this->mutationGenerator = $mutationGenerator;
        $this->mutationTestingRunner = $mutationTestingRunner;
        $this->constraintChecker = $constraintChecker;
        $this->consoleOutput = $consoleOutput;
        $this->metricsCalculator = $metricsCalculator;
        $this->testFrameworkExtraOptionsFilter = $testFrameworkExtraOptionsFilter;
    }

    public function execute(int $threads): bool
    {
        $this->runInitialTestSuite();
        $this->runMutationAnalysis($threads);

        return $this->checkMetrics();
    }

    private function runInitialTestSuite(): void
    {
        if ($this->config->shouldSkipInitialTests()) {
            $this->consoleOutput->logSkippingInitialTests();
            $this->assertCodeCoverageExists($this->config->getTestFramework());

            return;
        }

        $initialTestSuitProcess = $this->initialTestsRunner->run(
            $this->config->getTestFrameworkExtraOptions(),
            $this->config->shouldSkipCoverage(),
            explode(' ', (string) $this->config->getInitialTestsPhpOptions())
        );

        if (!$initialTestSuitProcess->isSuccessful()) {
            throw InitialTestsFailed::fromProcessAndAdapter($initialTestSuitProcess, $this->adapter);
        }

        $this->assertCodeCoverageProduced($initialTestSuitProcess, $this->config->getTestFramework());

        $this->memoryLimitApplier->applyMemoryLimitFromProcess($initialTestSuitProcess, $this->adapter);
    }

    private function runMutationAnalysis(int $threads): void
    {
        $mutations = $this->mutationGenerator->generate(
            $this->config->mutateOnlyCoveredCode(),
            $this->adapter instanceof IgnoresAdditionalNodes
                ? $this->adapter->getNodeIgnorers()
                : []
        );

        $actualExtraOptions = $this->config->getTestFrameworkExtraOptions();

        $filteredExtraOptionsForMutant = $this->adapter instanceof ProvidesInitialRunOnlyOptions
            ? $this->testFrameworkExtraOptionsFilter->filterForMutantProcess($actualExtraOptions, $this->adapter->getInitialRunOnlyOptions())
            : $actualExtraOptions;

        $this->mutationTestingRunner->run($mutations, $threads, $filteredExtraOptionsForMutant);
    }

    private function checkMetrics(): bool
    {
        if (!$this->constraintChecker->hasTestRunPassedConstraints()) {
            $this->consoleOutput->logBadMsiErrorMessage(
                $this->metricsCalculator,
                $this->constraintChecker->getMinRequiredValue(),
                $this->constraintChecker->getErrorType()
            );

            return false;
        }

        if ($this->constraintChecker->isActualOverRequired()) {
            $this->consoleOutput->logMinMsiCanGetIncreasedNotice(
                $this->metricsCalculator,
                $this->constraintChecker->getMinRequiredValue(),
                $this->constraintChecker->getActualOverRequiredType()
            );
        }

        $this->eventDispatcher->dispatch(new ApplicationExecutionWasFinished());

        return true;
    }

    private function assertCodeCoverageExists(string $testFrameworkKey): void
    {
        $coverageDir = $this->config->getCoveragePath();

        $coverageIndexFilePath = $coverageDir . '/' . PhpUnitXmlCoverageFactory::COVERAGE_INDEX_FILE_NAME;

        if (!file_exists($coverageIndexFilePath)) {
            throw CoverageDoesNotExistException::with(
                $coverageIndexFilePath,
                $testFrameworkKey,
                dirname($coverageIndexFilePath, 2)
            );
        }
    }

    private function assertCodeCoverageProduced(Process $initialTestsProcess, string $testFrameworkKey): void
    {
        $coverageDir = $this->config->getCoveragePath();

        $coverageIndexFilePath = $coverageDir . '/' . PhpUnitXmlCoverageFactory::COVERAGE_INDEX_FILE_NAME;

        $processInfo = sprintf(
            '%sCommand line: %s%sProcess Output: %s',
            PHP_EOL,
            $initialTestsProcess->getCommandLine(),
            PHP_EOL,
            $initialTestsProcess->getOutput()
        );

        if (!file_exists($coverageIndexFilePath)) {
            throw CoverageDoesNotExistException::with(
                $coverageIndexFilePath,
                $testFrameworkKey,
                dirname($coverageIndexFilePath, 2),
                $processInfo
            );
        }
    }
}
