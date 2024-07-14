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

use function array_filter;
use function array_key_exists;
use Closure;
use Infection\AbstractTestFramework\TestFrameworkAdapter;
use Infection\CI\MemoizedCiDetector;
use Infection\CI\NullCiDetector;
use Infection\Configuration\Configuration;
use Infection\Configuration\ConfigurationFactory;
use Infection\Configuration\Schema\SchemaConfiguration;
use Infection\Configuration\Schema\SchemaConfigurationFactory;
use Infection\Configuration\Schema\SchemaConfigurationFileLoader;
use Infection\Configuration\Schema\SchemaConfigurationLoader;
use Infection\Configuration\Schema\SchemaValidator;
use Infection\Console\Input\MsiParser;
use Infection\Console\LogVerbosity;
use Infection\Console\OutputFormatter\FormatterFactory;
use Infection\Console\OutputFormatter\FormatterName;
use Infection\Console\OutputFormatter\OutputFormatter;
use Infection\Differ\DiffChangedLinesParser;
use Infection\Differ\DiffColorizer;
use Infection\Differ\Differ;
use Infection\Differ\DiffSourceCodeMatcher;
use Infection\Differ\FilesDiffChangedLines;
use Infection\Event\EventDispatcher\EventDispatcher;
use Infection\Event\EventDispatcher\SyncEventDispatcher;
use Infection\Event\Subscriber\ChainSubscriberFactory;
use Infection\Event\Subscriber\CleanUpAfterMutationTestingFinishedSubscriberFactory;
use Infection\Event\Subscriber\DispatchPcntlSignalSubscriberFactory;
use Infection\Event\Subscriber\InitialTestsConsoleLoggerSubscriberFactory;
use Infection\Event\Subscriber\MutationGeneratingConsoleLoggerSubscriberFactory;
use Infection\Event\Subscriber\MutationTestingConsoleLoggerSubscriberFactory;
use Infection\Event\Subscriber\MutationTestingResultsCollectorSubscriberFactory;
use Infection\Event\Subscriber\MutationTestingResultsLoggerSubscriberFactory;
use Infection\Event\Subscriber\PerformanceLoggerSubscriberFactory;
use Infection\Event\Subscriber\StopInfectionOnSigintSignalSubscriberFactory;
use Infection\Event\Subscriber\SubscriberRegisterer;
use Infection\ExtensionInstaller\GeneratedExtensionsConfig;
use Infection\FileSystem\DummyFileSystem;
use Infection\FileSystem\Finder\ComposerExecutableFinder;
use Infection\FileSystem\Finder\TestFrameworkFinder;
use Infection\FileSystem\Locator\RootsFileLocator;
use Infection\FileSystem\Locator\RootsFileOrDirectoryLocator;
use Infection\FileSystem\SourceFileCollector;
use Infection\FileSystem\SourceFileFilter;
use Infection\FileSystem\TmpDirProvider;
use Infection\Logger\FederatedLogger;
use Infection\Logger\FileLoggerFactory;
use Infection\Logger\GitHub\GitDiffFileProvider;
use Infection\Logger\Html\StrykerHtmlReportBuilder;
use Infection\Logger\MutationTestingResultsLogger;
use Infection\Logger\StrykerLoggerFactory;
use Infection\Metrics\FilteringResultsCollectorFactory;
use Infection\Metrics\MetricsCalculator;
use Infection\Metrics\MinMsiChecker;
use Infection\Metrics\ResultsCollector;
use Infection\Metrics\TargetDetectionStatusesProvider;
use Infection\Mutant\MutantCodeFactory;
use Infection\Mutant\MutantExecutionResultFactory;
use Infection\Mutant\MutantFactory;
use Infection\Mutation\FileMutationGenerator;
use Infection\Mutation\MutationGenerator;
use Infection\Mutator\MutatorFactory;
use Infection\Mutator\MutatorParser;
use Infection\Mutator\MutatorResolver;
use Infection\PhpParser\FileParser;
use Infection\PhpParser\NodeTraverserFactory;
use Infection\Process\Factory\InitialTestsRunProcessFactory;
use Infection\Process\Factory\MutantProcessFactory;
use Infection\Process\Runner\DryProcessRunner;
use Infection\Process\Runner\InitialTestsRunner;
use Infection\Process\Runner\MutationTestingRunner;
use Infection\Process\Runner\ParallelProcessRunner;
use Infection\Process\Runner\ProcessRunner;
use Infection\Process\ShellCommandLineExecutor;
use Infection\Resource\Memory\MemoryFormatter;
use Infection\Resource\Memory\MemoryLimiter;
use Infection\Resource\Memory\MemoryLimiterEnvironment;
use Infection\Resource\Time\Stopwatch;
use Infection\Resource\Time\TimeFormatter;
use Infection\TestFramework\AdapterInstallationDecider;
use Infection\TestFramework\AdapterInstaller;
use Infection\TestFramework\CommandLineBuilder;
use Infection\TestFramework\Config\TestFrameworkConfigLocator;
use Infection\TestFramework\Coverage\BufferedSourceFileFilter;
use Infection\TestFramework\Coverage\CoverageChecker;
use Infection\TestFramework\Coverage\CoveredTraceProvider;
use Infection\TestFramework\Coverage\JUnit\JUnitReportLocator;
use Infection\TestFramework\Coverage\JUnit\JUnitTestExecutionInfoAdder;
use Infection\TestFramework\Coverage\JUnit\JUnitTestFileDataProvider;
use Infection\TestFramework\Coverage\JUnit\MemoizedTestFileDataProvider;
use Infection\TestFramework\Coverage\JUnit\TestFileDataProvider;
use Infection\TestFramework\Coverage\LineRangeCalculator;
use Infection\TestFramework\Coverage\UncoveredTraceProvider;
use Infection\TestFramework\Coverage\UnionTraceProvider;
use Infection\TestFramework\Coverage\XmlReport\IndexXmlCoverageLocator;
use Infection\TestFramework\Coverage\XmlReport\IndexXmlCoverageParser;
use Infection\TestFramework\Coverage\XmlReport\PhpUnitXmlCoverageTraceProvider;
use Infection\TestFramework\Coverage\XmlReport\XmlCoverageParser;
use Infection\TestFramework\Factory;
use Infection\TestFramework\TestFrameworkExtraOptionsFilter;
use InvalidArgumentException;
use OndraM\CiDetector\CiDetector;
use function php_ini_loaded_file;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use PhpParser\PrettyPrinterAbstract;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use function Safe\getcwd;
use SebastianBergmann\Diff\Differ as BaseDiffer;
use SebastianBergmann\Diff\Output\UnifiedDiffOutputBuilder;
use function sprintf;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;
use Webmozart\Assert\Assert;

