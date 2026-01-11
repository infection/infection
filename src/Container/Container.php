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

namespace Infection\Container;

use function array_filter;
use DIContainer\Container as DIContainer;
use Infection\AbstractTestFramework\TestFrameworkAdapter;
use Infection\CI\MemoizedCiDetector;
use Infection\CI\NullCiDetector;
use Infection\Configuration\Configuration;
use Infection\Configuration\ConfigurationFactory;
use Infection\Configuration\Schema\SchemaConfiguration;
use Infection\Configuration\Schema\SchemaConfigurationFileLoader;
use Infection\Configuration\Schema\SchemaConfigurationLoader;
use Infection\Configuration\SourceFilter\GitDiffFilter;
use Infection\Configuration\SourceFilter\IncompleteGitDiffFilter;
use Infection\Configuration\SourceFilter\PlainFilter;
use Infection\Console\Input\MsiParser;
use Infection\Console\LogVerbosity;
use Infection\Console\OutputFormatter\FormatterFactory;
use Infection\Console\OutputFormatter\FormatterName;
use Infection\Console\OutputFormatter\OutputFormatter;
use Infection\Container\Builder\IndexXmlCoverageParserBuilder;
use Infection\Differ\DiffColorizer;
use Infection\Differ\Differ;
use Infection\Differ\DiffSourceCodeMatcher;
use Infection\Event\EventDispatcher\EventDispatcher;
use Infection\Event\EventDispatcher\SyncEventDispatcher;
use Infection\Event\Subscriber\ChainSubscriberFactory;
use Infection\Event\Subscriber\CleanUpAfterMutationTestingFinishedSubscriberFactory;
use Infection\Event\Subscriber\DispatchPcntlSignalSubscriberFactory;
use Infection\Event\Subscriber\InitialStaticAnalysisRunConsoleLoggerSubscriberFactory;
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
use Infection\FileSystem\FileStore;
use Infection\FileSystem\FileSystem;
use Infection\FileSystem\Finder\ComposerExecutableFinder;
use Infection\FileSystem\Finder\ConcreteComposerExecutableFinder;
use Infection\FileSystem\Finder\MemoizedComposerExecutableFinder;
use Infection\FileSystem\Finder\StaticAnalysisToolExecutableFinder;
use Infection\FileSystem\Finder\TestFrameworkFinder;
use Infection\FileSystem\Locator\FileOrDirectoryNotFound;
use Infection\FileSystem\Locator\RootsFileLocator;
use Infection\FileSystem\Locator\RootsFileOrDirectoryLocator;
use Infection\FileSystem\ProjectDirProvider;
use Infection\Git\CommandLineGit;
use Infection\Git\Git;
use Infection\Logger\FederatedLogger;
use Infection\Logger\FileLoggerFactory;
use Infection\Logger\Html\StrykerHtmlReportBuilder;
use Infection\Logger\MutationTestingResultsLogger;
use Infection\Logger\StrykerLoggerFactory;
use Infection\Metrics\FilteringResultsCollectorFactory;
use Infection\Metrics\MaxTimeoutsChecker;
use Infection\Metrics\MetricsCalculator;
use Infection\Metrics\MinMsiChecker;
use Infection\Metrics\ResultsCollector;
use Infection\Metrics\TargetDetectionStatusesProvider;
use Infection\Mutant\MutantCodeFactory;
use Infection\Mutant\MutantCodePrinter;
use Infection\Mutant\MutantFactory;
use Infection\Mutant\TestFrameworkMutantExecutionResultFactory;
use Infection\Mutation\FileMutationGenerator;
use Infection\Mutation\MutationGenerator;
use Infection\Mutator\MutatorFactory;
use Infection\Mutator\MutatorResolver;
use Infection\PhpParser\FileParser;
use Infection\PhpParser\NodeTraverserFactory;
use Infection\Process\Factory\InitialStaticAnalysisProcessFactory;
use Infection\Process\Factory\InitialTestsRunProcessFactory;
use Infection\Process\Factory\MutantProcessContainerFactory;
use Infection\Process\Runner\DryProcessRunner;
use Infection\Process\Runner\InitialStaticAnalysisRunner;
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
use Infection\Source\Collector\CachedSourceCollector;
use Infection\Source\Collector\LazySourceCollector;
use Infection\Source\Collector\SourceCollector;
use Infection\Source\Collector\SourceCollectorFactory;
use Infection\Source\Exception\NoSourceFound;
use Infection\Source\Matcher\GitDiffSourceLineMatcher;
use Infection\Source\Matcher\NullSourceLineMatcher;
use Infection\Source\Matcher\SourceLineMatcher;
use Infection\StaticAnalysis\Config\StaticAnalysisConfigLocator;
use Infection\StaticAnalysis\StaticAnalysisToolAdapter;
use Infection\StaticAnalysis\StaticAnalysisToolFactory;
use Infection\TestFramework\AdapterInstallationDecider;
use Infection\TestFramework\AdapterInstaller;
use Infection\TestFramework\Config\TestFrameworkConfigLocator;
use Infection\TestFramework\Coverage\CoverageChecker;
use Infection\TestFramework\Coverage\CoveredTraceProvider;
use Infection\TestFramework\Coverage\JUnit\JUnitReportLocator;
use Infection\TestFramework\Coverage\JUnit\JUnitTestExecutionInfoAdder;
use Infection\TestFramework\Coverage\JUnit\JUnitTestFileDataProvider;
use Infection\TestFramework\Coverage\JUnit\MemoizedTestFileDataProvider;
use Infection\TestFramework\Coverage\JUnit\TestFileDataProvider;
use Infection\TestFramework\Coverage\XmlReport\IndexXmlCoverageLocator;
use Infection\TestFramework\Coverage\XmlReport\IndexXmlCoverageParser;
use Infection\TestFramework\Coverage\XmlReport\PhpUnitXmlCoverageTraceProvider;
use Infection\TestFramework\Coverage\XmlReport\XmlCoverageParser;
use Infection\TestFramework\Factory;
use Infection\TestFramework\TestFrameworkExtraOptionsFilter;
use Infection\TestFramework\Tracing\Trace\LineRangeCalculator;
use Infection\TestFramework\Tracing\TraceProvider;
use Infection\TestFramework\Tracing\TraceProviderAdapterTracer;
use Infection\TestFramework\Tracing\Tracer;
use OndraM\CiDetector\CiDetector;
use function php_ini_loaded_file;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use PhpParser\PrettyPrinterAbstract;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use SebastianBergmann\Diff\Differ as BaseDiffer;
use SebastianBergmann\Diff\Output\UnifiedDiffOutputBuilder;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Webmozart\Assert\Assert;

