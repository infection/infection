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
use function implode;
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
use Infection\Logger\GitHub\NoFilesInDiffToMutate;
use Infection\Metrics\MinMsiCheckFailed;
use Infection\Process\Runner\InitialTestsFailed;
use Infection\StaticAnalysis\StaticAnalysisToolTypes;
use Infection\TestFramework\Coverage\XmlReport\NoLineExecutedInDiffLinesMode;
use Infection\TestFramework\TestFrameworkTypes;
use InvalidArgumentException;
use const PHP_SAPI;
use Psr\Log\LoggerInterface;
use function sprintf;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputOption;
use function trim;

/**
 * @internal
 */
final class RunCommand extends BaseCommand
{
    /** @var string */
    public const OPTION_THREADS = 'threads';

    public const OPTION_MAP_SOURCE_CLASS_TO_TEST = 'map-source-class-to-test';

    /** @var string */
    public const OPTION_LOGGER_GITHUB = 'logger-github';

    /** @var string */
    public const OPTION_SHOW_MUTATIONS = 'show-mutations';

    /** @var string */
    private const OPTION_TEST_FRAMEWORK = 'test-framework';

    private const OPTION_STATIC_ANALYSIS_TOOL = 'static-analysis-tool';

    /** @var string */
    private const OPTION_TEST_FRAMEWORK_OPTIONS = 'test-framework-options';

    /** @var string */
    private const OPTION_STATIC_ANALYSIS_TOOL_OPTIONS = 'static-analysis-tool-options';

    /** @var string */
    private const OPTION_WITH_UNCOVERED = 'with-uncovered';

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
    private const OPTION_GIT_DIFF_LINES = 'git-diff-lines';

    /** @var string */
    private const OPTION_GIT_DIFF_BASE = 'git-diff-base';

    /** @var string */
    private const OPTION_LOGGER_GITLAB = 'logger-gitlab';

    private const OPTION_LOGGER_PROJECT_ROOT_DIRECTORY = 'logger-project-root-directory';

    private const OPTION_LOGGER_HTML = 'logger-html';

    private const OPTION_LOGGER_TEXT = 'logger-text';

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