/**
 * @internal
 */
final class Container
{
    public const DEFAULT_CONFIG_FILE = null;
    public const DEFAULT_MUTATORS_INPUT = '';
    public const DEFAULT_SHOW_MUTATIONS = false;
    public const DEFAULT_LOG_VERBOSITY = LogVerbosity::NORMAL;
    public const DEFAULT_DEBUG = false;
    public const DEFAULT_ONLY_COVERED = false;
    public const DEFAULT_FORMATTER_NAME = FormatterName::DOT;
    public const DEFAULT_GIT_DIFF_FILTER = null;
    public const DEFAULT_GIT_DIFF_LINES = false;
    public const DEFAULT_GIT_DIFF_BASE = null;
    public const DEFAULT_USE_GITHUB_LOGGER = null;
    public const DEFAULT_GITLAB_LOGGER_PATH = null;
    public const DEFAULT_LOGGER_PROJECT_ROOT_DIRECTORY = null;
    public const DEFAULT_HTML_LOGGER_PATH = null;
    public const DEFAULT_USE_NOOP_MUTATORS = false;
    public const DEFAULT_EXECUTE_ONLY_COVERING_TEST_CASES = false;
    public const DEFAULT_NO_PROGRESS = false;
    public const DEFAULT_FORCE_PROGRESS = false;
    public const DEFAULT_EXISTING_COVERAGE_PATH = null;
    public const DEFAULT_INITIAL_TESTS_PHP_OPTIONS = null;
    public const DEFAULT_SKIP_INITIAL_TESTS = false;
    public const DEFAULT_IGNORE_MSI_WITH_NO_MUTATIONS = false;
    public const DEFAULT_MIN_MSI = null;
    public const DEFAULT_MIN_COVERED_MSI = null;
    public const DEFAULT_MSI_PRECISION = MsiParser::DEFAULT_PRECISION;
    public const DEFAULT_TEST_FRAMEWORK = null;
    public const DEFAULT_TEST_FRAMEWORK_EXTRA_OPTIONS = null;
    public const DEFAULT_FILTER = '';
    public const DEFAULT_THREAD_COUNT = 1;
    public const DEFAULT_DRY_RUN = false;
    public const DEFAULT_MAP_SOURCE_CLASS_TO_TEST_STRATEGY = null;

    /**
     * @var array<class-string<object>, true>
     */
    private array $keys = [];

    /**
     * @var array<class-string<object>, object>
     */
    private array $values = [];

    /**
     * @var array<class-string<object>, Closure(self): object>
     */
    private array $factories = [];

    private ?string $defaultJUnitPath = null;

    /**
     * @param array<class-string<object>, Closure(self): object> $values
     */
    public function __construct(array $values)
    {
        foreach ($values as $id => $value) {
            $this->offsetSet($id, $value);
        }
    }