/**
 * @internal
 */
final class Container extends DIContainer
{
    public const DEFAULT_CONFIG_FILE = null;

    public const DEFAULT_MUTATORS_INPUT = '';

    public const DEFAULT_SHOW_MUTATIONS = 20;

    public const DEFAULT_LOG_VERBOSITY = LogVerbosity::NORMAL;

    public const DEFAULT_DEBUG = false;

    public const DEFAULT_WITH_UNCOVERED = false;

    public const DEFAULT_FORMATTER_NAME = FormatterName::DOT;

    public const DEFAULT_MUTANT_ID = null;

    public const DEFAULT_GIT_DIFF_FILTER = null;

    public const DEFAULT_GIT_DIFF_BASE = null;

    public const DEFAULT_USE_GITHUB_LOGGER = null;

    public const DEFAULT_GITLAB_LOGGER_PATH = null;

    public const DEFAULT_LOGGER_PROJECT_ROOT_DIRECTORY = null;

    public const DEFAULT_HTML_LOGGER_PATH = null;

    public const DEFAULT_TEXT_LOGGER_PATH = null;

    public const DEFAULT_SUMMARY_JSON_LOGGER_PATH = null;

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

    public const DEFAULT_TIMEOUTS_AS_ESCAPED = false;

    public const DEFAULT_MAX_TIMEOUTS = null;

    public const DEFAULT_MSI_PRECISION = MsiParser::DEFAULT_PRECISION;

    public const DEFAULT_TEST_FRAMEWORK = null;

    public const DEFAULT_STATIC_ANALYSIS_TOOL = null;

    public const DEFAULT_TEST_FRAMEWORK_EXTRA_OPTIONS = null;

    public const DEFAULT_STATIC_ANALYSIS_TOOL_OPTIONS = null;

    public const DEFAULT_FILTER = null;

    public const DEFAULT_THREAD_COUNT = null;

    public const DEFAULT_DRY_RUN = false;

    public const DEFAULT_MAP_SOURCE_CLASS_TO_TEST_STRATEGY = null;

