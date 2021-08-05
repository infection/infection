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

namespace Infection\Command;

use function extension_loaded;
use function file_exists;
use function implode;
use Infection\Configuration\Configuration;
use Infection\Configuration\Schema\SchemaConfigurationLoader;
use Infection\Console\ConsoleOutput;
use Infection\Console\Input\MsiParser;
use Infection\Console\IO;
use Infection\Console\LogVerbosity;
use Infection\Console\OutputFormatter\FormatterName;
use Infection\Console\XdebugHandler;
use Infection\Container;
use Infection\Engine;
use Infection\Event\ApplicationExecutionWasStarted;
use Infection\FileSystem\Locator\FileNotFound;
use Infection\FileSystem\Locator\FileOrDirectoryNotFound;
use Infection\FileSystem\Locator\Locator;
use Infection\Logger\ConsoleLogger;
use Infection\Metrics\MinMsiCheckFailed;
use Infection\Process\Runner\InitialTestsFailed;
use Infection\TestFramework\TestFrameworkTypes;
use InvalidArgumentException;
use const PHP_SAPI;
use Psr\Log\LoggerInterface;
use function Safe\sprintf;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputOption;
use function trim;

/**
 * @internal
 */
final class RunCommand extends BaseCommand
{
    /** @var string */
    private const OPTION_TEST_FRAMEWORK = 'test-framework';

    /** @var string */
    private const OPTION_TEST_FRAMEWORK_OPTIONS = 'test-framework-options';

    /** @var string */
    private const OPTION_THREADS = 'threads';

    /** @var string */
    private const OPTION_ONLY_COVERED = 'only-covered';

    /** @var string */
    private const OPTION_SHOW_MUTATIONS = 'show-mutations';

    /** @var string */
    private const OPTION_NO_PROGRESS = 'no-progress';

    /** @var string */
    private const OPTION_FORCE_PROGRESS = 'force-progress';

    /** @var string */
    private const OPTION_CONFIGURATION = 'configuration';

    /** @var string */
    private const OPTION_COVERAGE = 'coverage';

    /** @var string */
    private const OPTION_MUTATORS = 'mutators';

    /** @var string */
    private const OPTION_FILTER = 'filter';

    /** @var string */
    private const OPTION_FORMATTER = 'formatter';

    /** @var string */
    private const OPTION_GIT_DIFF_FILTER = 'git-diff-filter';

    /** @var string */
    private const OPTION_GIT_DIFF_BASE = 'git-diff-base';

    /** @var string */
    private const OPTION_LOGGER_GITHUB = 'logger-github';

    private const OPTION_USE_NOOP_MUTATORS = 'noop';

    private const OPTION_EXECUTE_ONLY_COVERING_TEST_CASES = 'only-covering-test-cases';

    /** @var string */
    private const OPTION_MIN_MSI = 'min-msi';

    /** @var string */
    private const OPTION_MIN_COVERED_MSI = 'min-covered-msi';

    /** @var string */
    private const OPTION_LOG_VERBOSITY = 'log-verbosity';

    /** @var string */
    private const OPTION_INITIAL_TESTS_PHP_OPTIONS = 'initial-tests-php-options';

    /** @var string */
    private const OPTION_SKIP_INITIAL_TESTS = 'skip-initial-tests';

    /** @var string */
    private const OPTION_IGNORE_MSI_WITH_NO_MUTATIONS = 'ignore-msi-with-no-mutations';

    /** @var string */
    private const OPTION_DEBUG = 'debug';

    /** @var string */
    private const OPTION_DRY_RUN = 'dry-run';