    public static function create(): self
    {
        $container = new self([
            Filesystem::class => static fn (): Filesystem => new Filesystem(),
            TmpDirProvider::class => static fn (): TmpDirProvider => new TmpDirProvider(),
            IndexXmlCoverageParser::class => static fn (self $container): IndexXmlCoverageParser => new IndexXmlCoverageParser(
                $container->getConfiguration()->isForGitDiffLines(),
            ),
            XmlCoverageParser::class => static fn (): XmlCoverageParser => new XmlCoverageParser(),
            CoveredTraceProvider::class => static fn (self $container): CoveredTraceProvider => new CoveredTraceProvider(
                $container->getPhpUnitXmlCoverageTraceProvider(),
                $container->getJUnitTestExecutionInfoAdder(),
                $container->getBufferedSourceFileFilter(),
            ),
            UnionTraceProvider::class => static fn (self $container): UnionTraceProvider => new UnionTraceProvider(
                $container->getCoveredTraceProvider(),
                $container->getUncoveredTraceProvider(),
                $container->getConfiguration()->mutateOnlyCoveredCode(),
            ),
            BufferedSourceFileFilter::class => static fn (self $container): BufferedSourceFileFilter => new BufferedSourceFileFilter(
                $container->getSourceFileFilter(),
                $container->getConfiguration()->getSourceFiles(),
            ),
            UncoveredTraceProvider::class => static fn (self $container): UncoveredTraceProvider => new UncoveredTraceProvider(
                $container->getBufferedSourceFileFilter(),
            ),
            SourceFileFilter::class => static fn (self $container): SourceFileFilter => new SourceFileFilter(
                $container->getConfiguration()->getSourceFilesFilter(),
                $container->getConfiguration()->getSourceFilesExcludes(),
            ),
            JUnitTestExecutionInfoAdder::class => static fn (self $container): JUnitTestExecutionInfoAdder => new JUnitTestExecutionInfoAdder(
                $container->getTestFrameworkAdapter(),
                $container->getMemoizedTestFileDataProvider(),
            ),
            PhpUnitXmlCoverageTraceProvider::class => static fn (self $container): PhpUnitXmlCoverageTraceProvider => new PhpUnitXmlCoverageTraceProvider(
                $container->getIndexXmlCoverageLocator(),
                $container->getIndexXmlCoverageParser(),
                $container->getXmlCoverageParser(),
            ),
            IndexXmlCoverageLocator::class => static fn (self $container): IndexXmlCoverageLocator => new IndexXmlCoverageLocator(
                $container->getConfiguration()->getCoveragePath(),
            ),
            RootsFileOrDirectoryLocator::class => static fn (self $container): RootsFileOrDirectoryLocator => new RootsFileOrDirectoryLocator(
                [$container->getProjectDir()],
                $container->getFileSystem(),
            ),
            Factory::class => static function (self $container): Factory {
                $config = $container->getConfiguration();

                return new Factory(
                    $config->getTmpDir(),
                    $container->getProjectDir(),
                    $container->getTestFrameworkConfigLocator(),
                    $container->getTestFrameworkFinder(),
                    $container->getDefaultJUnitFilePath(),
                    $config,
                    $container->getSourceFileFilter(),
                    GeneratedExtensionsConfig::EXTENSIONS,
                );
            },
            MutantCodeFactory::class => static fn (self $container): MutantCodeFactory => new MutantCodeFactory($container->getPrinter()),
            MutantFactory::class => static fn (self $container): MutantFactory => new MutantFactory(
                $container->getConfiguration()->getTmpDir(),
                $container->getDiffer(),
                $container->getPrinter(),
                $container->getMutantCodeFactory(),
            ),
            Differ::class => static fn (): Differ => new Differ(new BaseDiffer(new UnifiedDiffOutputBuilder(''))),
            SyncEventDispatcher::class => static fn (): SyncEventDispatcher => new SyncEventDispatcher(),
            ParallelProcessRunner::class => static fn (self $container): ParallelProcessRunner => new ParallelProcessRunner($container->getConfiguration()->getThreadCount()),
            DryProcessRunner::class => static fn (): DryProcessRunner => new DryProcessRunner(),
            TestFrameworkConfigLocator::class => static fn (self $container): TestFrameworkConfigLocator => new TestFrameworkConfigLocator(
                (string) $container->getConfiguration()->getPhpUnit()->getConfigDir(),
            ),
            DiffColorizer::class => static fn (): DiffColorizer => new DiffColorizer(),
            MemoizedTestFileDataProvider::class => static fn (self $container): TestFileDataProvider => new MemoizedTestFileDataProvider(
                new JUnitTestFileDataProvider($container->getJUnitReportLocator()),
            ),
            Parser::class => static fn (): Parser => (new ParserFactory())->createForHostVersion(),
            FileParser::class => static fn (self $container): FileParser => new FileParser($container->getParser()),
            PrettyPrinterAbstract::class => static fn (): Standard => new Standard(),
            MetricsCalculator::class => static fn (self $container): MetricsCalculator => new MetricsCalculator($container->getConfiguration()->getMsiPrecision()),
            ResultsCollector::class => static fn (self $container): ResultsCollector => new ResultsCollector(),
            Stopwatch::class => static fn (): Stopwatch => new Stopwatch(),
            TimeFormatter::class => static fn (): TimeFormatter => new TimeFormatter(),
            MemoryFormatter::class => static fn (): MemoryFormatter => new MemoryFormatter(),
            MemoryLimiter::class => static fn (self $container): MemoryLimiter => new MemoryLimiter(
                $container->getFileSystem(),
                (string) php_ini_loaded_file(),
                new MemoryLimiterEnvironment(),
            ),
            SchemaConfigurationLoader::class => static fn (self $container): SchemaConfigurationLoader => new SchemaConfigurationLoader(
                $container->getRootsFileLocator(),
                $container->getSchemaConfigurationFileLoader(),
            ),
            RootsFileLocator::class => static fn (self $container): RootsFileLocator => new RootsFileLocator(
                [$container->getProjectDir()],
                $container->getFileSystem(),
            ),
            SchemaConfigurationFileLoader::class => static fn (self $container): SchemaConfigurationFileLoader => new SchemaConfigurationFileLoader(
                $container->getSchemaValidator(),
                $container->getSchemaConfigurationFactory(),
            ),
            SchemaValidator::class => static fn (): SchemaValidator => new SchemaValidator(),
            SchemaConfigurationFactory::class => static fn (): SchemaConfigurationFactory => new SchemaConfigurationFactory(),
            ConfigurationFactory::class => static fn (self $container): ConfigurationFactory => new ConfigurationFactory(
                $container->getTmpDirProvider(),
                $container->getMutatorResolver(),
                $container->getMutatorFactory(),
                $container->getMutatorParser(),
                $container->getSourceFileCollector(),
                $container->getCiDetector(),
                $container->getGitDiffFileProvider(),
            ),
            MutatorResolver::class => static fn (): MutatorResolver => new MutatorResolver(),
            MutatorFactory::class => static fn (): MutatorFactory => new MutatorFactory(),
            MutatorParser::class => static fn (): MutatorParser => new MutatorParser(),
            CoverageChecker::class => static function (self $container): CoverageChecker {
                $config = $container->getConfiguration();
                $testFrameworkAdapter = $container->getTestFrameworkAdapter();

                return new CoverageChecker(
                    $config->shouldSkipCoverage(),
                    $config->shouldSkipInitialTests(),
                    $config->getInitialTestsPhpOptions() ?? '',
                    $config->getCoveragePath(),
                    $testFrameworkAdapter->hasJUnitReport(),
                    $container->getJUnitReportLocator(),
                    $testFrameworkAdapter->getName(),
                    $container->getIndexXmlCoverageLocator(),
                );
            },
            JUnitReportLocator::class => static fn (self $container): JUnitReportLocator => new JUnitReportLocator(
                $container->getConfiguration()->getCoveragePath(),
                $container->getDefaultJUnitFilePath(),
            ),
            MinMsiChecker::class => static function (self $container): MinMsiChecker {
                $config = $container->getConfiguration();

                return new MinMsiChecker(
                    $config->ignoreMsiWithNoMutations(),
                    (float) $config->getMinMsi(),
                    (float) $config->getMinCoveredMsi(),
                );
            },
            SubscriberRegisterer::class => static fn (self $container): SubscriberRegisterer => new SubscriberRegisterer(
                $container->getEventDispatcher(),
                $container->getSubscriberFactoryRegistry(),
            ),
            ChainSubscriberFactory::class => static fn (self $container): ChainSubscriberFactory => new ChainSubscriberFactory(
                $container->getInitialTestsConsoleLoggerSubscriberFactory(),
                $container->getMutationGeneratingConsoleLoggerSubscriberFactory(),
                $container->getMutationTestingResultsCollectorSubscriberFactory(),
                $container->getMutationTestingConsoleLoggerSubscriberFactory(),
                $container->getMutationTestingResultsLoggerSubscriberFactory(),
                $container->getPerformanceLoggerSubscriberFactory(),
                $container->getCleanUpAfterMutationTestingFinishedSubscriberFactory(),
                $container->getStopInfectionOnSigintSignalSubscriberFactory(),
                $container->getDispatchPcntlSignalSubscriberFactory(),
            ),
            CleanUpAfterMutationTestingFinishedSubscriberFactory::class => static function (self $container): CleanUpAfterMutationTestingFinishedSubscriberFactory {
                $config = $container->getConfiguration();

                return new CleanUpAfterMutationTestingFinishedSubscriberFactory(
                    $config->isDebugEnabled(),
                    $container->getFileSystem(),
                    $config->getTmpDir(),
                );
            },
            StopInfectionOnSigintSignalSubscriberFactory::class => static fn (self $container): StopInfectionOnSigintSignalSubscriberFactory => new StopInfectionOnSigintSignalSubscriberFactory(),
            DispatchPcntlSignalSubscriberFactory::class => static fn (self $container): DispatchPcntlSignalSubscriberFactory => new DispatchPcntlSignalSubscriberFactory(),
            InitialTestsConsoleLoggerSubscriberFactory::class => static function (self $container): InitialTestsConsoleLoggerSubscriberFactory {
                $config = $container->getConfiguration();

                return new InitialTestsConsoleLoggerSubscriberFactory(
                    $config->noProgress(),
                    $container->getTestFrameworkAdapter(),
                    $config->isDebugEnabled(),
                );
            },
            MutationGeneratingConsoleLoggerSubscriberFactory::class => static fn (self $container): MutationGeneratingConsoleLoggerSubscriberFactory => new MutationGeneratingConsoleLoggerSubscriberFactory(
                $container->getConfiguration()->noProgress(),
            ),
            MutationTestingResultsCollectorSubscriberFactory::class => static fn (self $container): MutationTestingResultsCollectorSubscriberFactory => new MutationTestingResultsCollectorSubscriberFactory(
                ...array_filter([
                    $container->getMetricsCalculator(),
                    $container->getFilteringResultsCollectorFactory()->create(
                        $container->getResultsCollector(),
                    ),
                ]),
            ),
            MutationTestingConsoleLoggerSubscriberFactory::class => static function (self $container): MutationTestingConsoleLoggerSubscriberFactory {
                $config = $container->getConfiguration();
                /** @var FederatedLogger $federatedMutationTestingResultsLogger */
                $federatedMutationTestingResultsLogger = $container->getMutationTestingResultsLogger();

                return new MutationTestingConsoleLoggerSubscriberFactory(
                    $container->getMetricsCalculator(),
                    $container->getResultsCollector(),
                    $container->getDiffColorizer(),
                    $federatedMutationTestingResultsLogger,
                    $config->showMutations(),
                    $container->getOutputFormatter(),
                );
            },
            MutationTestingResultsLoggerSubscriberFactory::class => static fn (self $container): MutationTestingResultsLoggerSubscriberFactory => new MutationTestingResultsLoggerSubscriberFactory(
                $container->getMutationTestingResultsLogger(),
            ),
            PerformanceLoggerSubscriberFactory::class => static fn (self $container): PerformanceLoggerSubscriberFactory => new PerformanceLoggerSubscriberFactory(
                $container->getStopwatch(),
                $container->getTimeFormatter(),
                $container->getMemoryFormatter(),
                $container->getConfiguration()->getThreadCount(),
            ),
            CommandLineBuilder::class => static fn (): CommandLineBuilder => new CommandLineBuilder(),
            SourceFileCollector::class => static fn (): SourceFileCollector => new SourceFileCollector(),
            NodeTraverserFactory::class => static fn (): NodeTraverserFactory => new NodeTraverserFactory(),
            FileMutationGenerator::class => static function (self $container): FileMutationGenerator {
                $configuration = $container->getConfiguration();

                return new FileMutationGenerator(
                    $container->getFileParser(),
                    $container->getNodeTraverserFactory(),
                    $container->getLineRangeCalculator(),
                    $container->getFilesDiffChangedLines(),
                    $configuration->isForGitDiffLines(),
                    $configuration->getGitDiffBase(),
                );
            },
            DiffChangedLinesParser::class => static fn (self $container): DiffChangedLinesParser => new DiffChangedLinesParser(),
            FilesDiffChangedLines::class => static fn (self $container): FilesDiffChangedLines => new FilesDiffChangedLines($container->getDiffChangedLinesParser(), $container->getGitDiffFileProvider()),
            StrykerLoggerFactory::class => static fn (self $container): StrykerLoggerFactory => new StrykerLoggerFactory(
                $container->getMetricsCalculator(),
                $container->getStrykerHtmlReportBuilder(),
                $container->getCiDetector(),
                $container->getLogger(),
            ),
            FileLoggerFactory::class => static function (self $container): FileLoggerFactory {
                $config = $container->getConfiguration();

                return new FileLoggerFactory(
                    $container->getMetricsCalculator(),
                    $container->getResultsCollector(),
                    $container->getFileSystem(),
                    $config->getLogVerbosity(),
                    $config->isDebugEnabled(),
                    $config->mutateOnlyCoveredCode(),
                    $container->getLogger(),
                    $container->getStrykerHtmlReportBuilder(),
                    $config->getLoggerProjectRootDirectory(),
                );
            },
            MutationTestingResultsLogger::class => static fn (self $container): MutationTestingResultsLogger => new FederatedLogger(...array_filter([
                $container->getFileLoggerFactory()->createFromLogEntries(
                    $container->getConfiguration()->getLogs(),
                ),
                $container->getStrykerLoggerFactory()->createFromLogEntries(
                    $container->getConfiguration()->getLogs(),
                ),
            ])),
            StrykerHtmlReportBuilder::class => static fn (self $container): StrykerHtmlReportBuilder => new StrykerHtmlReportBuilder($container->getMetricsCalculator(), $container->getResultsCollector()),
            TargetDetectionStatusesProvider::class => static function (self $container): TargetDetectionStatusesProvider {
                $config = $container->getConfiguration();

                return new TargetDetectionStatusesProvider(
                    $config->getLogs(),
                    $config->getLogVerbosity(),
                    $config->mutateOnlyCoveredCode(),
                    $config->showMutations(),
                );
            },
            FilteringResultsCollectorFactory::class => static fn (self $container): FilteringResultsCollectorFactory => new FilteringResultsCollectorFactory($container->getTargetDetectionStatusesProvider()),
            TestFrameworkAdapter::class => static function (self $container): TestFrameworkAdapter {
                $config = $container->getConfiguration();

                return $container->getFactory()->create(
                    $config->getTestFramework(),
                    $config->shouldSkipCoverage(),
                );
            },
            InitialTestsRunProcessFactory::class => static fn (self $container): InitialTestsRunProcessFactory => new InitialTestsRunProcessFactory(
                $container->getTestFrameworkAdapter(),
            ),
            InitialTestsRunner::class => static fn (self $container): InitialTestsRunner => new InitialTestsRunner(
                $container->getInitialTestRunProcessFactory(),
                $container->getEventDispatcher(),
            ),
            MutantProcessFactory::class => static fn (self $container): MutantProcessFactory => new MutantProcessFactory(
                $container->getTestFrameworkAdapter(),
                $container->getConfiguration()->getProcessTimeout(),
                $container->getEventDispatcher(),
                $container->getMutantExecutionResultFactory(),
            ),
            MutationGenerator::class => static function (self $container): MutationGenerator {
                $config = $container->getConfiguration();

                return new MutationGenerator(
                    $container->getUnionTraceProvider(),
                    $config->getMutators(),
                    $container->getEventDispatcher(),
                    $container->getFileMutationGenerator(),
                    $config->noProgress(),
                );
            },
            MutationTestingRunner::class => static function (self $container): MutationTestingRunner {
                $configuration = $container->getConfiguration();

                return new MutationTestingRunner(
                    $container->getMutantProcessFactory(),
                    $container->getMutantFactory(),
                    $container->getProcessRunner(),
                    $container->getEventDispatcher(),
                    $configuration->isDryRun()
                        ? new DummyFileSystem()
                        : $container->getFileSystem(),
                    $container->getDiffSourceCodeMatcher(),
                    $configuration->noProgress(),
                    $configuration->getProcessTimeout(),
                    $configuration->getIgnoreSourceCodeMutatorsMap(),
                );
            },
            LineRangeCalculator::class => static fn (): LineRangeCalculator => new LineRangeCalculator(),
            TestFrameworkFinder::class => static fn (): TestFrameworkFinder => new TestFrameworkFinder(),
            TestFrameworkExtraOptionsFilter::class => static fn (): TestFrameworkExtraOptionsFilter => new TestFrameworkExtraOptionsFilter(),
            AdapterInstallationDecider::class => static fn (): AdapterInstallationDecider => new AdapterInstallationDecider(new QuestionHelper()),
            AdapterInstaller::class => static fn (): AdapterInstaller => new AdapterInstaller(new ComposerExecutableFinder()),
            MutantExecutionResultFactory::class => static fn (self $container): MutantExecutionResultFactory => new MutantExecutionResultFactory($container->getTestFrameworkAdapter()),
            FormatterFactory::class => static fn (self $container): FormatterFactory => new FormatterFactory($container->getOutput()),
            DiffSourceCodeMatcher::class => static fn (): DiffSourceCodeMatcher => new DiffSourceCodeMatcher(),
            ShellCommandLineExecutor::class => static fn (): ShellCommandLineExecutor => new ShellCommandLineExecutor(),
            GitDiffFileProvider::class => static fn (self $container): GitDiffFileProvider => new GitDiffFileProvider($container->getShellCommandLineExecutor()),
        ]);

        return $container->withValues(
            new NullLogger(),
            new NullOutput(),
            self::DEFAULT_CONFIG_FILE,
            self::DEFAULT_MUTATORS_INPUT,
            self::DEFAULT_SHOW_MUTATIONS,
            self::DEFAULT_LOG_VERBOSITY,
            self::DEFAULT_DEBUG,
            self::DEFAULT_ONLY_COVERED,
            self::DEFAULT_FORMATTER_NAME,
            self::DEFAULT_NO_PROGRESS,
            self::DEFAULT_FORCE_PROGRESS,
            self::DEFAULT_EXISTING_COVERAGE_PATH,
            self::DEFAULT_INITIAL_TESTS_PHP_OPTIONS,
            self::DEFAULT_SKIP_INITIAL_TESTS,
            self::DEFAULT_IGNORE_MSI_WITH_NO_MUTATIONS,
            self::DEFAULT_MIN_MSI,
            self::DEFAULT_MIN_COVERED_MSI,
            self::DEFAULT_MSI_PRECISION,
            self::DEFAULT_TEST_FRAMEWORK,
            self::DEFAULT_TEST_FRAMEWORK_EXTRA_OPTIONS,
            self::DEFAULT_FILTER,
            self::DEFAULT_THREAD_COUNT,
            self::DEFAULT_DRY_RUN,
            self::DEFAULT_GIT_DIFF_FILTER,
            self::DEFAULT_GIT_DIFF_LINES,
            self::DEFAULT_GIT_DIFF_BASE,
            self::DEFAULT_USE_GITHUB_LOGGER,
            self::DEFAULT_GITLAB_LOGGER_PATH,
            self::DEFAULT_HTML_LOGGER_PATH,
            self::DEFAULT_USE_NOOP_MUTATORS,
            self::DEFAULT_EXECUTE_ONLY_COVERING_TEST_CASES,
            self::DEFAULT_MAP_SOURCE_CLASS_TO_TEST_STRATEGY,
            self::DEFAULT_LOGGER_PROJECT_ROOT_DIRECTORY,
        );
    }

