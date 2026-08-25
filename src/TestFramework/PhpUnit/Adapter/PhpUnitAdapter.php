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

namespace Infection\TestFramework\PhpUnit\Adapter;

use Closure;
use DuoClock\DuoClock;
use function escapeshellarg;
use function explode;
use function implode;
use Infection\AbstractTestFramework\Coverage\TestLocation;
use Infection\AbstractTestFramework\TestFrameworkAdapter;
use Infection\Config\ValueProvider\PCOVDirectoryProvider;
use Infection\Console\ConsoleOutput;
use Infection\Process\DryRunProcess;
use Infection\Process\Factory\MutantProcessContainerFactory;
use Infection\Process\ShellCommandLineExecutor;
use Infection\TestFramework\CommandLineArgumentsAndOptionsBuilder;
use Infection\TestFramework\Common\CommandLineBuilder;
use Infection\TestFramework\Common\InitialRunProcessFactory;
use Infection\TestFramework\Common\InitialTestsFailed;
use Infection\TestFramework\Common\MutantProcess;
use Infection\TestFramework\Common\MutantProcessContainer;
use Infection\TestFramework\Common\MutantProcessDetectionStatusResolver;
use Infection\TestFramework\Common\VersionParser;
use Infection\TestFramework\Config\InitialConfigBuilder;
use Infection\TestFramework\Config\MutationConfigBuilder;
use Infection\TestFramework\Contracts\DetectionStatus;
use Infection\TestFramework\Contracts\InitialTestsResult;
use Infection\TestFramework\Contracts\Mutant;
use Infection\TestFramework\Contracts\MutantEvaluationPipe;
use Infection\TestFramework\Contracts\TestFramework;
use Infection\TestFramework\Coverage\CoverageChecker;
use Infection\TestFramework\TestFrameworkExtraOptionsFilter;
use function min;
use Override;
use function Safe\preg_match;
use function sprintf;
use Symfony\Component\Process\Process;
use function trim;
use function version_compare;

/**
 * @internal
 */
final class PhpUnitAdapter implements MutantProcessDetectionStatusResolver, TestFramework, TestFrameworkAdapter
{
    final public const string COVERAGE_DIR = 'coverage-xml';

    private const array INITIAL_RUN_ONLY_OPTIONS = ['--configuration', '--filter', '--testsuite'];

    private const int PROCESS_MIN_ERROR_CODE = 100;

    private float $initialRunMemoryUsage = -1.;

    public function __construct(
        private readonly string $testFrameworkExecutable,
        private readonly string $tmpDir,
        private readonly string $jUnitFilePath,
        private readonly PCOVDirectoryProvider $pcovDirectoryProvider,
        private readonly InitialConfigBuilder $initialConfigBuilder,
        private readonly MutationConfigBuilder $mutationConfigBuilder,
        private readonly CommandLineArgumentsAndOptionsBuilder $argumentsAndOptionsBuilder,
        private readonly ShellCommandLineExecutor $shellCommandLineExecutor,
        private readonly VersionParser $versionParser,
        private readonly CommandLineBuilder $commandLineBuilder,
        private readonly ConsoleOutput $consoleOutput,
        private readonly CoverageChecker $coverageChecker,
        private readonly InitialRunProcessFactory $initialRunProcessFactory,
        private readonly bool $skipCoverage,
        private readonly bool $skipInitialTests,
        private readonly ?string $initialTestsPhpOptions,
        private readonly string $testFrameworkExtraOptions,
        private readonly float $processTimeout,
        private readonly bool $isDryRun,
        private readonly TestFrameworkExtraOptionsFilter $testFrameworkExtraOptionsFilter,
        private readonly MemoryLimiter $memoryLimiter,
        private readonly DuoClock $clock,
        private ?string $version = null,
    ) {
    }

    public function hasJUnitReport(): bool
    {
        return true;
    }

    /**
     * Returns array of arguments to pass them into the Initial Run Process
     *
     * @param string[] $phpExtraArgs
     *
     * @return string[]
     */
    #[Override]
    public function getInitialTestRunCommandLine(
        string $extraOptions,
        array $phpExtraArgs,
        bool $skipCoverage,
    ): array {
        if ($skipCoverage === false) {
            $generatedOptions = [];

            if (self::supportsExcludingSourceFromCoverage($this->getVersion())) {
                $generatedOptions[] = '--exclude-source-from-xml-coverage';
            }

            $generatedOptions[] = '--coverage-xml=' . $this->tmpDir . '/' . self::COVERAGE_DIR;
            $generatedOptions[] = '--log-junit=' . $this->jUnitFilePath; // escapeshellarg() is done up the stack in ArgumentsAndOptionsBuilder

            $extraOptions = trim(
                sprintf(
                    '%s %s',
                    $extraOptions,
                    implode(' ', $generatedOptions),
                ),
            );

            if ($this->pcovDirectoryProvider->shallProvide()) {
                $phpExtraArgs[] = '-d';
                $phpExtraArgs[] = sprintf('pcov.directory=%s', escapeshellarg($this->pcovDirectoryProvider->getDirectory()));
            }
        }

        return $this->getCommandLine(
            $phpExtraArgs,
            $this->argumentsAndOptionsBuilder->buildForInitialTestsRun(
                $this->initialConfigBuilder->build($this->getVersion()),
                $extraOptions,
            ),
        );
    }

