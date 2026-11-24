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

use function array_key_exists;
use function explode;
use function implode;
use Infection\AbstractTestFramework\InvalidVersion;
use Infection\Config\ValueProvider\PCOVDirectoryProvider;
use Infection\Configuration\Configuration;
use Infection\Mutant\Mutant;
use Infection\Process\MutantProcessContainer;
use Infection\TestFramework\Common\CommandLineBuilder;
use Infection\TestFramework\Common\InitialTestsRunner;
use Infection\TestFramework\Common\VersionParser;
use Infection\TestFramework\Contracts\InitialRunResults;
use Infection\TestFramework\Contracts\ShellCommandRunner;
use Infection\TestFramework\Contracts\TestFramework;
use Infection\TestFramework\Contracts\Throwable\InitialTestsFailed as InitialTestsFailedContract;
use Infection\TestFramework\Coverage\CoverageChecker;
use Infection\TestFramework\Coverage\CoverageCheckerFactory;
use Infection\TestFramework\PhpUnit\CommandLine\ArgumentsAndOptionsBuilder;
use Infection\TestFramework\PhpUnit\Config\Builder\InitialConfigBuilder;
use Infection\TestFramework\PhpUnit\Config\InvalidPhpUnitConfiguration;
use Infection\TestFramework\PhpUnit\Log\PhpUnitLogger;
use Infection\TestFramework\PhpUnit\MemoryLimiter;
use Infection\TestFramework\PhpUnit\Mutant\PHPUnitMutantProcessFactory;
use Infection\TestFramework\TestFrameworkExtraOptionsFilter;
use Override;
use function Safe\preg_match;
use function sprintf;
use Symfony\Component\Process\Exception\RuntimeException as SymfonyProcessRuntimeException;
use function trim;
use function version_compare;
use Webmozart\Assert\Assert;

/**
 * @internal
 */
final class PhpUnitAdapter implements TestFramework
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
        private readonly ArgumentsAndOptionsBuilder $argumentsAndOptionsBuilder,
        private readonly ShellCommandRunner $shellCommandRunner,
        private readonly VersionParser $versionParser,
        private readonly CommandLineBuilder $commandLineBuilder,
        private readonly string $testFrameworkConfigPath,
        private readonly PhpUnitLogger $logger,
        CoverageCheckerFactory $coverageCheckerFactory,
        private readonly InitialTestsRunner $initialTestsRunner,
        private readonly Configuration $config,
        private readonly PHPUnitMutantProcessFactory $processFactory,
        private readonly TestFrameworkExtraOptionsFilter $testFrameworkExtraOptionsFilter,
        private readonly MemoryLimiter $memoryLimiter,
        private ?string $version = null,
    ) {
        $this->coverageChecker = $coverageCheckerFactory->create($this->getName(), true);
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
     * @throws InitialTestsFailedContract
     * @throws InvalidVersion
     * @throws InvalidPhpUnitConfiguration
     */
    #[Override]
    public function executeInitialRun(): InitialRunResults
    {
        // TODO: rename ::getInitialTestsRunCommandLine() to ::getInitialRunCommandLine() separately.
        $command = $this->getInitialRunCommandLine(
            $this->config->testFrameworkExtraOptions,
            explode(
                ' ',
                (string) $this->config->initialTestsPhpOptions,
            ),
            $this->config->skipCoverage,
        );

        $result = $this->initialTestsRunner->run(
            $this,
            $command,
            skipCoverage: $this->config->skipCoverage,
            buildFailureRecommendations: $this->getInitialTestsFailRecommendations(...),
        );
        $output = $result->stdout;

        $this->coverageChecker->checkCoverageHasBeenGenerated(
            $result->commandLine,
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

    #[Override]
    public function test(Mutant $mutant): MutantProcessContainer
    {
        $extraOptions = $this->testFrameworkExtraOptionsFilter->filterForMutantProcess(
            $this->config->testFrameworkExtraOptions,
            $this->getInitialRunOnlyOptions(),
            ['--warn-when-php-is-not-configured-for-development'],
        );

        $version = $this->getVersion();

        return MutantProcessContainer::from(
            $mutant,
            fn () => $this->processFactory->create($mutant, $version, $extraOptions),
        );
    }

    public function hasJUnitReport(): bool
    {
        return true;
    }

    public function getName(): string
    {
        return 'PHPUnit';
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

    public function getVersion(): string
    {
        if ($this->version !== null) {
            return $this->version;
        }

        try {
            $this->version = $this->versionParser->parse($this->getVersionOutput());
        } catch (SymfonyProcessRuntimeException $exception) {
            $this->logger->logVersionDetectionFailure($exception);

            $this->version = TestFramework::UNKNOWN_VERSION;
        }

        return $this->version;
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
    private function getInitialRunCommandLine(
        string $extraOptions,
        array $phpExtraArgs,
        bool $skipCoverage,
    ): array {
        $this->validateConfigurationIfSupported($phpExtraArgs);

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

            // PCOV may require an adjusted `pcov.directory` setting to include
            // all target source code in the coverage report.
            if ($this->pcovDirectoryProvider->shouldProvide()) {
                $phpExtraArgs[] = '-d';
                $phpExtraArgs[] = sprintf(
                    'pcov.directory=%s',
                    $this->pcovDirectoryProvider->getDirectory(),
                );
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

    private function getInitialTestsFailRecommendations(string $commandLine): string
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
    private static function supportsExcludingSourceFromCoverage(string $version): bool
    {
        return version_compare($version, '12.5', '>=');
    }

    /**
     * @return string[]
     */
    private function getInitialRunOnlyOptions(): array
    {
        return ['--configuration', '--filter', '--testsuite'];
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

    /**
     * @param string[] $phpExtraArgs
     *
     * @throws InvalidPhpUnitConfiguration
     * @throws InvalidVersion
     */
    private function validateConfigurationIfSupported(array $phpExtraArgs): void
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
                    [
                        '--configuration',
                        $this->testFrameworkConfigPath,
                        '--validate-configuration',
                    ],
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

    /**
     * @throws SymfonyProcessRuntimeException
     */
    private function getVersionOutput(): string
    {
        return $this->shellCommandRunner->mustRun(
            $this->commandLineBuilder->build(
                $this->testFrameworkExecutable,
                [],
                ['--version'],
            ),
        );
    }
}