    public function withValues(
        LoggerInterface $logger,
        OutputInterface $output,
        ?string $configFile,
        string $mutatorsInput,
        bool $showMutations,
        string $logVerbosity,
        bool $debug,
        bool $onlyCovered,
        string $formatterName,
        bool $noProgress,
        bool $forceProgress,
        ?string $existingCoveragePath,
        ?string $initialTestsPhpOptions,
        bool $skipInitialTests,
        bool $ignoreMsiWithNoMutations,
        ?float $minMsi,
        ?float $minCoveredMsi,
        int $msiPrecision,
        ?string $testFramework,
        ?string $testFrameworkExtraOptions,
        string $filter,
        int $threadCount,
        bool $dryRun,
        ?string $gitDiffFilter,
        bool $isForGitDiffLines,
        ?string $gitDiffBase,
        ?bool $useGitHubLogger,
        ?string $gitlabLogFilePath,
        ?string $htmlLogFilePath,
        bool $useNoopMutators,
        bool $executeOnlyCoveringTestCases,
        ?string $mapSourceClassToTestStrategy,
        ?string $loggerProjectRootDirectory,
    ): self {
        $clone = clone $this;

        if ($forceProgress) {
            Assert::false($noProgress, 'Cannot force progress and set no progress at the same time');
        }

        $clone->offsetSet(
            CiDetector::class,
            static fn (): CiDetector => $forceProgress ? new NullCiDetector() : new MemoizedCiDetector(),
        );

        $clone->offsetSet(
            LoggerInterface::class,
            static fn (): LoggerInterface => $logger,
        );

        $clone->offsetSet(
            SchemaConfiguration::class,
            static fn (self $container): SchemaConfiguration => $container->getSchemaConfigurationLoader()->loadConfiguration(
                array_filter(
                    [
                        $configFile,
                        ...SchemaConfigurationLoader::POSSIBLE_DEFAULT_CONFIG_FILES,
                    ],
                ),
            ),
        );

        $clone->offsetSet(
            OutputInterface::class,
            static fn (): OutputInterface => $output,
        );

        $clone->offsetSet(
            OutputFormatter::class,
            static fn (self $container): OutputFormatter => $container->getFormatterFactory()->create($formatterName),
        );

        $clone->offsetSet(
            Configuration::class,
            static fn (self $container): Configuration => $container->getConfigurationFactory()->create(
                $container->getSchemaConfiguration(),
                $existingCoveragePath,
                $initialTestsPhpOptions,
                $skipInitialTests,
                $logVerbosity,
                $debug,
                $onlyCovered,
                $noProgress,
                $ignoreMsiWithNoMutations,
                $minMsi,
                $showMutations,
                $minCoveredMsi,
                $msiPrecision,
                $mutatorsInput,
                $testFramework,
                $testFrameworkExtraOptions,
                $filter,
                $threadCount,
                $dryRun,
                $gitDiffFilter,
                $isForGitDiffLines,
                $gitDiffBase,
                $useGitHubLogger,
                $gitlabLogFilePath,
                $htmlLogFilePath,
                $useNoopMutators,
                $executeOnlyCoveringTestCases,
                $mapSourceClassToTestStrategy,
                $loggerProjectRootDirectory,
            ),
        );

        return $clone;
    }