    protected function configure(): void
    {
        $this
            ->setName('run')
            ->setDescription('Runs the mutation testing.')
            ->addOption(
                self::OPTION_TEST_FRAMEWORK,
                null,
                InputOption::VALUE_REQUIRED,
                sprintf(
                    'Name of the Test framework to use ("%s")',
                    implode('", "', TestFrameworkTypes::TYPES)
                ),
                Container::DEFAULT_TEST_FRAMEWORK
            )
            ->addOption(
                self::OPTION_TEST_FRAMEWORK_OPTIONS,
                null,
                InputOption::VALUE_REQUIRED,
                'Options to be passed to the test framework',
                Container::DEFAULT_TEST_FRAMEWORK_EXTRA_OPTIONS
            )
            ->addOption(
                self::OPTION_THREADS,
                'j',
                InputOption::VALUE_REQUIRED,
                'Number of threads to use by the runner when executing the mutations',
                Container::DEFAULT_THREAD_COUNT
            )
            ->addOption(
                self::OPTION_ONLY_COVERED,
                null,
                InputOption::VALUE_NONE,
                'Mutate only covered by tests lines of code'
            )
            ->addOption(
                self::OPTION_SHOW_MUTATIONS,
                's',
                InputOption::VALUE_NONE,
                'Show escaped (and non-covered in verbose mode) mutations to the console'
            )
            ->addOption(
                self::OPTION_NO_PROGRESS,
                null,
                InputOption::VALUE_NONE,
                'Do not output progress bars and mutation count during progress. Automatically enabled if a CI is detected'
            )
            ->addOption(
                self::OPTION_FORCE_PROGRESS,
                null,
                InputOption::VALUE_NONE,
                'Output progress bars and mutation count during progress even if a CI is detected'
            )
            ->addOption(
                self::OPTION_CONFIGURATION,
                'c',
                InputOption::VALUE_REQUIRED,
                'Path to the configuration file to use',
                Container::DEFAULT_CONFIG_FILE
            )
            ->addOption(
                self::OPTION_COVERAGE,
                null,
                InputOption::VALUE_REQUIRED,
                'Path to existing coverage directory',
                Container::DEFAULT_EXISTING_COVERAGE_PATH
            )
            ->addOption(
                self::OPTION_MUTATORS,
                null,
                InputOption::VALUE_REQUIRED,
                sprintf('Specify particular mutators, e.g. "--%s=Plus,PublicVisibility"', self::OPTION_MUTATORS),
                Container::DEFAULT_MUTATORS_INPUT
            )
            ->addOption(
                self::OPTION_FILTER,
                null,
                InputOption::VALUE_REQUIRED,
                'Filter which files to mutate',
                Container::DEFAULT_FILTER
            )
            ->addOption(
                self::OPTION_FORMATTER,
                null,
                InputOption::VALUE_REQUIRED,
                sprintf(
                    'Name of the formatter to use ("%s")',
                    implode('", "', FormatterName::ALL)
                ),
                Container::DEFAULT_FORMATTER_NAME
            )
            ->addOption(
                self::OPTION_GIT_DIFF_FILTER,
                null,
                InputOption::VALUE_REQUIRED,
                'Filter files to mutate git `--diff-filter` options. A - only for added files, AM - for added and modified.',
                Container::DEFAULT_GIT_DIFF_FILTER
            )
            ->addOption(
                self::OPTION_GIT_DIFF_BASE,
                null,
                InputOption::VALUE_REQUIRED,
                sprintf('Base branch for `--%1$s` option. Must be used only together with `--%1$s`.', self::OPTION_GIT_DIFF_FILTER),
                Container::DEFAULT_GIT_DIFF_BASE
            )
            ->addOption(
                self::OPTION_LOGGER_GITHUB,
                null,
                InputOption::VALUE_NONE,
                'Log escaped Mutants as GitHub Annotations.',
            )
            ->addOption(
                self::OPTION_USE_NOOP_MUTATORS,
                null,
                InputOption::VALUE_NONE,
                'Use noop mutators that do not change AST. For debugging purposes.',
            )
            ->addOption(
                self::OPTION_EXECUTE_ONLY_COVERING_TEST_CASES,
                null,
                InputOption::VALUE_NONE,
                'Execute only those test cases that cover mutated line, not the whole file with covering test cases. Can dramatically speed up Mutation Testing for slow test suites. For PHPUnit / Pest it uses `--filter` option',
            )
            ->addOption(
                self::OPTION_MIN_MSI,
                null,
                InputOption::VALUE_REQUIRED,
                'Minimum Mutation Score Indicator (MSI) percentage value',
                Container::DEFAULT_MIN_MSI
            )
            ->addOption(
                self::OPTION_MIN_COVERED_MSI,
                null,
                InputOption::VALUE_REQUIRED,
                'Minimum Covered Code Mutation Score Indicator (MSI) percentage value',
                Container::DEFAULT_MIN_COVERED_MSI
            )
            ->addOption(
                self::OPTION_LOG_VERBOSITY,
                null,
                InputOption::VALUE_REQUIRED,
                '"all" - full logs format, "default" - short logs format, "none" - no logs',
                Container::DEFAULT_LOG_VERBOSITY
            )
            ->addOption(
                self::OPTION_INITIAL_TESTS_PHP_OPTIONS,
                null,
                InputOption::VALUE_REQUIRED,
                sprintf(
                    'PHP options passed to the PHP executable when executing the initial tests. Will be ignored if "--%s" option presented',
                    self::OPTION_COVERAGE
                ),
                Container::DEFAULT_INITIAL_TESTS_PHP_OPTIONS
            )
            ->addOption(
                self::OPTION_SKIP_INITIAL_TESTS,
                null,
                InputOption::VALUE_NONE,
                sprintf('Skips the initial test runs. Requires the coverage to be provided via the "--%s" option', self::OPTION_COVERAGE)
            )
            ->addOption(
                self::OPTION_IGNORE_MSI_WITH_NO_MUTATIONS,
                null,
                InputOption::VALUE_NONE,
                'Ignore MSI violations with zero mutations'
            )
            ->addOption(
                self::OPTION_DEBUG,
                null,
                InputOption::VALUE_NONE,
                'Will not clean up Infection temporary folder'
            )
            ->addOption(
                self::OPTION_DRY_RUN,
                null,
                InputOption::VALUE_NONE,
                'Will not apply the mutations'
            )
        ;
    }

