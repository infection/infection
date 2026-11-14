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

use function array_filter;
use function array_key_exists;
use function array_values;
use Infection\AbstractTestFramework\Coverage\TestLocation;
use Infection\AbstractTestFramework\InvalidVersion;
use Infection\AbstractTestFramework\SyntaxErrorAware;
use Infection\AbstractTestFramework\TestFrameworkAdapter;
use Infection\Config\ValueProvider\PCOVDirectoryProvider;
use Infection\Configuration\Configuration;
use Infection\Mutant\Mutant;
use Infection\Process\Factory\MutantProcessContainerFactory;
use Infection\Process\MutantProcessContainer;
use Infection\Process\Runner\InitialTestsRunner;
use Infection\TestFramework\Common\CommandLineArguments\CommandLineArguments as CommonCommandLineArguments;
use Infection\TestFramework\Common\CommandLineBuilder;
use Infection\TestFramework\Common\Throwable\InitialTestsFailed;
use Infection\TestFramework\Common\VersionParser;
use Infection\TestFramework\Contracts\CommandLineArguments;
use Infection\TestFramework\Contracts\InitialRunResults;
use Infection\TestFramework\Contracts\ShellCommandRunner;
use Infection\TestFramework\Contracts\TestFramework;
use Infection\TestFramework\Coverage\CoverageChecker;
use Infection\TestFramework\Coverage\CoverageCheckerFactory;
use Infection\TestFramework\PhpUnit\CommandLine\ArgumentsAndOptionsBuilder;
use Infection\TestFramework\PhpUnit\Config\Builder\InitialConfigBuilder;
use Infection\TestFramework\PhpUnit\Config\Builder\MutationConfigBuilder;
use Infection\TestFramework\PhpUnit\Config\InvalidPhpUnitConfiguration;
use Infection\TestFramework\PhpUnit\Log\PhpUnitLogger;
use Infection\TestFramework\PhpUnit\MemoryLimiter;
use Override;
use function Safe\preg_match;
use function sprintf;
use Symfony\Component\Process\Exception\RuntimeException as SymfonyProcessRuntimeException;
use function version_compare;
use Webmozart\Assert\Assert;

/**
 * @internal
 */
final class PhpUnitAdapter implements SyntaxErrorAware, TestFramework, TestFrameworkAdapter
{
    public const string COVERAGE_DIR = 'coverage-xml';

    private const int CONFIGURATION_VALIDATION_TIMEOUT_IN_SECONDS = 2;

    private readonly CoverageChecker $coverageChecker;

    public function __construct(
        private readonly string $testFrameworkExecutable,
        private readonly string $tmpDir,
        private readonly string $jUnitFilePath,
        private readonly PCOVDirectoryProvider $pcovDirectoryProvider,
        private readonly InitialConfigBuilder $initialConfigBuilder,
        private readonly MutationConfigBuilder $mutationConfigBuilder,
        private readonly ArgumentsAndOptionsBuilder $argumentsAndOptionsBuilder,
        private readonly ShellCommandRunner $shellCommandRunner,
        private readonly VersionParser $versionParser,
        private readonly CommandLineBuilder $commandLineBuilder,
        private readonly string $testFrameworkConfigPath,
        private readonly PhpUnitLogger $logger,
        CoverageCheckerFactory $coverageCheckerFactory,
        private readonly InitialTestsRunner $initialTestsRunner,
        private readonly Configuration $config,
        private readonly MutantProcessContainerFactory $processFactory,
        private readonly MemoryLimiter $memoryLimiter,
        private ?string $version = null,
    ) {
        $this->coverageChecker = $coverageCheckerFactory->create($this);
    }

    #[Override]
    public function checkRequirements(): void
    {
        // TODO: check supported version

        $this->coverageChecker->checkCoverageRequirements();

        if ($this->config->skipInitialTests) {
            $this->logger->logSkippingInitialTests();
            $this->coverageChecker->checkCoverageExists();
        }
    }