    public static function create(): self
    {
        $container = new self([
            IndexXmlCoverageParser::class => IndexXmlCoverageParserBuilder::class,
            Tracer::class => static fn (self $container) => new TraceProviderAdapterTracer(
                $container->getTraceProvider(),
            ),
            TraceProvider::class => static fn (self $container): TraceProvider => new CoveredTraceProvider(
                $container->getPhpUnitXmlCoverageTraceProvider(),
                $container->getJUnitTestExecutionInfoAdder(),
            ),
            PhpUnitXmlCoverageTraceProvider::class => static fn (self $container): PhpUnitXmlCoverageTraceProvider => new PhpUnitXmlCoverageTraceProvider(
                $container->getIndexXmlCoverageLocator(),
                $container->getIndexXmlCoverageParser(),
                $container->getXmlCoverageParser(),
            ),
            IndexXmlCoverageLocator::class => static fn (self $container) => IndexXmlCoverageLocator::create(
                $container->getFileSystem(),
                $container->getConfiguration()->coveragePath,
            ),
            RootsFileOrDirectoryLocator::class => static fn (self $container): RootsFileOrDirectoryLocator => new RootsFileOrDirectoryLocator(
                [$container->getProjectDir()],
                $container->getFileSystem(),
            ),
            Factory::class => static function (self $container): Factory {
                $config = $container->getConfiguration();

                return new Factory(
                    $config->tmpDir,
                    $container->getProjectDir(),
                    $container->getTestFrameworkConfigLocator(),
                    $container->getTestFrameworkFinder(),
                    $container->getJUnitReportLocator()->getDefaultLocation(),
                    $config,
                    $container->getSourceCollector(),
                    GeneratedExtensionsConfig::EXTENSIONS,
                );
            },
            StaticAnalysisToolFactory::class => static function (self $container): StaticAnalysisToolFactory {
                $config = $container->getConfiguration();

                return new StaticAnalysisToolFactory(
                    $config,
                    $container->getStaticAnalysisToolExecutableFinder(),
                    $container->getStaticAnalysisConfigLocator(),
                );
            },
            MutantFactory::class => static fn (self $container): MutantFactory => new MutantFactory(
                $container->getConfiguration()->tmpDir,
                $container->getDiffer(),
                $container->getMutantCodeFactory(),
            ),
            MutantCodeFactory::class => static fn (self $container): MutantCodeFactory => new MutantCodeFactory(
                $container->getMutatedCodePrinter(),
            ),
            MutantCodePrinter::class => static fn (self $container): MutantCodePrinter => new MutantCodePrinter(
                $container->getPrinter(),
            ),
            Differ::class => static fn (): Differ => new Differ(new BaseDiffer(new UnifiedDiffOutputBuilder(''))),
            SyncEventDispatcher::class => static fn (): SyncEventDispatcher => new SyncEventDispatcher(),
            ParallelProcessRunner::class => static fn (self $container): ParallelProcessRunner => new ParallelProcessRunner(
                $container->getConfiguration()->threadCount,
            ),
            TestFrameworkConfigLocator::class => static fn (self $container): TestFrameworkConfigLocator => new TestFrameworkConfigLocator(
                (string) $container->getConfiguration()->phpUnit->configDir,
            ),
            StaticAnalysisConfigLocator::class => static fn (self $container): StaticAnalysisConfigLocator => new StaticAnalysisConfigLocator(
                (string) $container->getConfiguration()->phpStan->configDir,
            ),
            MemoizedTestFileDataProvider::class => static fn (self $container): TestFileDataProvider => new MemoizedTestFileDataProvider(
                new JUnitTestFileDataProvider($container->getJUnitReportLocator()),
            ),
            Parser::class => static fn (): Parser => (new ParserFactory())->createForHostVersion(),
            PrettyPrinterAbstract::class => static fn (): Standard => new Standard(),
            MetricsCalculator::class => static fn (self $container): MetricsCalculator => new MetricsCalculator(
                $container->getConfiguration()->msiPrecision,
                $container->getConfiguration()->timeoutsAsEscaped,
            ),
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
            CoverageChecker::class => static function (self $container): CoverageChecker {
                $config = $container->getConfiguration();
                $testFrameworkAdapter = $container->getTestFrameworkAdapter();

                return new CoverageChecker(
                    $config->skipCoverage,
                    $config->skipInitialTests,
                    $config->initialTestsPhpOptions ?? '',
                    $config->coveragePath,
                    $testFrameworkAdapter->hasJUnitReport(),
                    $container->getJUnitReportLocator(),
                    $testFrameworkAdapter->getName(),
                    $container->getIndexXmlCoverageLocator(),
                );
            },
            JUnitReportLocator::class => static fn (self $container) => JUnitReportLocator::create(
                $container->getFileSystem(),
                $container->getConfiguration()->coveragePath,
            ),
            MinMsiChecker::class => static function (self $container): MinMsiChecker {
                $config = $container->getConfiguration();

                return new MinMsiChecker(
                    $config->ignoreMsiWithNoMutations,
                    (float) $config->minMsi,
                    (float) $config->minCoveredMsi,
                );
            },
            MaxTimeoutsChecker::class => static fn (self $container): MaxTimeoutsChecker => new MaxTimeoutsChecker(
                $container->getConfiguration()->maxTimeouts,
            ),
            ChainSubscriberFactory::class => static function (self $container): ChainSubscriberFactory {
                $subscriberFactories = [
                    $container->getInitialTestsConsoleLoggerSubscriberFactory(),
                    $container->getMutationGeneratingConsoleLoggerSubscriberFactory(),
                    $container->getMutationTestingResultsCollectorSubscriberFactory(),
                    $container->getMutationTestingConsoleLoggerSubscriberFactory(),
                    $container->getMutationTestingResultsLoggerSubscriberFactory(),
                    $container->getPerformanceLoggerSubscriberFactory(),
                    $container->getCleanUpAfterMutationTestingFinishedSubscriberFactory(),
                    $container->getStopInfectionOnSigintSignalSubscriberFactory(),
                    $container->getDispatchPcntlSignalSubscriberFactory(),
                ];

                if ($container->getConfiguration()->isStaticAnalysisEnabled()) {
                    $subscriberFactories[] = $container->getInitialStaticAnalysisRunConsoleLoggerSubscriberFactory();
                }

                return new ChainSubscriberFactory(...$subscriberFactories);
            },
            CleanUpAfterMutationTestingFinishedSubscriberFactory::class => static function (self $container): CleanUpAfterMutationTestingFinishedSubscriberFactory {
                $config = $container->getConfiguration();

                return new CleanUpAfterMutationTestingFinishedSubscriberFactory(
                    $config->isDebugEnabled,
                    $container->getFileSystem(),
                    $config->tmpDir,
                );
            },
            InitialTestsConsoleLoggerSubscriberFactory::class => static function (self $container): InitialTestsConsoleLoggerSubscriberFactory {
                $config = $container->getConfiguration();

                return new InitialTestsConsoleLoggerSubscriberFactory(
                    $config->noProgress,
                    $container->getTestFrameworkAdapter(),
                    $config->isDebugEnabled,
                );
            },
            InitialStaticAnalysisRunConsoleLoggerSubscriberFactory::class => static function (self $container): InitialStaticAnalysisRunConsoleLoggerSubscriberFactory {
                $config = $container->getConfiguration();

                return new InitialStaticAnalysisRunConsoleLoggerSubscriberFactory(
                    $config->noProgress,
                    $config->isDebugEnabled,
                    $container->getStaticAnalysisToolAdapter(),
                );
            },
            MutationGeneratingConsoleLoggerSubscriberFactory::class => static fn (self $container): MutationGeneratingConsoleLoggerSubscriberFactory => new MutationGeneratingConsoleLoggerSubscriberFactory(
                $container->getConfiguration()->noProgress,
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
                    $config->numberOfShownMutations,
                    $container->getOutputFormatter(),
                    !$config->mutateOnlyCoveredCode(),
                    $config->timeoutsAsEscaped,
                );
            },
            PerformanceLoggerSubscriberFactory::class => static fn (self $container): PerformanceLoggerSubscriberFactory => new PerformanceLoggerSubscriberFactory(
                $container->getStopwatch(),
                $container->getTimeFormatter(),
                $container->getMemoryFormatter(),
                $container->getConfiguration()->threadCount,
            ),
            FileMutationGenerator::class => static fn (self $container): FileMutationGenerator => new FileMutationGenerator(
                $container->getFileParser(),
                $container->getNodeTraverserFactory(),
                $container->getLineRangeCalculator(),
                $container->getSourceLineMatcher(),
                $container->getTracer(),
                $container->getFileStore(),
            ),
            FileLoggerFactory::class => static function (self $container): FileLoggerFactory {
                $config = $container->getConfiguration();

                return new FileLoggerFactory(
                    $container->getMetricsCalculator(),
                    $container->getResultsCollector(),
                    $container->getFileSystem(),
                    $config->logVerbosity,
                    $config->isDebugEnabled,
                    $config->mutateOnlyCoveredCode(),
                    $container->getLogger(),
                    $container->getStrykerHtmlReportBuilder(),
                    $config->loggerProjectRootDirectory,
                    $config->processTimeout,
                );
            },
            MutationTestingResultsLogger::class => static fn (self $container): MutationTestingResultsLogger => new FederatedLogger(...array_filter([
                $container->getFileLoggerFactory()->createFromLogEntries(
                    $container->getConfiguration()->logs,
                ),
                $container->getStrykerLoggerFactory()->createFromLogEntries(
                    $container->getConfiguration()->logs,
                ),
            ])),
            TargetDetectionStatusesProvider::class => static function (self $container): TargetDetectionStatusesProvider {
                $config = $container->getConfiguration();

                return new TargetDetectionStatusesProvider(
                    $config->logs,
                    $config->logVerbosity,
                    $config->mutateOnlyCoveredCode(),
                    $config->numberOfShownMutations,
                    $config->timeoutsAsEscaped,
                );
            },
            TestFrameworkAdapter::class => static function (self $container): TestFrameworkAdapter {
                $config = $container->getConfiguration();

                return $container->getFactory()->create(
                    $config->testFramework,
                    $config->skipCoverage,
                );
            },
            StaticAnalysisToolAdapter::class => static function (self $container): StaticAnalysisToolAdapter {
                $config = $container->getConfiguration();

                Assert::notNull($config->staticAnalysisTool);

                return $container->getStaticAnalysisToolFactory()->create(
                    $config->staticAnalysisTool,
                    $config->processTimeout,
                );
            },
            InitialStaticAnalysisProcessFactory::class => static fn (self $container): InitialStaticAnalysisProcessFactory => new InitialStaticAnalysisProcessFactory(
                $container->getStaticAnalysisToolAdapter(),
            ),
            InitialStaticAnalysisRunner::class => static fn (self $container): InitialStaticAnalysisRunner => new InitialStaticAnalysisRunner(
                $container->getInitialStaticAnalysisProcessFactory(),
                $container->getEventDispatcher(),
            ),
            MutantProcessContainerFactory::class => static function (self $container): MutantProcessContainerFactory {
                $config = $container->getConfiguration();

                $mutantProcessKillerFactories = [];

                if ($config->isStaticAnalysisEnabled()) {
                    $mutantProcessKillerFactories[] = $container->getStaticAnalysisToolAdapter()->createMutantProcessFactory();
                }

                $configuration = $container->getConfiguration();

                return new MutantProcessContainerFactory(
                    $container->getTestFrameworkAdapter(),
                    $configuration->processTimeout,
                    $container->getMutantExecutionResultFactory(),
                    $mutantProcessKillerFactories,
                    $container->getConfiguration(),
                );
            },
            MutationGenerator::class => static function (self $container): MutationGenerator {
                $config = $container->getConfiguration();

                return new MutationGenerator(
                    $container->getSourceCollector(),
                    $config->mutators,
                    $container->getEventDispatcher(),
                    $container->getFileMutationGenerator(),
                );
            },
            MutationTestingRunner::class => static function (self $container): MutationTestingRunner {
                $configuration = $container->getConfiguration();

                return new MutationTestingRunner(
                    $container->getMutantProcessContainerFactory(),
                    $container->getMutantFactory(),
                    $container->getProcessRunner(),
                    $container->getEventDispatcher(),
                    $configuration->isDryRun
                        ? new DummyFileSystem()
                        : $container->getFileSystem(),
                    $container->getDiffSourceCodeMatcher(),
                    $configuration->noProgress,
                    $configuration->processTimeout,
                    $configuration->ignoreSourceCodeMutatorsMap,
                    $configuration->mutantId,
                );
            },
            MemoizedComposerExecutableFinder::class => static fn (): ComposerExecutableFinder => new MemoizedComposerExecutableFinder(new ConcreteComposerExecutableFinder()),
            Git::class => static fn (self $container): Git => new CommandLineGit(
                new ShellCommandLineExecutor(),
                $container->getLogger(),
            ),
            SourceLineMatcher::class => static function (self $container): SourceLineMatcher {
                $configuration = $container->getConfiguration();
                $sourceFilter = $configuration->sourceFilter;

                return $sourceFilter instanceof GitDiffFilter
                    ? new GitDiffSourceLineMatcher(
                        $container->getGit(),
                        $container->getFileSystem(),
                        $sourceFilter->base,
                        $sourceFilter->value,
                        $configuration->source->directories,
                    )
                    : new NullSourceLineMatcher();
            },
            SourceCollectorFactory::class => static fn (self $container): SourceCollectorFactory => new SourceCollectorFactory(
                $container->getGit(),
            ),
            SourceCollector::class => static fn (self $container): SourceCollector => new LazySourceCollector(
                static function () use ($container): SourceCollector {
                    $configuration = $container->getConfiguration();

                    return new CachedSourceCollector(
                        $container->get(SourceCollectorFactory::class)->create(
                            $configuration->configurationPathname,
                            $configuration->source,
                            $configuration->sourceFilter,
                        ),
                    );
                },
            ),
        ]);

