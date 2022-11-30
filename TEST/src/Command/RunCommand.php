<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Command;

use function extension_loaded;
use function file_exists;
use function getenv;
use function implode;
use _HumbugBox9658796bb9f0\Infection\Configuration\Configuration;
use _HumbugBox9658796bb9f0\Infection\Configuration\Schema\SchemaConfigurationLoader;
use _HumbugBox9658796bb9f0\Infection\Console\ConsoleOutput;
use _HumbugBox9658796bb9f0\Infection\Console\Input\MsiParser;
use _HumbugBox9658796bb9f0\Infection\Console\IO;
use _HumbugBox9658796bb9f0\Infection\Console\LogVerbosity;
use _HumbugBox9658796bb9f0\Infection\Console\OutputFormatter\FormatterName;
use _HumbugBox9658796bb9f0\Infection\Console\XdebugHandler;
use _HumbugBox9658796bb9f0\Infection\Container;
use _HumbugBox9658796bb9f0\Infection\Engine;
use _HumbugBox9658796bb9f0\Infection\Event\ApplicationExecutionWasStarted;
use _HumbugBox9658796bb9f0\Infection\FileSystem\Locator\FileNotFound;
use _HumbugBox9658796bb9f0\Infection\FileSystem\Locator\FileOrDirectoryNotFound;
use _HumbugBox9658796bb9f0\Infection\FileSystem\Locator\Locator;
use _HumbugBox9658796bb9f0\Infection\Logger\ConsoleLogger;
use _HumbugBox9658796bb9f0\Infection\Logger\GitHub\NoFilesInDiffToMutate;
use _HumbugBox9658796bb9f0\Infection\Metrics\MinMsiCheckFailed;
use _HumbugBox9658796bb9f0\Infection\Process\Runner\InitialTestsFailed;
use _HumbugBox9658796bb9f0\Infection\Resource\Processor\CpuCoresCountProvider;
use _HumbugBox9658796bb9f0\Infection\TestFramework\Coverage\XmlReport\NoLineExecutedInDiffLinesMode;
use _HumbugBox9658796bb9f0\Infection\TestFramework\TestFrameworkTypes;
use InvalidArgumentException;
use function is_numeric;
use function max;
use const PHP_SAPI;
use _HumbugBox9658796bb9f0\Psr\Log\LoggerInterface;
use function sprintf;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Input\ArrayInput;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Input\InputInterface;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Input\InputOption;
use function trim;
use _HumbugBox9658796bb9f0\Webmozart\Assert\Assert;
final class RunCommand extends BaseCommand
{
    private const OPTION_TEST_FRAMEWORK = 'test-framework';
    private const OPTION_TEST_FRAMEWORK_OPTIONS = 'test-framework-options';
    private const OPTION_THREADS = 'threads';
    private const OPTION_ONLY_COVERED = 'only-covered';
    private const OPTION_SHOW_MUTATIONS = 'show-mutations';
    private const OPTION_NO_PROGRESS = 'no-progress';
    private const OPTION_FORCE_PROGRESS = 'force-progress';
    private const OPTION_CONFIGURATION = 'configuration';
    private const OPTION_COVERAGE = 'coverage';
    private const OPTION_MUTATORS = 'mutators';
    private const OPTION_FILTER = 'filter';
    private const OPTION_FORMATTER = 'formatter';
    private const OPTION_GIT_DIFF_FILTER = 'git-diff-filter';
    private const OPTION_GIT_DIFF_LINES = 'git-diff-lines';
    private const OPTION_GIT_DIFF_BASE = 'git-diff-base';
    private const OPTION_LOGGER_GITHUB = 'logger-github';
    private const OPTION_LOGGER_HTML = 'logger-html';
    private const OPTION_USE_NOOP_MUTATORS = 'noop';
    private const OPTION_EXECUTE_ONLY_COVERING_TEST_CASES = 'only-covering-test-cases';
    private const OPTION_MIN_MSI = 'min-msi';
    private const OPTION_MIN_COVERED_MSI = 'min-covered-msi';
    private const OPTION_LOG_VERBOSITY = 'log-verbosity';
    private const OPTION_INITIAL_TESTS_PHP_OPTIONS = 'initial-tests-php-options';
    private const OPTION_SKIP_INITIAL_TESTS = 'skip-initial-tests';
    private const OPTION_IGNORE_MSI_WITH_NO_MUTATIONS = 'ignore-msi-with-no-mutations';
    private const OPTION_DEBUG = 'debug';
    private const OPTION_DRY_RUN = 'dry-run';
    protected function configure() : void
    {
        $this->setName('run')->setDescription('Runs the mutation testing.')->addOption(self::OPTION_TEST_FRAMEWORK, null, InputOption::VALUE_REQUIRED, sprintf('Name of the Test framework to use ("%s")', implode('", "', TestFrameworkTypes::TYPES)), Container::DEFAULT_TEST_FRAMEWORK)->addOption(self::OPTION_TEST_FRAMEWORK_OPTIONS, null, InputOption::VALUE_REQUIRED, 'Options to be passed to the test framework', Container::DEFAULT_TEST_FRAMEWORK_EXTRA_OPTIONS)->addOption(self::OPTION_THREADS, 'j', InputOption::VALUE_REQUIRED, 'Number of threads to use by the runner when executing the mutations. Use "max" to auto calculate it.', Container::DEFAULT_THREAD_COUNT)->addOption(self::OPTION_ONLY_COVERED, null, InputOption::VALUE_NONE, 'Mutate only covered by tests lines of code')->addOption(self::OPTION_SHOW_MUTATIONS, 's', InputOption::VALUE_NONE, 'Show escaped (and non-covered in verbose mode) mutations to the console')->addOption(self::OPTION_NO_PROGRESS, null, InputOption::VALUE_NONE, 'Do not output progress bars and mutation count during progress. Automatically enabled if a CI is detected')->addOption(self::OPTION_FORCE_PROGRESS, null, InputOption::VALUE_NONE, 'Output progress bars and mutation count during progress even if a CI is detected')->addOption(self::OPTION_CONFIGURATION, 'c', InputOption::VALUE_REQUIRED, 'Path to the configuration file to use', Container::DEFAULT_CONFIG_FILE)->addOption(self::OPTION_COVERAGE, null, InputOption::VALUE_REQUIRED, 'Path to existing coverage directory', Container::DEFAULT_EXISTING_COVERAGE_PATH)->addOption(self::OPTION_MUTATORS, null, InputOption::VALUE_REQUIRED, sprintf('Specify particular mutators, e.g. <comment>"--%s=Plus,PublicVisibility"</comment>', self::OPTION_MUTATORS), Container::DEFAULT_MUTATORS_INPUT)->addOption(self::OPTION_FILTER, null, InputOption::VALUE_REQUIRED, 'Filter which files to mutate', Container::DEFAULT_FILTER)->addOption(self::OPTION_FORMATTER, null, InputOption::VALUE_REQUIRED, sprintf('Name of the formatter to use ("%s")', implode('", "', FormatterName::ALL)), Container::DEFAULT_FORMATTER_NAME)->addOption(self::OPTION_GIT_DIFF_FILTER, null, InputOption::VALUE_REQUIRED, 'Filter files to mutate by git <comment>"--diff-filter"</comment> option. <comment>A</comment> - only for added files, <comment>AM</comment> - for added and modified.', Container::DEFAULT_GIT_DIFF_FILTER)->addOption(self::OPTION_GIT_DIFF_LINES, null, InputOption::VALUE_NONE, 'Mutates only added and modified <comment>lines</comment> in files.', Container::DEFAULT_GIT_DIFF_FILTER)->addOption(self::OPTION_GIT_DIFF_BASE, null, InputOption::VALUE_REQUIRED, sprintf('Base branch for <comment>"--%1$s"</comment> option. Must be used only together with <comment>"--%1$s"</comment>.', self::OPTION_GIT_DIFF_FILTER), Container::DEFAULT_GIT_DIFF_BASE)->addOption(self::OPTION_LOGGER_GITHUB, null, InputOption::VALUE_OPTIONAL, 'Log escaped Mutants as GitHub Annotations (automatically detected on Github Actions itself, use <comment>true</comment> to force-enable or <comment>false</comment> to force-disable it).', \false)->addOption(self::OPTION_LOGGER_HTML, null, InputOption::VALUE_REQUIRED, 'Path to HTML report file, similar to PHPUnit HTML report.')->addOption(self::OPTION_USE_NOOP_MUTATORS, null, InputOption::VALUE_NONE, 'Use noop mutators that do not change AST. For debugging purposes.')->addOption(self::OPTION_EXECUTE_ONLY_COVERING_TEST_CASES, null, InputOption::VALUE_NONE, 'Execute only those test cases that cover mutated line, not the whole file with covering test cases. Can dramatically speed up Mutation Testing for slow test suites. For PHPUnit / Pest it uses <comment>"--filter"</comment> option')->addOption(self::OPTION_MIN_MSI, null, InputOption::VALUE_REQUIRED, 'Minimum Mutation Score Indicator (MSI) percentage value', Container::DEFAULT_MIN_MSI)->addOption(self::OPTION_MIN_COVERED_MSI, null, InputOption::VALUE_REQUIRED, 'Minimum Covered Code Mutation Score Indicator (MSI) percentage value', Container::DEFAULT_MIN_COVERED_MSI)->addOption(self::OPTION_LOG_VERBOSITY, null, InputOption::VALUE_REQUIRED, '"all" - full logs format, "default" - short logs format, "none" - no logs', Container::DEFAULT_LOG_VERBOSITY)->addOption(self::OPTION_INITIAL_TESTS_PHP_OPTIONS, null, InputOption::VALUE_REQUIRED, sprintf('PHP options passed to the PHP executable when executing the initial tests. Will be ignored if <comment>"--%s"</comment> option presented', self::OPTION_COVERAGE), Container::DEFAULT_INITIAL_TESTS_PHP_OPTIONS)->addOption(self::OPTION_SKIP_INITIAL_TESTS, null, InputOption::VALUE_NONE, sprintf('Skips the initial test runs. Requires the coverage to be provided via the <comment>"--%s"</comment> option', self::OPTION_COVERAGE))->addOption(self::OPTION_IGNORE_MSI_WITH_NO_MUTATIONS, null, InputOption::VALUE_NONE, 'Ignore MSI violations with zero mutations')->addOption(self::OPTION_DEBUG, null, InputOption::VALUE_NONE, 'Will not clean up utility files from Infection temporary folder. Adds command lines to the logs and prints Initial Tests output to stdout.')->addOption(self::OPTION_DRY_RUN, null, InputOption::VALUE_NONE, 'Will not apply the mutations');
    }
    protected function executeCommand(IO $io) : bool
    {
        $logger = new ConsoleLogger($io);
        $container = $this->createContainer($io, $logger);
        $consoleOutput = new ConsoleOutput($logger);
        try {
            $this->startUp($container, $consoleOutput, $logger, $io);
            $engine = new Engine($container->getConfiguration(), $container->getTestFrameworkAdapter(), $container->getCoverageChecker(), $container->getEventDispatcher(), $container->getInitialTestsRunner(), $container->getMemoryLimiter(), $container->getMutationGenerator(), $container->getMutationTestingRunner(), $container->getMinMsiChecker(), $consoleOutput, $container->getMetricsCalculator(), $container->getTestFrameworkExtraOptionsFilter());
            $engine->execute();
            return \true;
        } catch (NoFilesInDiffToMutate|NoLineExecutedInDiffLinesMode $e) {
            $io->success($e->getMessage());
            return \true;
        } catch (InitialTestsFailed|MinMsiCheckFailed $exception) {
            $io->error($exception->getMessage());
            return \false;
        }
    }
    private function createContainer(IO $io, LoggerInterface $logger) : Container
    {
        $input = $io->getInput();
        $configFile = trim((string) $input->getOption(self::OPTION_CONFIGURATION));
        $coverage = trim((string) $input->getOption(self::OPTION_COVERAGE));
        $testFramework = trim((string) $input->getOption(self::OPTION_TEST_FRAMEWORK));
        $testFrameworkExtraOptions = trim((string) $input->getOption(self::OPTION_TEST_FRAMEWORK_OPTIONS));
        $initialTestsPhpOptions = trim((string) $input->getOption(self::OPTION_INITIAL_TESTS_PHP_OPTIONS));
        $htmlFileLogPath = trim((string) $input->getOption(self::OPTION_LOGGER_HTML));
        $minMsi = $input->getOption(self::OPTION_MIN_MSI);
        $minCoveredMsi = $input->getOption(self::OPTION_MIN_COVERED_MSI);
        $msiPrecision = MsiParser::detectPrecision($minMsi, $minCoveredMsi);
        $noProgress = (bool) $input->getOption(self::OPTION_NO_PROGRESS);
        $forceProgress = (bool) $input->getOption(self::OPTION_FORCE_PROGRESS);
        if ($noProgress && $forceProgress) {
            throw new InvalidArgumentException(sprintf('Cannot pass both "%s" and "%s" option: use none or only one of them', self::OPTION_NO_PROGRESS, self::OPTION_FORCE_PROGRESS));
        }
        $gitDiffFilter = $input->getOption(self::OPTION_GIT_DIFF_FILTER);
        $isForGitDiffLines = (bool) $input->getOption(self::OPTION_GIT_DIFF_LINES);
        $gitDiffBase = $input->getOption(self::OPTION_GIT_DIFF_BASE);
        if ($isForGitDiffLines && $gitDiffFilter !== Container::DEFAULT_GIT_DIFF_FILTER) {
            throw new InvalidArgumentException(sprintf('Cannot pass both "--%s" and "--%s" options: use none or only one of them', self::OPTION_GIT_DIFF_LINES, self::OPTION_GIT_DIFF_FILTER));
        }
        if ($gitDiffBase !== Container::DEFAULT_GIT_DIFF_BASE && $gitDiffFilter === Container::DEFAULT_GIT_DIFF_FILTER && $isForGitDiffLines === Container::DEFAULT_GIT_DIFF_LINES) {
            throw new InvalidArgumentException(sprintf('Cannot pass "--%s" without "--%s"', self::OPTION_GIT_DIFF_BASE, self::OPTION_GIT_DIFF_FILTER));
        }
        $filter = trim((string) $input->getOption(self::OPTION_FILTER));
        if ($filter !== '' && $gitDiffFilter !== Container::DEFAULT_GIT_DIFF_BASE) {
            throw new InvalidArgumentException(sprintf('Cannot pass both "--%s" and "--%s" options: use none or only one of them', self::OPTION_FILTER, self::OPTION_GIT_DIFF_FILTER));
        }
        return $this->getApplication()->getContainer()->withValues($logger, $io->getOutput(), $configFile === '' ? Container::DEFAULT_CONFIG_FILE : $configFile, trim((string) $input->getOption(self::OPTION_MUTATORS)), (bool) $input->getOption(self::OPTION_SHOW_MUTATIONS), trim((string) $input->getOption(self::OPTION_LOG_VERBOSITY)), (bool) $input->getOption(self::OPTION_DEBUG), (bool) $input->getOption(self::OPTION_ONLY_COVERED), trim((string) $input->getOption(self::OPTION_FORMATTER)), $noProgress, $forceProgress, $coverage === '' ? Container::DEFAULT_EXISTING_COVERAGE_PATH : $coverage, $initialTestsPhpOptions === '' ? Container::DEFAULT_INITIAL_TESTS_PHP_OPTIONS : $initialTestsPhpOptions, (bool) $input->getOption(self::OPTION_SKIP_INITIAL_TESTS), (bool) $input->getOption(self::OPTION_IGNORE_MSI_WITH_NO_MUTATIONS), MsiParser::parse($minMsi, $msiPrecision, self::OPTION_MIN_MSI), MsiParser::parse($minCoveredMsi, $msiPrecision, self::OPTION_MIN_COVERED_MSI), $msiPrecision, $testFramework === '' ? Container::DEFAULT_TEST_FRAMEWORK : $testFramework, $testFrameworkExtraOptions === '' ? Container::DEFAULT_TEST_FRAMEWORK_EXTRA_OPTIONS : $testFrameworkExtraOptions, $filter, $this->getThreadCount($input), (bool) $input->getOption(self::OPTION_DRY_RUN), $gitDiffFilter, $isForGitDiffLines, $gitDiffBase, $this->getUseGitHubLogger($input), $htmlFileLogPath === '' ? Container::DEFAULT_HTML_LOGGER_PATH : $htmlFileLogPath, (bool) $input->getOption(self::OPTION_USE_NOOP_MUTATORS), (bool) $input->getOption(self::OPTION_EXECUTE_ONLY_COVERING_TEST_CASES));
    }
    private function installTestFrameworkIfNeeded(Container $container, IO $io) : void
    {
        $installationDecider = $container->getAdapterInstallationDecider();
        $configTestFramework = $container->getConfiguration()->getTestFramework();
        $adapterName = trim((string) $io->getInput()->getOption(self::OPTION_TEST_FRAMEWORK)) ?: $configTestFramework;
        if (!$installationDecider->shouldBeInstalled($adapterName, $io)) {
            return;
        }
        $io->newLine();
        $io->writeln(sprintf('Installing <comment>infection/%s-adapter</comment>...', $adapterName));
        $container->getAdapterInstaller()->install($adapterName);
    }
    private function startUp(Container $container, ConsoleOutput $consoleOutput, LoggerInterface $logger, IO $io) : void
    {
        $locator = $container->getRootsFileOrDirectoryLocator();
        if (($customConfigPath = (string) $io->getInput()->getOption(self::OPTION_CONFIGURATION)) !== '') {
            $locator->locate($customConfigPath);
        } else {
            $this->runConfigurationCommand($locator, $io);
        }
        $this->installTestFrameworkIfNeeded($container, $io);
        XdebugHandler::check($logger);
        $application = $this->getApplication();
        $io->writeln($application->getHelp());
        $io->newLine();
        $this->logRunningWithDebugger($consoleOutput);
        if (!$application->isAutoExitEnabled()) {
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
    private function runConfigurationCommand(Locator $locator, IO $io) : void
    {
        try {
            $locator->locateOneOf(SchemaConfigurationLoader::POSSIBLE_DEFAULT_CONFIG_FILES);
        } catch (FileNotFound|FileOrDirectoryNotFound $exception) {
            $configureCommand = $this->getApplication()->find('configure');
            $args = [sprintf('--%s', self::OPTION_TEST_FRAMEWORK) => $io->getInput()->getOption(self::OPTION_TEST_FRAMEWORK) ?: TestFrameworkTypes::PHPUNIT];
            $newInput = new ArrayInput($args);
            $newInput->setInteractive($io->isInteractive());
            $configureCommand->run($newInput, $io->getOutput());
        }
    }
    private function includeUserBootstrap(Configuration $config) : void
    {
        $bootstrap = $config->getBootstrap();
        if ($bootstrap === null) {
            return;
        }
        if (!file_exists($bootstrap)) {
            throw FileOrDirectoryNotFound::fromFileName($bootstrap, [__DIR__]);
        }
        (static function (string $infectionBootstrapFile) : void {
            require_once $infectionBootstrapFile;
        })($bootstrap);
    }
    private function logRunningWithDebugger(ConsoleOutput $consoleOutput) : void
    {
        if (PHP_SAPI === 'phpdbg') {
            $consoleOutput->logRunningWithDebugger(PHP_SAPI);
        } elseif (extension_loaded('xdebug')) {
            $consoleOutput->logRunningWithDebugger('Xdebug');
        } elseif (extension_loaded('pcov')) {
            $consoleOutput->logRunningWithDebugger('PCOV');
        }
    }
    private function getUseGitHubLogger(InputInterface $input) : ?bool
    {
        if (getenv('INFECTION_E2E_TESTS_ENV') !== \false) {
            return \false;
        }
        $useGitHubLogger = $input->getOption(self::OPTION_LOGGER_GITHUB);
        if ($useGitHubLogger === \false) {
            return null;
        }
        if ($useGitHubLogger === null) {
            return \true;
        }
        if ($useGitHubLogger === 'true') {
            return \true;
        }
        if ($useGitHubLogger === 'false') {
            return \false;
        }
        throw new InvalidArgumentException(sprintf('Cannot pass "%s" to "--%s": only "true", "false" or no argument is supported', $useGitHubLogger, self::OPTION_LOGGER_GITHUB));
    }
    private function getThreadCount(InputInterface $input) : int
    {
        $threads = $input->getOption(self::OPTION_THREADS);
        if (is_numeric($threads)) {
            return (int) $threads;
        }
        Assert::same($threads, 'max', sprintf('The value of option `--threads` must be of type integer or string "max". String "%s" provided.', $threads));
        return max(1, CpuCoresCountProvider::provide() - 1);
    }
}