    private const OPTION_MUTANT_ID = 'id';

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
                    implode('", "', TestFrameworkTypes::getTypes()),
                ),
                Container::DEFAULT_TEST_FRAMEWORK,
            )
            ->addOption(
                self::OPTION_STATIC_ANALYSIS_TOOL,
                null,
                InputOption::VALUE_REQUIRED,
                sprintf(
                    'Name of the Static Analysis tool to use ("%s")',
                    implode('", "', StaticAnalysisToolTypes::getTypes()),
                ),
                Container::DEFAULT_STATIC_ANALYSIS_TOOL,
            )
            ->addOption(
                self::OPTION_TEST_FRAMEWORK_OPTIONS,
                null,
                InputOption::VALUE_REQUIRED,
                'Options to be passed to the test framework',
                Container::DEFAULT_TEST_FRAMEWORK_EXTRA_OPTIONS,
            )
            ->addOption(
                self::OPTION_STATIC_ANALYSIS_TOOL_OPTIONS,
                null,
                InputOption::VALUE_REQUIRED,
                'Options to be passed to the static analysis tool',
                Container::DEFAULT_STATIC_ANALYSIS_TOOL_OPTIONS,
            )
            ->addOption(
                self::OPTION_THREADS,
                'j',
                InputOption::VALUE_REQUIRED,
                'Number of threads to use by the runner when executing the mutations. Use "max" to auto calculate it.',
                Container::DEFAULT_THREAD_COUNT,
            )
            ->addOption(
                self::OPTION_WITH_UNCOVERED,
                null,
                InputOption::VALUE_NONE,
                'Allow mutation of code not covered by tests.',
            )
            ->addOption(
                self::OPTION_SHOW_MUTATIONS,
                's',
                InputOption::VALUE_OPTIONAL,
                'Number of maximum escaped (and non-covered in verbose mode) mutations shown to the console. Use "max" to show all.',
                Container::DEFAULT_SHOW_MUTATIONS,
            )
            ->addOption(
                self::OPTION_NO_PROGRESS,
                null,
                InputOption::VALUE_NONE,
                'Do not output progress bars and mutation count during progress. Automatically enabled if a CI is detected',
            )
            ->addOption(
                self::OPTION_FORCE_PROGRESS,
                null,
                InputOption::VALUE_NONE,
                'Output progress bars and mutation count during progress even if a CI is detected',
            )
            ->addOption(
                self::OPTION_CONFIGURATION,
                'c',
                InputOption::VALUE_REQUIRED,
                'Path to the configuration file to use',
                Container::DEFAULT_CONFIG_FILE,
            )
            ->addOption(
                self::OPTION_COVERAGE,
                null,
                InputOption::VALUE_REQUIRED,
                'Path to existing coverage directory',
                Container::DEFAULT_EXISTING_COVERAGE_PATH,
            )
            ->addOption(
                self::OPTION_MUTATORS,
                null,
                InputOption::VALUE_REQUIRED,
                sprintf('Specify particular mutators, e.g. <comment>"--%s=Plus,PublicVisibility"</comment>', self::OPTION_MUTATORS),
                Container::DEFAULT_MUTATORS_INPUT,
            )
            ->addOption(
                self::OPTION_FILTER,
                null,
                InputOption::VALUE_REQUIRED,
                'Filter which files to mutate',
                Container::DEFAULT_FILTER,
            )
            ->addOption(
                self::OPTION_FORMATTER,
                null,
                InputOption::VALUE_REQUIRED,
                sprintf(
                    'Name of the formatter to use ("%s")',
                    implode('", "', FormatterName::ALL),
                ),
                Container::DEFAULT_FORMATTER_NAME,
            )
            ->addOption(
                self::OPTION_GIT_DIFF_FILTER,
                null,
                InputOption::VALUE_REQUIRED,
                'Filter files to mutate by git <comment>"--diff-filter"</comment> option. <comment>A</comment> - only for added files, <comment>AM</comment> - for added and modified.',
                Container::DEFAULT_GIT_DIFF_FILTER,
            )
            ->addOption(
                self::OPTION_GIT_DIFF_LINES,
                null,
                InputOption::VALUE_NONE,
                'Mutates only added and modified <comment>lines</comment> in files.',
                Container::DEFAULT_GIT_DIFF_FILTER,
            )
            ->addOption(
                self::OPTION_GIT_DIFF_BASE,
                null,
                InputOption::VALUE_REQUIRED,
                sprintf('Base branch for <comment>"--%1$s"</comment> option. Must be used only together with <comment>"--%1$s"</comment>.', self::OPTION_GIT_DIFF_FILTER),
                Container::DEFAULT_GIT_DIFF_BASE,
            )
            ->addOption(
                self::OPTION_LOGGER_GITHUB,
                null,
                InputOption::VALUE_OPTIONAL,
                'Log escaped Mutants as GitHub Annotations (automatically detected on Github Actions itself, use <comment>true</comment> to force-enable or <comment>false</comment> to force-disable it).',
                false,
            )
            ->addOption(
                self::OPTION_MUTANT_ID,
                null,
                InputOption::VALUE_REQUIRED,
                'Run only one Mutant by its ID. Can be used multiple times. If source code is changed, can be invalidated. Pass all previous options with this one.',
                Container::DEFAULT_MUTANT_ID,
            )
            ->addOption(
                self::OPTION_MAP_SOURCE_CLASS_TO_TEST,
                null,
                InputOption::VALUE_OPTIONAL,
                'Enables test files filtering during "Initial Tests Run" stage when `--filter`/`--git-diff-filter`/`--git-diff-lines` are used. With this option, only those test files are executed to provide coverage, that cover changed/added source files.',
                false,
            )
            ->addOption(
                self::OPTION_LOGGER_GITLAB,
                null,
                InputOption::VALUE_OPTIONAL,
                'Path to log escaped Mutants in the GitLab (Code Climate) JSON format.',
            )
            ->addOption(
                self::OPTION_LOGGER_PROJECT_ROOT_DIRECTORY,
                null,
                InputOption::VALUE_REQUIRED,
                'Custom path to project root directory used on the log report generation (auto-detected if not set).',
            )
            ->addOption(
                self::OPTION_LOGGER_HTML,
                null,
                InputOption::VALUE_REQUIRED,
                'Path to HTML report file, similar to PHPUnit HTML report.',
            )
            ->addOption(
                self::OPTION_LOGGER_TEXT,
                null,
                InputOption::VALUE_REQUIRED,
                'Path to text report file.',
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
                'Execute only those test cases that cover mutated line, not the whole file with covering test cases. Can dramatically speed up Mutation Testing for slow test suites. For PHPUnit, it uses <comment>"--filter"</comment> option',
            )
            ->addOption(
                self::OPTION_MIN_MSI,
                null,
                InputOption::VALUE_REQUIRED,
                'Minimum Mutation Score Indicator (MSI) percentage value',
                Container::DEFAULT_MIN_MSI,
            )
            ->addOption(
                self::OPTION_MIN_COVERED_MSI,
                null,
                InputOption::VALUE_REQUIRED,
                'Minimum Covered Code Mutation Score Indicator (MSI) percentage value',
                Container::DEFAULT_MIN_COVERED_MSI,
            )
            ->addOption(
                self::OPTION_LOG_VERBOSITY,
                null,
                InputOption::VALUE_REQUIRED,
                '"all" - full logs format, "default" - short logs format, "none" - no logs',
                Container::DEFAULT_LOG_VERBOSITY,
            )
            ->addOption(
                self::OPTION_INITIAL_TESTS_PHP_OPTIONS,
                null,
                InputOption::VALUE_REQUIRED,
                sprintf(
                    'PHP options passed to the PHP executable when executing the initial tests. Will be ignored if <comment>"--%s"</comment> option presented',
                    self::OPTION_COVERAGE,
                ),
                Container::DEFAULT_INITIAL_TESTS_PHP_OPTIONS,
            )
            ->addOption(
                self::OPTION_SKIP_INITIAL_TESTS,
                null,
                InputOption::VALUE_NONE,
                sprintf('Skips the initial test runs. Requires the coverage to be provided via the <comment>"--%s"</comment> option', self::OPTION_COVERAGE),
            )
            ->addOption(
                self::OPTION_IGNORE_MSI_WITH_NO_MUTATIONS,
                null,
                InputOption::VALUE_NONE,
                'Ignore MSI violations with zero mutations',
            )
            ->addOption(
                self::OPTION_DEBUG,
                null,
                InputOption::VALUE_NONE,
                'Will not clean up utility files from Infection temporary folder. Adds command lines to the logs and prints Initial Tests output to stdout.',
            )
            ->addOption(
                self::OPTION_DRY_RUN,
                null,
                InputOption::VALUE_NONE,
                'Runs mutation testing and does not run killer processes.',
            )
        ;
    }

    protected function executeCommand(IO $io): bool
    {
        $logger = new ConsoleLogger($io);
        $container = $this->createContainer($io, $logger);
        $consoleOutput = new ConsoleOutput($logger);

        try {
            $this->startUp($container, $consoleOutput, $logger, $io);

            $config = $container->getConfiguration();

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
                $container->getTestFrameworkExtraOptionsFilter(),
                // do not create a chain of classes for SA if not enabled
                $config->isStaticAnalysisEnabled() ? $container->getInitialStaticAnalysisRunner() : null,
                $config->isStaticAnalysisEnabled() ? $container->getStaticAnalysisToolAdapter() : null,
            );

            $engine->execute();

            return true;
        } catch (NoFilesInDiffToMutate|NoLineExecutedInDiffLinesMode $e) {
            $io->success($e->getMessage());

            return true;
        } catch (InitialTestsFailed|MinMsiCheckFailed $exception) {
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
        $staticAnalysisTool = trim((string) $input->getOption(self::OPTION_STATIC_ANALYSIS_TOOL));
        $staticAnalysisToolOptions = trim((string) $input->getOption(self::OPTION_STATIC_ANALYSIS_TOOL_OPTIONS));
        $initialTestsPhpOptions = trim((string) $input->getOption(self::OPTION_INITIAL_TESTS_PHP_OPTIONS));
        $gitlabFileLogPath = trim((string) $input->getOption(self::OPTION_LOGGER_GITLAB));
        $htmlFileLogPath = trim((string) $input->getOption(self::OPTION_LOGGER_HTML));
        $textLogFilePath = trim((string) $input->getOption(self::OPTION_LOGGER_TEXT));
        $loggerProjectRootDirectory = $input->getOption(self::OPTION_LOGGER_PROJECT_ROOT_DIRECTORY);

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
                    self::OPTION_FORCE_PROGRESS),
            );
        }

        $gitDiffFilter = $input->getOption(self::OPTION_GIT_DIFF_FILTER);
        $isForGitDiffLines = (bool) $input->getOption(self::OPTION_GIT_DIFF_LINES);
        $gitDiffBase = $input->getOption(self::OPTION_GIT_DIFF_BASE);

        if ($isForGitDiffLines && $gitDiffFilter !== Container::DEFAULT_GIT_DIFF_FILTER) {
            throw new InvalidArgumentException(
                sprintf(
                    'The options "--%s" and "--%s" are mutually exclusive. Please use only one of them.',
                    self::OPTION_GIT_DIFF_LINES,
                    self::OPTION_GIT_DIFF_FILTER,
                ),
            );
        }

        if ($gitDiffBase !== Container::DEFAULT_GIT_DIFF_BASE && $gitDiffFilter === Container::DEFAULT_GIT_DIFF_FILTER && $isForGitDiffLines === Container::DEFAULT_GIT_DIFF_LINES) {
            throw new InvalidArgumentException(
                sprintf(
                    'The option "--%s" cannot be used without the option "--%s" or "--%s".',
                    self::OPTION_GIT_DIFF_BASE,
                    self::OPTION_GIT_DIFF_LINES,
                    self::OPTION_GIT_DIFF_FILTER,
                ),
            );
        }

        $filter = trim((string) $input->getOption(self::OPTION_FILTER));

        if ($filter !== '' && $gitDiffFilter !== Container::DEFAULT_GIT_DIFF_BASE) {
            throw new InvalidArgumentException(
                sprintf(
                    'The options "--%s" and "--%s" are mutually exclusive. Use "--%s" for regular filtering or "--%s" for Git-based filtering.',
                    self::OPTION_FILTER,
                    self::OPTION_GIT_DIFF_FILTER,
                    self::OPTION_FILTER,
                    self::OPTION_GIT_DIFF_FILTER,
                ),
            );
        }

        $commandHelper = new RunCommandHelper($input);

        return $this->getApplication()->getContainer()->withValues(
            $logger,
            $io->getOutput(),
            $configFile === '' ? Container::DEFAULT_CONFIG_FILE : $configFile,
            trim((string) $input->getOption(self::OPTION_MUTATORS)),
            $commandHelper->getNumberOfShownMutations(),
            trim((string) $input->getOption(self::OPTION_LOG_VERBOSITY)),
            // To keep in sync with Container::DEFAULT_DEBUG
            (bool) $input->getOption(self::OPTION_DEBUG),
            // To keep in sync with Container::DEFAULT_WITH_UNCOVERED
            (bool) $input->getOption(self::OPTION_WITH_UNCOVERED),
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
            $staticAnalysisToolOptions === ''
                ? Container::DEFAULT_STATIC_ANALYSIS_TOOL_OPTIONS
                : $staticAnalysisToolOptions,
            $filter,
            $commandHelper->getThreadCount(),
            // To keep in sync with Container::DEFAULT_DRY_RUN
            (bool) $input->getOption(self::OPTION_DRY_RUN),
            $gitDiffFilter,
            $isForGitDiffLines,
            $gitDiffBase,
            $commandHelper->getUseGitHubLogger(),
            $gitlabFileLogPath === '' ? Container::DEFAULT_GITLAB_LOGGER_PATH : $gitlabFileLogPath,
            $htmlFileLogPath === '' ? Container::DEFAULT_HTML_LOGGER_PATH : $htmlFileLogPath,
            $textLogFilePath === '' ? Container::DEFAULT_TEXT_LOGGER_PATH : $textLogFilePath,
            (bool) $input->getOption(self::OPTION_USE_NOOP_MUTATORS),
            (bool) $input->getOption(self::OPTION_EXECUTE_ONLY_COVERING_TEST_CASES),
            $commandHelper->getMapSourceClassToTest(),
            $loggerProjectRootDirectory,
            $staticAnalysisTool === '' ? Container::DEFAULT_STATIC_ANALYSIS_TOOL : $staticAnalysisTool,
            $input->getOption(self::OPTION_MUTANT_ID),
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
            $adapterName,
        ));

        $container->getAdapterInstaller()->install($adapterName);
    }

    private function startUp(
        Container $container,
        ConsoleOutput $consoleOutput,
        LoggerInterface $logger,
        IO $io,
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

        if ($config->isStaticAnalysisEnabled()) {
            $container->getStaticAnalysisToolAdapter()->assertMinimumVersionSatisfied();
        }

        $container->getFileSystem()->mkdir($config->getTmpDir());

        LogVerbosity::convertVerbosityLevel($io->getInput(), $consoleOutput);

        $container->getSubscriberRegisterer()->registerSubscribers($io->getOutput());

        $container->getEventDispatcher()->dispatch(new ApplicationExecutionWasStarted());
    }

    private function runConfigurationCommand(Locator $locator, IO $io): void
    {
        try {
            $locator->locateOneOf(SchemaConfigurationLoader::POSSIBLE_DEFAULT_CONFIG_FILES);
        } catch (FileNotFound|FileOrDirectoryNotFound) {
            $configureCommand = $this->getApplication()->find('configure');

            $args = [
                sprintf('--%s', self::OPTION_TEST_FRAMEWORK) => $io->getInput()->getOption(self::OPTION_TEST_FRAMEWORK) ?: TestFrameworkTypes::PHPUNIT,
            ];

            $newInput = new ArrayInput($args);
            $newInput->setInteractive($io->isInteractive());
            $configureCommand->run($newInput, $io->getOutput());
        }
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