    /**
     * @throws InitialTestsFailed
     * @throws InvalidPhpUnitConfiguration
     * @throws InvalidVersion
     * @throws SymfonyProcessRuntimeException
     */
    #[Override]
    public function executeInitialRun(): InitialRunResults
    {
        $initialTestSuiteProcess = $this->initialTestsRunner->run(
            $this->createInitialCommand(
                $this->config->testFrameworkArguments,
                $this->config->initialTestsPhpOptions,
                $this->config->skipCoverage,
            ),
            $this->config->skipCoverage,
        );

        if (!$initialTestSuiteProcess->isSuccessful()) {
            throw InitialTestsFailed::fromProcess(
                $initialTestSuiteProcess,
                $this->getName(),
                'tests',
                $this->getInitialTestsFailRecommendations(
                    $initialTestSuiteProcess->getCommandLine(),
                ),
            );
        }

        $output = $initialTestSuiteProcess->getOutput();

        $this->coverageChecker->checkCoverageHasBeenGenerated(
            $initialTestSuiteProcess->getCommandLine(),
            $output,
        );

        $memoryUsage = $this->getMemoryUsed($output);

        $results = new InitialRunResults(
            output: $output,
            memoryUsage: $memoryUsage === -1. ? null : $memoryUsage,
        );

        $this->memoryLimiter->recordInitialRunMemoryUsage($results->memoryUsage);

        return $results;
    }

    /**
     * @throws InvalidVersion
     */
    #[Override]
    public function test(Mutant $mutant): MutantProcessContainer
    {
        return $this->processFactory->create(
            $this,
            $mutant,
            $this->createMutantCommand(
                $mutant->getTests(),
                $mutant->getFilePath(),
                $mutant->getMutation()->getHash(),
                $mutant->getMutation()->getOriginalFilePath(),
                $this->config->testFrameworkArguments,
            ),
        );
    }

    public function hasJUnitReport(): bool
    {
        return true;
    }

    /**
     * Validates the original configuration when supported, then returns the initial run command.
     *
     * @param string[] $phpExtraArgs
     *
     * @throws InvalidPhpUnitConfiguration
     * @throws InvalidVersion
     *
     * @return string[]
     */
    #[Override]
    public function getInitialTestRunCommandLine(
        string $extraOptions,
        array $phpExtraArgs,
        bool $skipCoverage,
    ): array {
        // Legacy callers may supply empty elements from splitting raw PHP options.
        $phpExtraArgs = array_values(array_filter($phpExtraArgs, static fn (string $argument): bool => $argument !== ''));

        return $this->createInitialCommand(
            CommonCommandLineArguments::fromString($extraOptions),
            CommonCommandLineArguments::fromTokens($phpExtraArgs),
            $skipCoverage,
        );
    }

    /**
     * @throws InvalidVersion
     */
    public function getMutantCommandLine(
        array $coverageTests,
        string $mutatedFilePath,
        string $mutationHash,
        string $mutationOriginalFilePath,
        string $extraOptions,
    ): array {
        return $this->createMutantCommand(
            $coverageTests,
            $mutatedFilePath,
            $mutationHash,
            $mutationOriginalFilePath,
            CommonCommandLineArguments::fromString($extraOptions),
        );
    }