    protected function executeCommand(IO $io): bool
    {
        $logger = new ConsoleLogger($io);
        $container = $this->createContainer($io, $logger);
        $consoleOutput = new ConsoleOutput($logger);

        $this->startUp($container, $consoleOutput, $logger, $io);

        $engine = new Engine(
            $container->getConfiguration(),
            $container->getTestFrameworkAdapter(),
            $container->getCoverageChecker(),
            $container->getEventDispatcher(),
            $container->getInitialTestsRunner(),
            $container->getMemoryLimiter(),
            $container->getMutationGenerator(),
            $container->getMutationTestingRunner(),
            $container->getMinMsiChecker(),
            $consoleOutput,
            $container->getMetricsCalculator(),
            $container->getTestFrameworkExtraOptionsFilter()
        );

        try {
            $engine->execute();

            return true;
        } catch (InitialTestsFailed | MinMsiCheckFailed $exception) {
            // TODO: we can move that in a dedicated logger later and handle those cases in the
            // Engine instead
            $io->error($exception->getMessage());

            return false;
        }
    }

    private function createContainer(IO $io, LoggerInterface $logger): Container
    {
        $input = $io->getInput();

        // Currently the configuration is mandatory hence there is no way to
        // say "do not use a config". If this becomes possible in the future
        // though, it will likely be a `--no-config` option rather than relying
        // on this value to be set to an empty string.
        $configFile = trim((string) $input->getOption(self::OPTION_CONFIGURATION));

        $coverage = trim((string) $input->getOption(self::OPTION_COVERAGE));
        $testFramework = trim((string) $input->getOption(self::OPTION_TEST_FRAMEWORK));
        $testFrameworkExtraOptions = trim((string) $input->getOption(self::OPTION_TEST_FRAMEWORK_OPTIONS));
        $initialTestsPhpOptions = trim((string) $input->getOption(self::OPTION_INITIAL_TESTS_PHP_OPTIONS));

        /** @var string|null $minMsi */
        $minMsi = $input->getOption(self::OPTION_MIN_MSI);
        /** @var string|null $minCoveredMsi */
        $minCoveredMsi = $input->getOption(self::OPTION_MIN_COVERED_MSI);

        $msiPrecision = MsiParser::detectPrecision($minMsi, $minCoveredMsi);

        $noProgress = (bool) $input->getOption(self::OPTION_NO_PROGRESS);
        $forceProgress = (bool) $input->getOption(self::OPTION_FORCE_PROGRESS);

        if ($noProgress && $forceProgress) {
            throw new InvalidArgumentException(
                sprintf(
                    'Cannot pass both "%s" and "%s" option: use none or only one of them',
                    self::OPTION_NO_PROGRESS,
                    self::OPTION_FORCE_PROGRESS)
            );
        }

        $gitDiffFilter = $input->getOption(self::OPTION_GIT_DIFF_FILTER);
        $gitDiffBase = $input->getOption(self::OPTION_GIT_DIFF_BASE);

        if ($gitDiffBase !== Container::DEFAULT_GIT_DIFF_BASE && $gitDiffFilter === Container::DEFAULT_GIT_DIFF_FILTER) {
            throw new InvalidArgumentException(sprintf('Cannot pass "--%s" without "--%s"', self::OPTION_GIT_DIFF_BASE, self::OPTION_GIT_DIFF_FILTER));
        }

        $filter = trim((string) $input->getOption(self::OPTION_FILTER));

        if ($filter !== '' && $gitDiffFilter !== Container::DEFAULT_GIT_DIFF_BASE) {
            throw new InvalidArgumentException(
                sprintf('Cannot pass both "--%s" and "--%s" option: use none or only one of them', self::OPTION_FILTER, self::OPTION_GIT_DIFF_FILTER)
            );
        }

        return $this->getApplication()->getContainer()->withValues(
            $logger,
            $io->getOutput(),
            $configFile === '' ? Container::DEFAULT_CONFIG_FILE : $configFile,
            trim((string) $input->getOption(self::OPTION_MUTATORS)),
            // To keep in sync with Container::DEFAULT_SHOW_MUTATIONS
            (bool) $input->getOption(self::OPTION_SHOW_MUTATIONS),
            trim((string) $input->getOption(self::OPTION_LOG_VERBOSITY)),
            // To keep in sync with Container::DEFAULT_DEBUG
            (bool) $input->getOption(self::OPTION_DEBUG),
            // To keep in sync with Container::DEFAULT_ONLY_COVERED
            (bool) $input->getOption(self::OPTION_ONLY_COVERED),
            // TODO: add more type check like we do for the test frameworks
            trim((string) $input->getOption(self::OPTION_FORMATTER)),
            // To keep in sync with Container::DEFAULT_NO_PROGRESS
            $noProgress,
            $forceProgress,
            $coverage === ''
                ? Container::DEFAULT_EXISTING_COVERAGE_PATH
                : $coverage,
            $initialTestsPhpOptions === ''
                ? Container::DEFAULT_INITIAL_TESTS_PHP_OPTIONS
                : $initialTestsPhpOptions,
            // To keep in sync with Container::DEFAULT_SKIP_INITIAL_TESTS
            (bool) $input->getOption(self::OPTION_SKIP_INITIAL_TESTS),
            // To keep in sync with Container::DEFAULT_IGNORE_MSI_WITH_NO_MUTATIONS
            (bool) $input->getOption(self::OPTION_IGNORE_MSI_WITH_NO_MUTATIONS),
            MsiParser::parse($minMsi, $msiPrecision, self::OPTION_MIN_MSI),
            MsiParser::parse($minCoveredMsi, $msiPrecision, self::OPTION_MIN_COVERED_MSI),
            $msiPrecision,
            $testFramework === ''
                ? Container::DEFAULT_TEST_FRAMEWORK
                : $testFramework,
            $testFrameworkExtraOptions === ''
                ? Container::DEFAULT_TEST_FRAMEWORK_EXTRA_OPTIONS
                : $testFrameworkExtraOptions,
            $filter,
            // TODO: more validation here?
            (int) $input->getOption(self::OPTION_THREADS),
            // To keep in sync with Container::DEFAULT_DRY_RUN
            (bool) $input->getOption(self::OPTION_DRY_RUN),
            $gitDiffFilter,
            $gitDiffBase,
            (bool) $input->getOption(self::OPTION_LOGGER_GITHUB),
            (bool) $input->getOption(self::OPTION_USE_NOOP_MUTATORS),
            (bool) $input->getOption(self::OPTION_EXECUTE_ONLY_COVERING_TEST_CASES)
        );
    }