    public function getProjectDir(): string
    {
        // TODO: cache that result
        return getcwd();
    }

    public function getFileSystem(): Filesystem
    {
        return $this->get(Filesystem::class);
    }

    public function getTmpDirProvider(): TmpDirProvider
    {
        return $this->get(TmpDirProvider::class);
    }

    public function getDefaultJUnitFilePath(): string
    {
        return $this->defaultJUnitPath ??= sprintf(
            '%s/%s',
            Path::canonicalize(
                $this->getConfiguration()->getCoveragePath(),
            ),
            'junit.xml',
        );
    }

    public function getJUnitReportLocator(): JUnitReportLocator
    {
        return $this->get(JUnitReportLocator::class);
    }

    public function getIndexXmlCoverageParser(): IndexXmlCoverageParser
    {
        return $this->get(IndexXmlCoverageParser::class);
    }

    public function getXmlCoverageParser(): XmlCoverageParser
    {
        return $this->get(XmlCoverageParser::class);
    }

    public function getCoveredTraceProvider(): CoveredTraceProvider
    {
        return $this->get(CoveredTraceProvider::class);
    }

    public function getUnionTraceProvider(): UnionTraceProvider
    {
        return $this->get(UnionTraceProvider::class);
    }

    public function getSourceFileFilter(): SourceFileFilter
    {
        return $this->get(SourceFileFilter::class);
    }