    /**
     * @param TestLocation[] $coverageTests
     */
    public function getMutantCommandLine(
        array $coverageTests,
        string $mutatedFilePath,
        string $mutationHash,
        string $mutationOriginalFilePath,
        string $extraOptions,
    ): array {
        return $this->getCommandLine(
            $this->getMutantPhpExtraArgs(),
            $this->argumentsAndOptionsBuilder->buildForMutant(
                $this->mutationConfigBuilder->build(
                    $coverageTests,
                    $mutatedFilePath,
                    $mutationHash,
                    $mutationOriginalFilePath,
                    $this->getVersion(),
                ),
                $extraOptions,
                $coverageTests,
                $this->getVersion(),
            ),
        );
    }

    public function getVersion(): string
    {
        return $this->version ??= $this->versionParser->parse(
            $this->shellCommandLineExecutor->execute(
                $this->commandLineBuilder->build($this->testFrameworkExecutable, [], ['--version']),
            ),
        );
    }

    public function testsPass(string $output): bool
    {
        if (preg_match('/failures!/i', $output) === 1) {
            return false;
        }

        if (preg_match('/errors!/i', $output) === 1) {
            return false;
        }

        // OK (XX tests, YY assertions)
        $isOk = preg_match('/OK\s\(/', $output) === 1;

        // "OK, but incomplete, skipped, or risky tests!"
        $isOkWithInfo = preg_match('/OK\s?,/', $output) === 1;

        // "Warnings!" - e.g. when deprecated functions are used, but tests pass
        $isWarning = preg_match('/warnings!/i', $output) === 1;

        // "No tests executed!" - e.g. when --filter option contains too large regular expression
        $isNoTestsExecuted = preg_match('/No tests executed!/i', $output) === 1;

        return $isOk || $isOkWithInfo || $isWarning || $isNoTestsExecuted;
    }

    public function isSyntaxError(string $output): bool
    {
        return preg_match('/ParseError: syntax error/i', $output) === 1;
    }

    public function getName(): string
    {
        return 'PHPUnit';
    }

    public function checkRequirements(): void
    {
        // TODO: check supported version

        if ($this->skipInitialTests) {
            $this->consoleOutput->logSkippingInitialTests();
            $this->coverageChecker->checkCoverageExists();
        }
    }

    public function executeInitialRun(?Closure $onProgress = null): InitialTestsResult
    {
        $initialTestSuiteProcess = $this->initialRunProcessFactory->create(
            $this->getInitialTestRunCommandLine(
                $this->testFrameworkExtraOptions,
                explode(' ', (string) $this->initialTestsPhpOptions),
                $this->skipCoverage,
            ),
            !$this->skipCoverage,
        );

        $initialTestSuiteProcess->run(static function (string $type) use ($initialTestSuiteProcess, $onProgress): void {
            if ($type === Process::ERR) {
                // Infection forces PHPUnit's `stderr` configuration to false, so stderr is not
                // expected test-run output. Stop immediately on bootstrap or configuration errors
                // instead of waiting for the rest of the suite. This is PHPUnit-specific: tools
                // such as PHPStan legitimately use stderr for non-error output.
                $initialTestSuiteProcess->stop();
            }

            $onProgress?->__invoke();
        });

        if (!$initialTestSuiteProcess->isSuccessful()) {
            throw InitialTestsFailed::fromProcess(
                $initialTestSuiteProcess,
                $this->getName(),
                $this->getInitialTestsFailRecommendations($initialTestSuiteProcess->getCommandLine()),
            );
        }

        $output = $initialTestSuiteProcess->getOutput();

        $this->initialRunMemoryUsage = self::retrieveMemoryUsed($output);

        $this->coverageChecker->checkCoverageHasBeenGenerated(
            $initialTestSuiteProcess->getCommandLine(),
            $output,
        );

        return new InitialTestsResult($output);
    }

    public function test(Mutant $mutant): MutantEvaluationPipe
    {
        return MutantProcessContainer::from(
            $mutant,
            fn (): MutantProcess => $this->createMutantProcess($mutant),
        );
    }