        return $container->withValues(
            new NullLogger(),
            new NullOutput(),
        );
    }

    /**
     * @param non-empty-string|null $configFile
     */
    public function withValues(
        LoggerInterface $logger,
        OutputInterface $output,
        ?string $configFile = self::DEFAULT_CONFIG_FILE,
        string $mutatorsInput = self::DEFAULT_MUTATORS_INPUT,
        ?int $numberOfShownMutations = self::DEFAULT_SHOW_MUTATIONS,
        string $logVerbosity = self::DEFAULT_LOG_VERBOSITY,
        bool $debug = self::DEFAULT_DEBUG,
        bool $withUncovered = self::DEFAULT_WITH_UNCOVERED,
        FormatterName $formatterName = self::DEFAULT_FORMATTER_NAME,
        bool $noProgress = self::DEFAULT_NO_PROGRESS,
        bool $forceProgress = self::DEFAULT_FORCE_PROGRESS,
        ?string $existingCoveragePath = self::DEFAULT_EXISTING_COVERAGE_PATH,
        ?string $initialTestsPhpOptions = self::DEFAULT_INITIAL_TESTS_PHP_OPTIONS,
        bool $skipInitialTests = self::DEFAULT_SKIP_INITIAL_TESTS,
        ?bool $ignoreMsiWithNoMutations = self::DEFAULT_IGNORE_MSI_WITH_NO_MUTATIONS,
        ?float $minMsi = self::DEFAULT_MIN_MSI,
        ?float $minCoveredMsi = self::DEFAULT_MIN_COVERED_MSI,
        bool $timeoutsAsEscaped = self::DEFAULT_TIMEOUTS_AS_ESCAPED,
        ?int $maxTimeouts = self::DEFAULT_MAX_TIMEOUTS,
        int $msiPrecision = self::DEFAULT_MSI_PRECISION,
        ?string $testFramework = self::DEFAULT_TEST_FRAMEWORK,
        ?string $testFrameworkExtraOptions = self::DEFAULT_TEST_FRAMEWORK_EXTRA_OPTIONS,
        ?string $staticAnalysisToolOptions = self::DEFAULT_STATIC_ANALYSIS_TOOL_OPTIONS,
        PlainFilter|IncompleteGitDiffFilter|null $sourceFilter = null,
        ?int $threadCount = self::DEFAULT_THREAD_COUNT,
        bool $dryRun = self::DEFAULT_DRY_RUN,
        ?bool $useGitHubLogger = self::DEFAULT_USE_GITHUB_LOGGER,
        ?string $gitlabLogFilePath = self::DEFAULT_GITLAB_LOGGER_PATH,
        ?string $htmlLogFilePath = self::DEFAULT_HTML_LOGGER_PATH,
        ?string $textLogFilePath = self::DEFAULT_TEXT_LOGGER_PATH,
        ?string $summaryJsonLogFilePath = self::DEFAULT_SUMMARY_JSON_LOGGER_PATH,
        bool $useNoopMutators = self::DEFAULT_USE_NOOP_MUTATORS,
        bool $executeOnlyCoveringTestCases = self::DEFAULT_EXECUTE_ONLY_COVERING_TEST_CASES,
        ?string $mapSourceClassToTestStrategy = self::DEFAULT_MAP_SOURCE_CLASS_TO_TEST_STRATEGY,
        ?string $loggerProjectRootDirectory = self::DEFAULT_LOGGER_PROJECT_ROOT_DIRECTORY,
        ?string $staticAnalysisTool = self::DEFAULT_STATIC_ANALYSIS_TOOL,
        ?string $mutantId = self::DEFAULT_MUTANT_ID,
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
                        ...SchemaConfigurationLoader::POSSIBLE_DEFAULT_CONFIG_FILE_NAMES,
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
            /**
             * @throws FileOrDirectoryNotFound
             * @throws NoSourceFound
             */
            static fn (self $container): Configuration => $container->getConfigurationFactory()->create(
                schema: $container->getSchemaConfiguration(),
                existingCoveragePath: $existingCoveragePath,
                initialTestsPhpOptions: $initialTestsPhpOptions,
                skipInitialTests: $skipInitialTests,
                logVerbosity: $logVerbosity,
                debug: $debug,
                withUncovered: $withUncovered,
                noProgress: $noProgress,
                ignoreMsiWithNoMutations: $ignoreMsiWithNoMutations,
                minMsi: $minMsi,
                numberOfShownMutations: $numberOfShownMutations,
                minCoveredMsi: $minCoveredMsi,
                timeoutsAsEscaped: $timeoutsAsEscaped,
                maxTimeouts: $maxTimeouts,
                msiPrecision: $msiPrecision,
                mutatorsInput: $mutatorsInput,
                testFramework: $testFramework,
                testFrameworkExtraOptions: $testFrameworkExtraOptions,
                staticAnalysisToolOptions: $staticAnalysisToolOptions,
                sourceFilter: $sourceFilter,
                threadCount: $threadCount,
                dryRun: $dryRun,
                useGitHubLogger: $useGitHubLogger,
                gitlabLogFilePath: $gitlabLogFilePath,
                htmlLogFilePath: $htmlLogFilePath,
                textLogFilePath: $textLogFilePath,
                summaryJsonLogFilePath: $summaryJsonLogFilePath,
                useNoopMutators: $useNoopMutators,
                executeOnlyCoveringTestCases: $executeOnlyCoveringTestCases,
                mapSourceClassToTestStrategy: $mapSourceClassToTestStrategy,
                loggerProjectRootDirectory: $loggerProjectRootDirectory,
                staticAnalysisTool: $staticAnalysisTool,
                mutantId: $mutantId,
            ),
        );

        return $clone;
    }

    public function getFileSystem(): FileSystem
    {
        return $this->get(FileSystem::class);
    }

    public function getTracer(): Tracer
    {
        return $this->get(Tracer::class);
    }

    public function getTraceProvider(): TraceProvider
    {
        return $this->get(TraceProvider::class);
    }

    public function getDiffColorizer(): DiffColorizer
    {
        return $this->get(DiffColorizer::class);
    }

    public function getParser(): Parser
    {
        return $this->get(Parser::class);
    }

    public function getFileParser(): FileParser
    {
        return $this->get(FileParser::class);
    }

    public function getMetricsCalculator(): MetricsCalculator
    {
        return $this->get(MetricsCalculator::class);
    }

    public function getResultsCollector(): ResultsCollector
    {
        return $this->get(ResultsCollector::class);
    }

    public function getMemoryLimiter(): MemoryLimiter
    {
        return $this->get(MemoryLimiter::class);
    }

    public function getMutatorResolver(): MutatorResolver
    {
        return $this->get(MutatorResolver::class);
    }

    public function getMutatorFactory(): MutatorFactory
    {
        return $this->get(MutatorFactory::class);
    }

    public function getSubscriberRegisterer(): SubscriberRegisterer
    {
        return $this->get(SubscriberRegisterer::class);
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

    public function getInitialStaticAnalysisRunConsoleLoggerSubscriberFactory(): InitialStaticAnalysisRunConsoleLoggerSubscriberFactory
    {
        return $this->get(InitialStaticAnalysisRunConsoleLoggerSubscriberFactory::class);
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

    public function getSourceCollector(): SourceCollector
    {
        return $this->get(SourceCollector::class);
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

    public function getStaticAnalysisToolAdapter(): StaticAnalysisToolAdapter
    {
        return $this->get(StaticAnalysisToolAdapter::class);
    }

    public function getInitialTestRunProcessFactory(): InitialTestsRunProcessFactory
    {
        return $this->get(InitialTestsRunProcessFactory::class);
    }

    public function getInitialStaticAnalysisProcessFactory(): InitialStaticAnalysisProcessFactory
    {
        return $this->get(InitialStaticAnalysisProcessFactory::class);
    }

    public function getInitialTestsRunner(): InitialTestsRunner
    {
        return $this->get(InitialTestsRunner::class);
    }

    public function getInitialStaticAnalysisRunner(): InitialStaticAnalysisRunner
    {
        return $this->get(InitialStaticAnalysisRunner::class);
    }

    public function getMutantProcessContainerFactory(): MutantProcessContainerFactory
    {
        return $this->get(MutantProcessContainerFactory::class);
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

    // Should throw all the exceptions ConfigurationFactory::create() can throw.
    /**
     * @throws FileOrDirectoryNotFound
     * @throws NoSourceFound
     */
    public function getConfiguration(): Configuration
    {
        return $this->get(Configuration::class);
    }

    public function getLineRangeCalculator(): LineRangeCalculator
    {
        return $this->get(LineRangeCalculator::class);
    }

    public function getSourceLineMatcher(): SourceLineMatcher
    {
        return $this->get(SourceLineMatcher::class);
    }

    public function getTestFrameworkFinder(): TestFrameworkFinder
    {
        return $this->get(TestFrameworkFinder::class);
    }

    public function getStaticAnalysisToolExecutableFinder(): StaticAnalysisToolExecutableFinder
    {
        return $this->get(StaticAnalysisToolExecutableFinder::class);
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

    public function getMutantExecutionResultFactory(): TestFrameworkMutantExecutionResultFactory
    {
        return $this->get(TestFrameworkMutantExecutionResultFactory::class);
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

    public function getGit(): Git
    {
        return $this->get(Git::class);
    }

    public function getStrykerHtmlReportBuilder(): StrykerHtmlReportBuilder
    {
        return $this->get(StrykerHtmlReportBuilder::class);
    }

    public function getComposerExecutableFinder(): ComposerExecutableFinder
    {
        return $this->get(MemoizedComposerExecutableFinder::class);
    }

    public function getMutantCodeFactory(): MutantCodeFactory
    {
        return $this->get(MutantCodeFactory::class);
    }

    public function getRootsFileOrDirectoryLocator(): RootsFileOrDirectoryLocator
    {
        return $this->get(RootsFileOrDirectoryLocator::class);
    }

    public function getCoverageChecker(): CoverageChecker
    {
        return $this->get(CoverageChecker::class);
    }

    public function getEventDispatcher(): EventDispatcher
    {
        return $this->get(SyncEventDispatcher::class);
    }

    public function getMinMsiChecker(): MinMsiChecker
    {
        return $this->get(MinMsiChecker::class);
    }

    public function getMaxTimeoutsChecker(): MaxTimeoutsChecker
    {
        return $this->get(MaxTimeoutsChecker::class);
    }

    public function getFileStore(): FileStore
    {
        return $this->get(FileStore::class);
    }

    private function getMutatedCodePrinter(): MutantCodePrinter
    {
        return $this->get(MutantCodePrinter::class);
    }

    private function getStopwatch(): Stopwatch
    {
        return $this->get(Stopwatch::class);
    }

    private function getTimeFormatter(): TimeFormatter
    {
        return $this->get(TimeFormatter::class);
    }

    private function getMemoryFormatter(): MemoryFormatter
    {
        return $this->get(MemoryFormatter::class);
    }

    private function getSchemaConfigurationLoader(): SchemaConfigurationLoader
    {
        return $this->get(SchemaConfigurationLoader::class);
    }

    private function getRootsFileLocator(): RootsFileLocator
    {
        return $this->get(RootsFileLocator::class);
    }

    private function getSchemaConfigurationFileLoader(): SchemaConfigurationFileLoader
    {
        return $this->get(SchemaConfigurationFileLoader::class);
    }

    private function getConfigurationFactory(): ConfigurationFactory
    {
        return $this->get(ConfigurationFactory::class);
    }

    private function getPrinter(): PrettyPrinterAbstract
    {
        return $this->get(PrettyPrinterAbstract::class);
    }

    private function getTestFrameworkConfigLocator(): TestFrameworkConfigLocator
    {
        return $this->get(TestFrameworkConfigLocator::class);
    }

    private function getStaticAnalysisConfigLocator(): StaticAnalysisConfigLocator
    {
        return $this->get(StaticAnalysisConfigLocator::class);
    }

    private function getProcessRunner(): ProcessRunner
    {
        $config = $this->getConfiguration();

        return $config->isDryRun
            ? $this->get(DryProcessRunner::class)
            : $this->get(ParallelProcessRunner::class)
        ;
    }

    private function getDiffer(): Differ
    {
        return $this->get(Differ::class);
    }

    private function getMutantFactory(): MutantFactory
    {
        return $this->get(MutantFactory::class);
    }

    private function getFactory(): Factory
    {
        return $this->get(Factory::class);
    }

    private function getStaticAnalysisToolFactory(): StaticAnalysisToolFactory
    {
        return $this->get(StaticAnalysisToolFactory::class);
    }

    private function getJUnitTestExecutionInfoAdder(): JUnitTestExecutionInfoAdder
    {
        return $this->get(JUnitTestExecutionInfoAdder::class);
    }

    private function getPhpUnitXmlCoverageTraceProvider(): PhpUnitXmlCoverageTraceProvider
    {
        return $this->get(PhpUnitXmlCoverageTraceProvider::class);
    }

    private function getIndexXmlCoverageLocator(): IndexXmlCoverageLocator
    {
        return $this->get(IndexXmlCoverageLocator::class);
    }

    private function getProjectDir(): string
    {
        return $this->get(ProjectDirProvider::class)->getProjectDir();
    }

    private function getJUnitReportLocator(): JUnitReportLocator
    {
        return $this->get(JUnitReportLocator::class);
    }

    private function getIndexXmlCoverageParser(): IndexXmlCoverageParser
    {
        return $this->get(IndexXmlCoverageParser::class);
    }

    private function getXmlCoverageParser(): XmlCoverageParser
    {
        return $this->get(XmlCoverageParser::class);
    }

    /**
     * @param class-string<object> $id
     * @param callable(static): object $value
     */
    private function offsetSet(string $id, callable $value): void
    {
        $this->set($id, $value);
    }
}