    private function installTestFrameworkIfNeeded(Container $container, IO $io): void
    {
        $installationDecider = $container->getAdapterInstallationDecider();
        $configTestFramework = $container->getConfiguration()->getTestFramework();

        $adapterName = trim((string) $io->getInput()->getOption(self::OPTION_TEST_FRAMEWORK)) ?: $configTestFramework;

        if (!$installationDecider->shouldBeInstalled($adapterName, $io)) {
            return;
        }

        $io->newLine();
        $io->writeln(sprintf(
            'Installing <comment>infection/%s-adapter</comment>...',
            $adapterName
        ));

        $container->getAdapterInstaller()->install($adapterName);
    }

    private function startUp(
        Container $container,
        ConsoleOutput $consoleOutput,
        LoggerInterface $logger,
        IO $io
    ): void {
        $locator = $container->getRootsFileOrDirectoryLocator();

        if (($customConfigPath = (string) $io->getInput()->getOption(self::OPTION_CONFIGURATION)) !== '') {
            $locator->locate($customConfigPath);
        } else {
            $this->runConfigurationCommand($locator, $io);
        }

        $this->installTestFrameworkIfNeeded($container, $io);

        // Check if the application needs a restart _after_ configuring the command or adding
        // a missing test framework
        XdebugHandler::check($logger);

        $application = $this->getApplication();

        $io->writeln($application->getHelp());
        $io->newLine();

        $this->logRunningWithDebugger($consoleOutput);

        if (!$application->isAutoExitEnabled()) {
            // When we're not in control of exit codes, that means it's the caller
            // responsibility to disable xdebug if it isn't needed. As of writing
            // that's only the case during E2E testing. Show a warning nevertheless.

            $consoleOutput->logNotInControlOfExitCodes();
        }

        $container->getCoverageChecker()->checkCoverageRequirements();

        $config = $container->getConfiguration();

        $this->includeUserBootstrap($config);

        $container->getFileSystem()->mkdir($config->getTmpDir());

        LogVerbosity::convertVerbosityLevel($io->getInput(), $consoleOutput);

        $container->getSubscriberRegisterer()->registerSubscribers($io->getOutput());

        $container->getEventDispatcher()->dispatch(new ApplicationExecutionWasStarted());
    }