    #[Override]
    public function getInitialTestsFailRecommendations(string $commandLine): string
    {
        $recommendations = $this->createInitialTestsFailRecommendations($commandLine);

        if (self::supportsExecutionOrderDefectsRandom($this->getVersion())) {
            $recommendations = sprintf(
                "%s\n\n%s\n\n%s",
                "Infection runs the test suite in a RANDOM order. Make sure your tests do not have hidden dependencies.\n\n"
                . 'You can add these attributes to `phpunit.xml` to check it: <phpunit executionOrder="defects,random" resolveDependencies="true" ...',
                'If you don\'t want to let Infection run tests in a random order, set the `executionOrder` to some value, for example <phpunit executionOrder="default"',
                $this->createInitialTestsFailRecommendations($commandLine),
            );
        } elseif (version_compare($this->getVersion(), '7.2', '>=')) {
            $recommendations = sprintf(
                "%s\n\n%s\n\n%s",
                "Infection runs the test suite in a RANDOM order. Make sure your tests do not have hidden dependencies.\n\n"
                . 'You can add these attributes to `phpunit.xml` to check it: <phpunit executionOrder="random" resolveDependencies="true" ...',
                'If you don\'t want to let Infection run tests in a random order, set the `executionOrder` to some value, for example <phpunit executionOrder="default"',
                $this->createInitialTestsFailRecommendations($commandLine),
            );
        }

        return $recommendations;
    }

    /**
     * As of PHPUnit 12.5, the `--exclude-source-from-xml-coverage` is available which removes the `source` element from the XML report which contained the list of tokens of the source code file.
     */
    public static function supportsExcludingSourceFromCoverage(string $version): bool
    {
        return version_compare($version, '12.5', '>=');
    }

    public static function supportsExecutionOrderDefectsRandom(string $version): bool
    {
        return
            version_compare($version, '10.5.48', '>=') && version_compare($version, '11.0', '<')
            || version_compare($version, '11.5.27', '>=') && version_compare($version, '12.0', '<')
            || version_compare($version, '12.2.7', '>=')
        ;
    }

    public function resolve(
        string $stdout,
        string $stderr,
        int $exitCode,
        bool $timedOut,
    ): DetectionStatus {
        if ($timedOut) {
            return DetectionStatus::TIMED_OUT;
        }

        if ($exitCode > self::PROCESS_MIN_ERROR_CODE) {
            // See \Symfony\Component\Process\Process::$exitCodes
            return DetectionStatus::ERROR;
        }

        if ($exitCode === 0 && $this->testsPass($stdout)) {
            return DetectionStatus::ESCAPED;
        }

        if ($this->isSyntaxError($stdout)) {
            return DetectionStatus::SYNTAX_ERROR;
        }

        return DetectionStatus::KILLED_BY_TESTS;
    }

    private function createMutantProcess(Mutant $mutant): MutantProcess
    {
        // getNominalTestExecutionTime() returns the time the test-suite requires to run the test, excluding process creation and test-framework bootstrapping.
        $timeout = min(
            MutantProcessContainerFactory::TEST_FRAMEWORK_BOOTSTRAP_THRESHOLD + (MutantProcessContainerFactory::TIMEOUT_FACTOR * $mutant->getNominalTestExecutionTime()),
            $this->processTimeout,
        );

        $process = new Process(
            command: $this->getMutantCommandLine(
                $mutant->getTests(),
                $mutant->getFilePath(),
                $mutant->getId(),
                $mutant->getOriginalFilePath(),
                $this->testFrameworkExtraOptionsFilter->filterForMutantProcess(
                    $this->testFrameworkExtraOptions,
                    self::INITIAL_RUN_ONLY_OPTIONS,
                ),
            ),
            timeout: $timeout,
        );

        if ($this->isDryRun) {
            $process = DryRunProcess::fromProcess($process);
        }

        return new MutantProcess(
            $process,
            $this,
            $this->clock,
        );
    }

    /** @return list<string> */
    private function getMutantPhpExtraArgs(): array
    {
        return $this->memoryLimiter->getPhpExtraArguments($this->initialRunMemoryUsage);
    }

    /**
     * @param string[] $phpExtraArgs
     * @param string[] $testFrameworkArgs
     *
     * @return string[]
     */
    private function getCommandLine(array $phpExtraArgs, array $testFrameworkArgs): array
    {
        return $this->commandLineBuilder->build(
            $this->testFrameworkExecutable,
            $phpExtraArgs,
            $testFrameworkArgs,
        );
    }

    private function createInitialTestsFailRecommendations(string $commandLine): string
    {
        return sprintf('Check the executed command to identify the problem: %s', $commandLine);
    }

    private static function retrieveMemoryUsed(string $output): float
    {
        if (preg_match('/Memory: (\d+(?:\.\d+))\s*MB/', $output, $match) === 1) {
            return (float) $match[1];
        }

        return -1.;
    }
}