    public function getBufferedSourceFileFilter(): BufferedSourceFileFilter
    {
        return $this->get(BufferedSourceFileFilter::class);
    }

    public function getUncoveredTraceProvider(): UncoveredTraceProvider
    {
        return $this->get(UncoveredTraceProvider::class);
    }

    public function getJUnitTestExecutionInfoAdder(): JUnitTestExecutionInfoAdder
    {
        return $this->get(JUnitTestExecutionInfoAdder::class);
    }

    public function getPhpUnitXmlCoverageTraceProvider(): PhpUnitXmlCoverageTraceProvider
    {
        return $this->get(PhpUnitXmlCoverageTraceProvider::class);
    }

    public function getIndexXmlCoverageLocator(): IndexXmlCoverageLocator
    {
        return $this->get(IndexXmlCoverageLocator::class);
    }

    public function getRootsFileOrDirectoryLocator(): RootsFileOrDirectoryLocator
    {
        return $this->get(RootsFileOrDirectoryLocator::class);
    }

    public function getFactory(): Factory
    {
        return $this->get(Factory::class);
    }

    public function getMutantCodeFactory(): MutantCodeFactory
    {
        return $this->get(MutantCodeFactory::class);
    }

    public function getMutantFactory(): MutantFactory
    {
        return $this->get(MutantFactory::class);
    }

