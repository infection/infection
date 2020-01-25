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
use Infection\Process\Builder\SubscriberBuilder;
use Infection\Process\Coverage\CoverageRequirementChecker;
use Infection\Process\Runner\InitialTestsFailed;
use Infection\Process\Runner\InitialTestsRunner;
use Infection\Process\Runner\MutationTestingRunner;
use Infection\Process\Runner\TestRunConstraintChecker;
use Infection\Resource\Limiter\MemoryLimiter;
use Infection\TestFramework\Coverage\CoverageDoesNotExistException;
use Infection\TestFramework\Coverage\XmlReport\PhpUnitXmlCoverageFactory;
use Infection\TestFramework\HasExtraNodeVisitors;
use const PHP_EOL;
use function Safe\sprintf;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

/**
 * @internal
 */
final class Engine
{
    private $coverageChecker;
    private $config;
    private $fileSystem;
    private $adapter;
    private $subscriberBuilder;
    private $eventDispatcher;
    private $initialTestsRunner;
    private $memoryLimitApplier;
    private $mutationGenerator;
    private $mutationTestingRunner;
    private $constraintChecker;
    private $consoleOutput;
    private $metricsCalculator;

    public function __construct(
        CoverageRequirementChecker $coverageChecker,
        Configuration $config,
        Filesystem $fileSystem,
        TestFrameworkAdapter $adapter,
        SubscriberBuilder $subscriberBuilder,
        EventDispatcher $eventDispatcher,
        InitialTestsRunner $initialTestsRunner,
        MemoryLimiter $memoryLimitApplier,
        MutationGenerator $mutationGenerator,
        MutationTestingRunner $mutationTestingRunner,
        TestRunConstraintChecker $constraintChecker,
        ConsoleOutput $consoleOutput,
        MetricsCalculator $metricsCalculator
    ) {
        $this->coverageChecker = $coverageChecker;
        $this->config = $config;
        $this->fileSystem = $fileSystem;
        $this->adapter = $adapter;
        $this->subscriberBuilder = $subscriberBuilder;
        $this->eventDispatcher = $eventDispatcher;
        $this->initialTestsRunner = $initialTestsRunner;
        $this->memoryLimitApplier = $memoryLimitApplier;
        $this->mutationGenerator = $mutationGenerator;
        $this->mutationTestingRunner = $mutationTestingRunner;
        $this->constraintChecker = $constraintChecker;
        $this->consoleOutput = $consoleOutput;
        $this->metricsCalculator = $metricsCalculator;
    }

    public function execute(int $threads): bool
    {
        $this->runInitialTestSuite();
        $this->runMutationAnalysis($threads);

        return $this->checkMetrics();
    }

    private function runInitialTestSuite(): void
    {
        $initialTestSuitProcess = $this->initialTestsRunner->run(
            $this->config->getTestFrameworkExtraOptions()->getForInitialProcess(),
            $this->config->shouldSkipCoverage(),
            explode(' ', (string) $this->config->getInitialTestsPhpOptions())
        );

        if (!$initialTestSuitProcess->isSuccessful()) {
            throw InitialTestsFailed::fromProcessAndAdapter($initialTestSuitProcess, $this->adapter);
        }

        $this->assertCodeCoverageExists($initialTestSuitProcess, $this->config->getTestFramework());

        $this->memoryLimitApplier->applyMemoryLimitFromProcess($initialTestSuitProcess, $this->adapter);
    }

    private function runMutationAnalysis(int $threads): void
    {
        $mutations = $this->mutationGenerator->generate(
            $this->config->mutateOnlyCoveredCode(),
            $this->adapter instanceof HasExtraNodeVisitors
                ? $this->adapter->getMutationsCollectionNodeVisitors()
                : []
        );

        $this->mutationTestingRunner->run(
            $mutations,
            $threads,
            $this->config->getTestFrameworkExtraOptions()->getForMutantProcess()
        );
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

    private function assertCodeCoverageExists(Process $initialTestsProcess, string $testFrameworkKey): void
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