    private function runConfigurationCommand(Locator $locator, IO $io): void
    {
        try {
            $locator->locateOneOf([
                SchemaConfigurationLoader::DEFAULT_CONFIG_FILE,
                SchemaConfigurationLoader::DEFAULT_DIST_CONFIG_FILE,
            ]);
        } catch (FileNotFound | FileOrDirectoryNotFound $exception) {
            $configureCommand = $this->getApplication()->find('configure');

            $args = [
                sprintf('--%s', self::OPTION_TEST_FRAMEWORK) => $io->getInput()->getOption(self::OPTION_TEST_FRAMEWORK) ?: TestFrameworkTypes::PHPUNIT,
            ];

            $newInput = new ArrayInput($args);
            $newInput->setInteractive($io->isInteractive());
            $configureCommand->run($newInput, $io->getOutput());
        }
    }

    private function includeUserBootstrap(Configuration $config): void
    {
        $bootstrap = $config->getBootstrap();

        if ($bootstrap === null) {
            return;
        }

        if (!file_exists($bootstrap)) {
            throw FileOrDirectoryNotFound::fromFileName($bootstrap, [__DIR__]);
        }

        (static function (string $infectionBootstrapFile): void {
            require_once $infectionBootstrapFile;
        })($bootstrap);
    }

    private function logRunningWithDebugger(ConsoleOutput $consoleOutput): void
    {
        if (PHP_SAPI === 'phpdbg') {
            $consoleOutput->logRunningWithDebugger(PHP_SAPI);
        } elseif (extension_loaded('xdebug')) {
            $consoleOutput->logRunningWithDebugger('Xdebug');
        } elseif (extension_loaded('pcov')) {
            $consoleOutput->logRunningWithDebugger('PCOV');
        }
    }
}