    public function getDiffer(): Differ
    {
        return $this->get(Differ::class);
    }

    public function getEventDispatcher(): EventDispatcher
    {
        return $this->get(SyncEventDispatcher::class);
    }

    public function getProcessRunner(): ProcessRunner
    {
        $config = $this->getConfiguration();

        return $config->isDryRun()
            ? $this->get(DryProcessRunner::class)
            : $this->get(ParallelProcessRunner::class)
        ;
    }

    public function getTestFrameworkConfigLocator(): TestFrameworkConfigLocator
    {
        return $this->get(TestFrameworkConfigLocator::class);
    }

    public function getDiffColorizer(): DiffColorizer
    {
        return $this->get(DiffColorizer::class);
    }

    public function getMemoizedTestFileDataProvider(): MemoizedTestFileDataProvider
    {
        return $this->get(MemoizedTestFileDataProvider::class);
    }

    public function getParser(): Parser
    {
        return $this->get(Parser::class);
    }

    public function getFileParser(): FileParser
    {
        return $this->get(FileParser::class);
    }

    public function getPrinter(): PrettyPrinterAbstract
    {
        return $this->get(PrettyPrinterAbstract::class);
    }

    public function getMetricsCalculator(): MetricsCalculator
    {
        return $this->get(MetricsCalculator::class);
    }

    public function getResultsCollector(): ResultsCollector
    {
        return $this->get(ResultsCollector::class);
    }

    public function getStopwatch(): Stopwatch
    {
        return $this->get(Stopwatch::class);
    }

    public function getTimeFormatter(): TimeFormatter
    {
        return $this->get(TimeFormatter::class);
    }

    public function getMemoryFormatter(): MemoryFormatter
    {
        return $this->get(MemoryFormatter::class);
    }

    public function getMemoryLimiter(): MemoryLimiter
    {
        return $this->get(MemoryLimiter::class);
    }

    public function getSchemaConfigurationLoader(): SchemaConfigurationLoader
    {
        return $this->get(SchemaConfigurationLoader::class);
    }

    public function getRootsFileLocator(): RootsFileLocator
    {
        return $this->get(RootsFileLocator::class);
    }

    public function getSchemaConfigurationFileLoader(): SchemaConfigurationFileLoader
    {
        return $this->get(SchemaConfigurationFileLoader::class);
    }

    public function getSchemaValidator(): SchemaValidator
    {
        return $this->get(SchemaValidator::class);
    }

    public function getSchemaConfigurationFactory(): SchemaConfigurationFactory
    {
        return $this->get(SchemaConfigurationFactory::class);
    }

    public function getConfigurationFactory(): ConfigurationFactory
    {
        return $this->get(ConfigurationFactory::class);
    }

    public function getMutatorResolver(): MutatorResolver
    {
        return $this->get(MutatorResolver::class);
    }

    public function getMutatorFactory(): MutatorFactory
    {
        return $this->get(MutatorFactory::class);
    }

    public function getMutatorParser(): MutatorParser
    {
        return $this->get(MutatorParser::class);
    }

    public function getCoverageChecker(): CoverageChecker
    {
        return $this->get(CoverageChecker::class);
    }

    public function getMinMsiChecker(): MinMsiChecker
    {
        return $this->get(MinMsiChecker::class);
    }

    public function getSubscriberRegisterer(): SubscriberRegisterer
    {
        return $this->get(SubscriberRegisterer::class);
    }

    public function getSubscriberFactoryRegistry(): ChainSubscriberFactory
    {
        return $this->get(ChainSubscriberFactory::class);
    }

    public function getCleanUpAfterMutationTestingFinishedSubscriberFactory(): CleanUpAfterMutationTestingFinishedSubscriberFactory
    {
        return $this->get(CleanUpAfterMutationTestingFinishedSubscriberFactory::class);
    }

    public function getStopInfectionOnSigintSignalSubscriberFactory(): StopInfectionOnSigintSignalSubscriberFactory
    {
        return $this->get(StopInfectionOnSigintSignalSubscriberFactory::class);
    }

    public function getDispatchPcntlSignalSubscriberFactory(): DispatchPcntlSignalSubscriberFactory
    {
        return $this->get(DispatchPcntlSignalSubscriberFactory::class);
    }

    public function getInitialTestsConsoleLoggerSubscriberFactory(): InitialTestsConsoleLoggerSubscriberFactory
    {
        return $this->get(InitialTestsConsoleLoggerSubscriberFactory::class);
    }

    public function getMutationGeneratingConsoleLoggerSubscriberFactory(): MutationGeneratingConsoleLoggerSubscriberFactory
    {
        return $this->get(MutationGeneratingConsoleLoggerSubscriberFactory::class);
    }