    public function getVersion(): string
    {
        return $this->version ??= $this->versionParser->parse(
            $this->shellCommandRunner->mustRun(
                $this->commandLineBuilder->build(
                    $this->testFrameworkExecutable,
                    CommonCommandLineArguments::fromTokens([]),
                    CommonCommandLineArguments::fromTokens(['--version']),
                ),
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
        // ordering by defects needs the test run history, which the initial run deactivates. PHPUnit ignored that combination silently until 13.3 turned it into a warning https://github.com/sebastianbergmann/phpunit/blob/13.3.0/src/TextUI/Application.php
        if (version_compare($version, '13.0', '>=')) {
            return false;
        }

        return
            version_compare($version, '10.5.48', '>=') && version_compare($version, '11.0', '<')
            || version_compare($version, '11.5.27', '>=') && version_compare($version, '12.0', '<')
            || version_compare($version, '12.2.7', '>=');
    }

    /**
     * @throws InvalidPhpUnitConfiguration
     * @throws InvalidVersion
     *
     * @return list<string>
     */
    private function createInitialCommand(
        CommandLineArguments $arguments,
        CommandLineArguments $phpExtraArgs,
        bool $skipCoverage,
    ): array {
        $this->validateConfigurationIfSupported($phpExtraArgs);

        $arguments = $this->argumentsAndOptionsBuilder->buildForInitialTestsRun(
            $this->initialConfigBuilder->build($this->getVersion()),
            $arguments,
        );

        if ($skipCoverage === false) {
            if (self::supportsExcludingSourceFromCoverage($this->getVersion())) {
                $arguments = $arguments->append(['--exclude-source-from-xml-coverage']);
            }

            $arguments = $arguments->append([
                '--coverage-xml=' . $this->tmpDir . '/' . self::COVERAGE_DIR,
                '--log-junit=' . $this->jUnitFilePath,
            ]);

            // PCOV may require an adjusted `pcov.directory` setting to include
            // all target source code in the coverage report.
            if ($this->pcovDirectoryProvider->shouldProvide()) {
                $phpExtraArgs = $phpExtraArgs->append([
                    '-d',
                    sprintf('pcov.directory=%s', $this->pcovDirectoryProvider->getDirectory()),
                ]);
            }
        }

        return $this->getCommandLine($phpExtraArgs, $arguments);
    }

    /**
     * @param TestLocation[] $coverageTests
     *
     * @throws InvalidVersion
     *
     * @return list<string>
     */
    private function createMutantCommand(
        array $coverageTests,
        string $mutatedFilePath,
        string $mutationHash,
        string $mutationOriginalFilePath,
        CommandLineArguments $arguments,
    ): array {
        return $this->getCommandLine(
            CommonCommandLineArguments::fromTokens($this->memoryLimiter->getPhpExtraArguments()),
            $this->argumentsAndOptionsBuilder->buildForMutant(
                $this->mutationConfigBuilder->build(
                    $coverageTests,
                    $mutatedFilePath,
                    $mutationHash,
                    $mutationOriginalFilePath,
                    $this->getVersion(),
                ),
                $arguments,
                $coverageTests,
                $this->getVersion(),
            ),
        );
    }

    private function getMemoryUsed(string $output): float
    {
        if (preg_match('/Memory: (\d+(?:\.\d+))\s*MB/', $output, $match) === 1) {
            Assert::keyExists($match, 1);

            return (float) $match[1];
        }

        return -1.;
    }

    /**
     * @return list<string>
     */
    private function getCommandLine(CommandLineArguments $phpExtraArgs, CommandLineArguments $testFrameworkArgs): array
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

    /**
     * @throws InvalidPhpUnitConfiguration
     * @throws InvalidVersion
     */
    private function validateConfigurationIfSupported(CommandLineArguments $phpExtraArgs): void
    {
        // PHPUnit 13.2 introduced validation against its bundled schema, without
        // resolving the schema URL declared in the configuration.
        // https://github.com/sebastianbergmann/phpunit/commit/e85b58eda9e750763f3b26fb416612d5931299aa
        if (!$this->isPhpUnit132OrHigher($this->getVersion())) {
            return;
        }

        try {
            $this->shellCommandRunner->mustRun(
                $this->commandLineBuilder->build(
                    $this->testFrameworkExecutable,
                    $phpExtraArgs,
                    CommonCommandLineArguments::fromTokens([
                        '--configuration',
                        $this->testFrameworkConfigPath,
                        '--validate-configuration',
                    ]),
                ),
                timeout: self::CONFIGURATION_VALIDATION_TIMEOUT_IN_SECONDS,
            );
        } catch (SymfonyProcessRuntimeException $processFailed) {
            throw new InvalidPhpUnitConfiguration(
                sprintf(
                    'Could not validate the PHPUnit configuration file "%s".',
                    $this->testFrameworkConfigPath,
                ),
                (int) $processFailed->getCode(),
                previous: $processFailed,
            );
        }
    }

    private function isPhpUnit132OrHigher(string $testFrameworkVersion): bool
    {
        static $versions = [];

        if (!array_key_exists($testFrameworkVersion, $versions)) {
            $versions[$testFrameworkVersion] = version_compare($testFrameworkVersion, '13.2.0', '>=');
        }

        return $versions[$testFrameworkVersion];
    }
}
