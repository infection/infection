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

use function array_map;
use Closure;
use function explode;
use Infection\AbstractTestFramework\MemoryUsageAware;
use Infection\AbstractTestFramework\TestFrameworkAdapter;
use Infection\Configuration\Configuration;
use Infection\Console\ConsoleOutput;
use Infection\Mutant\Mutant;
use Infection\Mutant\MutantExecutionResultFactory;
use Infection\Process\DryRunProcess;
use Infection\Process\Factory\LazyMutantProcessFactory;
use Infection\Process\MutantProcess;
use Infection\Process\Runner\InitialTestsFailed;
use Infection\Process\Runner\InitialTestsRunner;
use Infection\Resource\Memory\MemoryLimiterEnvironment;
use Infection\TestFramework\Common\LazyMutantEvaluationPipe;
use Infection\TestFramework\Contracts\InitialRunResults;
use Infection\TestFramework\Contracts\MutantEvaluationPipe;
use Infection\TestFramework\Contracts\TestFramework;
use Infection\TestFramework\Coverage\CoverageChecker;
use function min;
use function sprintf;
use Symfony\Component\Process\Process;

/**
 * @deprecated This is for the compatibility layer with the old AbstractTestFramework contract. To be removed.
 *
 * @internal
 */
final class LegacyTestFrameworkBridge implements TestFramework
{
    private const int TIMEOUT_FACTOR = 5;

    private const int TEST_FRAMEWORK_BOOTSTRAP_THRESHOLD = 5;

    private const int MEMORY_LIMIT_FACTOR = 2;

    /**
     * @var list<string>
     */
    private array $mutantPhpExtraArgs = [];

    /**
     * @param list<LazyMutantProcessFactory> $mutantProcessKillerFactories
     */
    public function __construct(
        private readonly TestFrameworkAdapter $adapter,
        private readonly ConsoleOutput $consoleOutput,
        private readonly CoverageChecker $coverageChecker,
        private readonly InitialTestsRunner $initialTestsRunner,
        private readonly Configuration $config,
        private readonly TestFrameworkExtraOptionsFilter $testFrameworkExtraOptionsFilter,
        private readonly MutantExecutionResultFactory $mutantExecutionResultFactory,
        private readonly MemoryLimiterEnvironment $memoryLimiterEnvironment,
        private readonly array $mutantProcessKillerFactories,
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

        $this->configureMutantMemoryLimit($output);

        return new InitialRunResults(
            output: $output,
        );
    }

    public function test(Mutant $mutant): MutantEvaluationPipe
    {
        return new LazyMutantEvaluationPipe(
            fn () => $this->createTestProcess($mutant),
            ...array_map(
                static fn (LazyMutantProcessFactory $factory): Closure => static fn () => $factory->create($mutant),
                $this->mutantProcessKillerFactories,
            ),
        );
    }

    private function createTestProcess(Mutant $mutant): MutantProcess
    {
        // getNominalTestExecutionTime() returns the time the test-suite requires to run the test, excluding process creation and test-framework bootstrapping.
        $timeout = min(
            self::TEST_FRAMEWORK_BOOTSTRAP_THRESHOLD + (self::TIMEOUT_FACTOR * $mutant->getMutation()->getNominalTestExecutionTime()),
            $this->config->processTimeout,
        );

        // TODO: we should strive to remove this Process instantiation.
        $process = new Process(
            command: $this->getMutantCommandLine($mutant),
            timeout: $timeout,
        );

        if ($this->config->isDryRun) {
            $process = DryRunProcess::fromProcess($process);
        }

        return new MutantProcess(
            $process,
            $mutant,
            $this->mutantExecutionResultFactory,
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

    /**
     * @return string[]
     */
    private function getMutantCommandLine(Mutant $mutant): array
    {
        $arguments = [
            $mutant->getTests(),
            $mutant->getFilePath(),
            $mutant->getMutation()->getHash(),
            $mutant->getMutation()->getOriginalFilePath(),
            $this->getFilteredExtraOptionsForMutant(),
        ];

        return $this->adapter instanceof MutantPhpExtraArgsAware
            ? $this->adapter->getMutantCommandLineWithPhpExtraArgs(
                ...$arguments,
                phpExtraArgs: $this->mutantPhpExtraArgs,
            )
            : $this->adapter->getMutantCommandLine(...$arguments);
    }

    private function configureMutantMemoryLimit(string $output): void
    {
        if (!$this->adapter instanceof MemoryUsageAware
            || $this->memoryLimiterEnvironment->hasMemoryLimitSet()
        ) {
            return;
        }

        $memoryUsage = $this->adapter->getMemoryUsed($output);

        if ($memoryUsage < 0.) {
            return;
        }

        $this->mutantPhpExtraArgs = [
            '-d',
            sprintf('memory_limit=%dM', (int) (self::MEMORY_LIMIT_FACTOR * $memoryUsage)),
        ];
    }
}