    public function getMutationTestingResultsCollectorSubscriberFactory(): MutationTestingResultsCollectorSubscriberFactory
    {
        return $this->get(MutationTestingResultsCollectorSubscriberFactory::class);
    }

    public function getMutationTestingConsoleLoggerSubscriberFactory(): MutationTestingConsoleLoggerSubscriberFactory
    {
        return $this->get(MutationTestingConsoleLoggerSubscriberFactory::class);
    }

    public function getMutationTestingResultsLoggerSubscriberFactory(): MutationTestingResultsLoggerSubscriberFactory
    {
        return $this->get(MutationTestingResultsLoggerSubscriberFactory::class);
    }

    public function getPerformanceLoggerSubscriberFactory(): PerformanceLoggerSubscriberFactory
    {
        return $this->get(PerformanceLoggerSubscriberFactory::class);
    }

    public function getSourceFileCollector(): SourceFileCollector
    {
        return $this->get(SourceFileCollector::class);
    }

    public function getNodeTraverserFactory(): NodeTraverserFactory
    {
        return $this->get(NodeTraverserFactory::class);
    }

    public function getFileMutationGenerator(): FileMutationGenerator
    {
        return $this->get(FileMutationGenerator::class);
    }

    public function getFileLoggerFactory(): FileLoggerFactory
    {
        return $this->get(FileLoggerFactory::class);
    }

    public function getStrykerLoggerFactory(): StrykerLoggerFactory
    {
        return $this->get(StrykerLoggerFactory::class);
    }

    public function getMutationTestingResultsLogger(): MutationTestingResultsLogger
    {
        return $this->get(MutationTestingResultsLogger::class);
    }

    public function getTargetDetectionStatusesProvider(): TargetDetectionStatusesProvider
    {
        return $this->get(TargetDetectionStatusesProvider::class);
    }

    public function getFilteringResultsCollectorFactory(): FilteringResultsCollectorFactory
    {
        return $this->get(FilteringResultsCollectorFactory::class);
    }

    public function getTestFrameworkAdapter(): TestFrameworkAdapter
    {
        return $this->get(TestFrameworkAdapter::class);
    }

    public function getInitialTestRunProcessFactory(): InitialTestsRunProcessFactory
    {
        return $this->get(InitialTestsRunProcessFactory::class);
    }

    public function getInitialTestsRunner(): InitialTestsRunner
    {
        return $this->get(InitialTestsRunner::class);
    }

    public function getMutantProcessFactory(): MutantProcessFactory
    {
        return $this->get(MutantProcessFactory::class);
    }

    public function getMutationGenerator(): MutationGenerator
    {
        return $this->get(MutationGenerator::class);
    }

    public function getMutationTestingRunner(): MutationTestingRunner
    {
        return $this->get(MutationTestingRunner::class);
    }

    public function getSchemaConfiguration(): SchemaConfiguration
    {
        return $this->get(SchemaConfiguration::class);
    }

    public function getConfiguration(): Configuration
    {
        return $this->get(Configuration::class);
    }

    public function getLineRangeCalculator(): LineRangeCalculator
    {
        return $this->get(LineRangeCalculator::class);
    }

    public function getFilesDiffChangedLines(): FilesDiffChangedLines
    {
        return $this->get(FilesDiffChangedLines::class);
    }

    public function getDiffChangedLinesParser(): DiffChangedLinesParser
    {
        return $this->get(DiffChangedLinesParser::class);
    }

    public function getTestFrameworkFinder(): TestFrameworkFinder
    {
        return $this->get(TestFrameworkFinder::class);
    }

    public function getTestFrameworkExtraOptionsFilter(): TestFrameworkExtraOptionsFilter
    {
        return $this->get(TestFrameworkExtraOptionsFilter::class);
    }

    public function getAdapterInstallationDecider(): AdapterInstallationDecider
    {
        return $this->get(AdapterInstallationDecider::class);
    }

    public function getAdapterInstaller(): AdapterInstaller
    {
        return $this->get(AdapterInstaller::class);
    }

    public function getMutantExecutionResultFactory(): MutantExecutionResultFactory
    {
        return $this->get(MutantExecutionResultFactory::class);
    }

    public function getCiDetector(): CiDetector
    {
        return $this->get(CiDetector::class);
    }

    public function getLogger(): LoggerInterface
    {
        return $this->get(LoggerInterface::class);
    }

    public function getOutput(): OutputInterface
    {
        return $this->get(OutputInterface::class);
    }

    public function getFormatterFactory(): FormatterFactory
    {
        return $this->get(FormatterFactory::class);
    }

    public function getOutputFormatter(): OutputFormatter
    {
        return $this->get(OutputFormatter::class);
    }

    public function getDiffSourceCodeMatcher(): DiffSourceCodeMatcher
    {
        return $this->get(DiffSourceCodeMatcher::class);
    }

    public function getShellCommandLineExecutor(): ShellCommandLineExecutor
    {
        return $this->get(ShellCommandLineExecutor::class);
    }

    public function getGitDiffFileProvider(): GitDiffFileProvider
    {
        return $this->get(GitDiffFileProvider::class);
    }

    public function getStrykerHtmlReportBuilder(): StrykerHtmlReportBuilder
    {
        return $this->get(StrykerHtmlReportBuilder::class);
    }

    /**
     * @param class-string<object> $id
     * @param Closure(self): object $value
     */
    private function offsetSet(string $id, Closure $value): void
    {
        $this->keys[$id] = true;
        $this->factories[$id] = $value;
        unset($this->values[$id]);
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $id
     * @phpstan-return T
     */
    private function get(string $id): object
    {
        if (!isset($this->keys[$id])) {
            throw new InvalidArgumentException(sprintf('Unknown service "%s"', $id));
        }

        if (array_key_exists($id, $this->values)) {
            $value = $this->values[$id];
        } else {
            $value = $this->values[$id] = $this->factories[$id]($this);
        }

        Assert::isInstanceOf($value, $id);

        return $value;
    }
}
