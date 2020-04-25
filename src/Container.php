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

use Infection\CI\MemoizedCiDetector;
use function array_filter;
use function array_key_exists;
use Closure;
use Infection\AbstractTestFramework\TestFrameworkAdapter;
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
use Infection\Differ\DiffColorizer;
use Infection\Differ\Differ;
use Infection\Event\EventDispatcher\EventDispatcher;
use Infection\Event\EventDispatcher\SyncEventDispatcher;
use Infection\Event\Subscriber\ChainSubscriberFactory;
use Infection\Event\Subscriber\CleanUpAfterMutationTestingFinishedSubscriberFactory;
use Infection\Event\Subscriber\InitialTestsConsoleLoggerSubscriberFactory;
use Infection\Event\Subscriber\MutationGeneratingConsoleLoggerSubscriberFactory;
use Infection\Event\Subscriber\MutationTestingConsoleLoggerSubscriberFactory;
use Infection\Event\Subscriber\MutationTestingResultsLoggerSubscriberFactory;
use Infection\Event\Subscriber\PerformanceLoggerSubscriberFactory;
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
use Infection\Logger\LoggerFactory;
use Infection\Metrics\MetricsCalculator;
use Infection\Metrics\MinMsiChecker;
use Infection\Mutant\MutantCodeFactory;
use Infection\Mutant\MutantExecutionResultFactory;
use Infection\Mutant\MutantFactory;
use Infection\Mutation\FileMutationGenerator;
use Infection\Mutation\MutationAttributeKeys;
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
use Infection\Resource\Memory\MemoryFormatter;
use Infection\Resource\Memory\MemoryLimiter;
use Infection\Resource\Memory\MemoryLimiterEnvironment;
use Infection\Resource\Time\Stopwatch;
use Infection\Resource\Time\TimeFormatter;
use Infection\TestFramework\AdapterInstallationDecider;
use Infection\TestFramework\AdapterInstaller;
use Infection\TestFramework\CommandLineBuilder;
use Infection\TestFramework\Config\TestFrameworkConfigLocator;
use Infection\TestFramework\Coverage\CoverageChecker;
use Infection\TestFramework\Coverage\FilteredEnrichedTraceProvider;
use Infection\TestFramework\Coverage\JUnit\JUnitReportLocator;
use Infection\TestFramework\Coverage\JUnit\JUnitTestExecutionInfoAdder;
use Infection\TestFramework\Coverage\JUnit\JUnitTestFileDataProvider;
use Infection\TestFramework\Coverage\JUnit\MemoizedTestFileDataProvider;
use Infection\TestFramework\Coverage\JUnit\TestFileDataProvider;
use Infection\TestFramework\Coverage\LineRangeCalculator;
use Infection\TestFramework\Coverage\XmlReport\IndexXmlCoverageLocator;
use Infection\TestFramework\Coverage\XmlReport\IndexXmlCoverageParser;
use Infection\TestFramework\Coverage\XmlReport\PhpUnitXmlCoverageTraceProvider;
use Infection\TestFramework\Coverage\XmlReport\XmlCoverageParser;
use Infection\TestFramework\Factory;
use Infection\TestFramework\TestFrameworkExtraOptionsFilter;
use InvalidArgumentException;
use OndraM\CiDetector\CiDetector;
use function php_ini_loaded_file;
use PhpParser\Lexer;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use PhpParser\PrettyPrinterAbstract;
use function Safe\getcwd;
use function Safe\sprintf;
use SebastianBergmann\Diff\Differ as BaseDiffer;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Filesystem\Filesystem;
use Webmozart\Assert\Assert;
use Webmozart\PathUtil\Path;

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
    public const DEFAULT_FORMATTER = 'dot';
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

    /**
     * @var array<class-string<object>, true>
     */
    private $keys = [];

    /**
     * @var array<class-string<object>, object>
     */
    private $values = [];

    /**
     * @var array<class-string<object>, Closure(self): object>
     */
    private $factories = [];

    /**
     * @var string|null
     */
    private $defaultJUnitPath;

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
            Filesystem::class => static function (): Filesystem {
                return new Filesystem();
            },
            TmpDirProvider::class => static function (): TmpDirProvider {
                return new TmpDirProvider();
            },
            IndexXmlCoverageParser::class => static function (): IndexXmlCoverageParser {
                return new IndexXmlCoverageParser();
            },
            XmlCoverageParser::class => static function (): XmlCoverageParser {
                // TODO XmlCoverageParser might want to notify ProcessRunner if it can't parse another file due to lack of RAM
                return new XmlCoverageParser();
            },
            FilteredEnrichedTraceProvider::class => static function (self $container): FilteredEnrichedTraceProvider {
                return new FilteredEnrichedTraceProvider(
                    $container->getPhpUnitXmlCoverageTraceProvider(),
                    $container->getJUnitTestExecutionInfoAdder(),
                    $container->getSourceFileFilter(),
                    $container->getConfiguration()->getSourceFiles(),
                    $container->getConfiguration()->mutateOnlyCoveredCode()
                );
            },
            SourceFileFilter::class => static function (self $container): SourceFileFilter {
                return new SourceFileFilter(
                    $container->getConfiguration()->getSourceFilesFilter(),
                    $container->getConfiguration()->getSourceFilesExcludes()
                );
            },
            JUnitTestExecutionInfoAdder::class => static function (self $container): JUnitTestExecutionInfoAdder {
                return new JUnitTestExecutionInfoAdder(
                    $container->getTestFrameworkAdapter(),
                    $container->getMemoizedTestFileDataProvider()
                );
            },
            PhpUnitXmlCoverageTraceProvider::class => static function (self $container): PhpUnitXmlCoverageTraceProvider {
                return new PhpUnitXmlCoverageTraceProvider(
                    $container->getIndexXmlCoverageLocator(),
                    $container->getIndexXmlCoverageParser(),
                    $container->getXmlCoverageParser()
                );
            },
            IndexXmlCoverageLocator::class => static function (self $container): IndexXmlCoverageLocator {
                return new IndexXmlCoverageLocator(
                    $container->getConfiguration()->getCoveragePath()
                );
            },
            RootsFileOrDirectoryLocator::class => static function (self $container): RootsFileOrDirectoryLocator {
                return new RootsFileOrDirectoryLocator(
                    [$container->getProjectDir()],
                    $container->getFileSystem()
                );
            },
            Factory::class => static function (self $container): Factory {
                $config = $container->getConfiguration();

                return new Factory(
                    $config->getTmpDir(),
                    $container->getProjectDir(),
                    $container->getTestFrameworkConfigLocator(),
                    $container->getTestFrameworkFinder(),
                    $container->getDefaultJUnitFilePath(),
                    $config,
                    GeneratedExtensionsConfig::EXTENSIONS
                );
            },
            MutantCodeFactory::class => static function (self $container): MutantCodeFactory {
                return new MutantCodeFactory($container->getPrinter());
            },
            MutantFactory::class => static function (self $container): MutantFactory {
                return new MutantFactory(
                    $container->getConfiguration()->getTmpDir(),
                    $container->getDiffer(),
                    $container->getPrinter(),
                    $container->getMutantCodeFactory()
                );
            },
            Differ::class => static function (): Differ {
                return new Differ(new BaseDiffer());
            },
            SyncEventDispatcher::class => static function (): SyncEventDispatcher {
                return new SyncEventDispatcher();
            },
            ParallelProcessRunner::class => static function (self $container): ParallelProcessRunner {
                return new ParallelProcessRunner($container->getConfiguration()->getThreadCount());
            },
            DryProcessRunner::class => static function (): DryProcessRunner {
                return new DryProcessRunner();
            },
            TestFrameworkConfigLocator::class => static function (self $container): TestFrameworkConfigLocator {
                return new TestFrameworkConfigLocator(
                    (string) $container->getConfiguration()->getPhpUnit()->getConfigDir()
                );
            },
            DiffColorizer::class => static function (): DiffColorizer {
                return new DiffColorizer();
            },
            MemoizedTestFileDataProvider::class => static function (self $container): TestFileDataProvider {
                return new MemoizedTestFileDataProvider(
                    new JUnitTestFileDataProvider($container->getJUnitReportLocator())
                );
            },
            Lexer::class => static function (): Lexer {
                $attributes = MutationAttributeKeys::ALL;
                $attributes[] = 'comments';

                return new Lexer\Emulative(['usedAttributes' => $attributes]);
            },
            Parser::class => static function (self $container): Parser {
                $lexer = $container->getLexer();

                return (new ParserFactory())->create(ParserFactory::PREFER_PHP7, $lexer);
            },
            FileParser::class => static function (self $container): FileParser {
                return new FileParser($container->getParser());
            },
            PrettyPrinterAbstract::class => static function (): Standard {
                return new Standard();
            },
            MetricsCalculator::class => static function (self $container): MetricsCalculator {
                return new MetricsCalculator($container->getConfiguration()->getMsiPrecision());
            },
            Stopwatch::class => static function (): Stopwatch {
                return new Stopwatch();
            },
            TimeFormatter::class => static function (): TimeFormatter {
                return new TimeFormatter();
            },
            MemoryFormatter::class => static function (): MemoryFormatter {
                return new MemoryFormatter();
            },
            MemoryLimiter::class => static function (self $container): MemoryLimiter {
                return new MemoryLimiter(
                    $container->getFileSystem(),
                    (string) php_ini_loaded_file(),
                    new MemoryLimiterEnvironment()
                );
            },
            SchemaConfigurationLoader::class => static function (self $container): SchemaConfigurationLoader {
                return new SchemaConfigurationLoader(
                    $container->getRootsFileLocator(),
                    $container->getSchemaConfigurationFileLoader()
                );
            },
            RootsFileLocator::class => static function (self $container): RootsFileLocator {
                return new RootsFileLocator(
                    [$container->getProjectDir()],
                    $container->getFileSystem()
                );
            },
            SchemaConfigurationFileLoader::class => static function (self $container): SchemaConfigurationFileLoader {
                return new SchemaConfigurationFileLoader(
                    $container->getSchemaValidator(),
                    $container->getSchemaConfigurationFactory()
                );
            },
            SchemaValidator::class => static function (): SchemaValidator {
                return new SchemaValidator();
            },
            SchemaConfigurationFactory::class => static function (): SchemaConfigurationFactory {
                return new SchemaConfigurationFactory();
            },
            ConfigurationFactory::class => static function (self $container): ConfigurationFactory {
                return new ConfigurationFactory(
                    $container->getTmpDirProvider(),
                    $container->getMutatorResolver(),
                    $container->getMutatorFactory(),
                    $container->getMutatorParser(),
                    $container->getSourceFileCollector(),
                    $container->getCiDetector()
                );
            },
            MutatorResolver::class => static function (): MutatorResolver {
                return new MutatorResolver();
            },
            MutatorFactory::class => static function (): MutatorFactory {
                return new MutatorFactory();
            },
            MutatorParser::class => static function (): MutatorParser {
                return new MutatorParser();
            },
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
                    $container->getIndexXmlCoverageLocator()
                );
            },
            JUnitReportLocator::class => static function (self $container): JUnitReportLocator {
                return new JUnitReportLocator(
                    $container->getConfiguration()->getCoveragePath(),
                    $container->getDefaultJUnitFilePath()
                );
            },
            MinMsiChecker::class => static function (self $container): MinMsiChecker {
                $config = $container->getConfiguration();

                return new MinMsiChecker(
                    $config->ignoreMsiWithNoMutations(),
                    (float) $config->getMinMsi(),
                    (float) $config->getMinCoveredMsi()
                );
            },
            SubscriberRegisterer::class => static function (self $container): SubscriberRegisterer {
                return new SubscriberRegisterer(
                    $container->getEventDispatcher(),
                    $container->getSubscriberFactoryRegistry()
                );
            },
            ChainSubscriberFactory::class => static function (self $container): ChainSubscriberFactory {
                return new ChainSubscriberFactory(
                    $container->getInitialTestsConsoleLoggerSubscriberFactory(),
                    $container->getMutationGeneratingConsoleLoggerSubscriberFactory(),
                    $container->getMutationTestingConsoleLoggerSubscriberFactory(),
                    $container->getMutationTestingResultsLoggerSubscriberFactory(),
                    $container->getPerformanceLoggerSubscriberFactory(),
                    $container->getCleanUpAfterMutationTestingFinishedSubscriberFactory()
                );
            },
            CleanUpAfterMutationTestingFinishedSubscriberFactory::class => static function (self $container): CleanUpAfterMutationTestingFinishedSubscriberFactory {
                $config = $container->getConfiguration();

                return new CleanUpAfterMutationTestingFinishedSubscriberFactory(
                    $config->isDebugEnabled(),
                    $container->getFileSystem(),
                    $config->getTmpDir()
                );
            },
            InitialTestsConsoleLoggerSubscriberFactory::class => static function (self $container): InitialTestsConsoleLoggerSubscriberFactory {
                $config = $container->getConfiguration();

                return new InitialTestsConsoleLoggerSubscriberFactory(
                    $config->noProgress(),
                    $container->getTestFrameworkAdapter(),
                    $config->isDebugEnabled()
                );
            },
            MutationGeneratingConsoleLoggerSubscriberFactory::class => static function (self $container): MutationGeneratingConsoleLoggerSubscriberFactory {
                return new MutationGeneratingConsoleLoggerSubscriberFactory(
                    $container->getConfiguration()->noProgress()
                );
            },
            MutationTestingConsoleLoggerSubscriberFactory::class => static function (self $container): MutationTestingConsoleLoggerSubscriberFactory {
                $config = $container->getConfiguration();

                return new MutationTestingConsoleLoggerSubscriberFactory(
                    $container->getMetricsCalculator(),
                    $container->getDiffColorizer(),
                    $config->showMutations(),
                    $config->getFormatter()
                );
            },
            MutationTestingResultsLoggerSubscriberFactory::class => static function (self $container): MutationTestingResultsLoggerSubscriberFactory {
                return new MutationTestingResultsLoggerSubscriberFactory(
                    $container->getLoggerFactory(),
                    $container->getConfiguration()->getLogs()
                );
            },
            PerformanceLoggerSubscriberFactory::class => static function (self $container): PerformanceLoggerSubscriberFactory {
                return new PerformanceLoggerSubscriberFactory(
                    $container->getStopwatch(),
                    $container->getTimeFormatter(),
                    $container->getMemoryFormatter()
                );
            },
            CommandLineBuilder::class => static function (): CommandLineBuilder {
                return new CommandLineBuilder();
            },
            SourceFileCollector::class => static function (): SourceFileCollector {
                return new SourceFileCollector();
            },
            NodeTraverserFactory::class => static function (): NodeTraverserFactory {
                return new NodeTraverserFactory();
            },
            FileMutationGenerator::class => static function (self $container): FileMutationGenerator {
                return new FileMutationGenerator(
                    $container->getFileParser(),
                    $container->getNodeTraverserFactory(),
                    $container->getLineRangeCalculator()
                );
            },
            LoggerFactory::class => static function (self $container): LoggerFactory {
                $config = $container->getConfiguration();

                return new LoggerFactory(
                    $container->getMetricsCalculator(),
                    $container->getFileSystem(),
                    $config->getLogVerbosity(),
                    $config->isDebugEnabled(),
                    $config->mutateOnlyCoveredCode(),
                    $container->getCiDetector()
                );
            },
            TestFrameworkAdapter::class => static function (self $container): TestFrameworkAdapter {
                $config = $container->getConfiguration();

                return $container->getFactory()->create(
                    $config->getTestFramework(),
                    $config->shouldSkipCoverage()
                );
            },
            InitialTestsRunProcessFactory::class => static function (self $container): InitialTestsRunProcessFactory {
                return new InitialTestsRunProcessFactory(
                    $container->getTestFrameworkAdapter()
                );
            },
            InitialTestsRunner::class => static function (self $container): InitialTestsRunner {
                return new InitialTestsRunner(
                    $container->getInitialTestRunProcessFactory(),
                    $container->getEventDispatcher()
                );
            },
            MutantProcessFactory::class => static function (self $container): MutantProcessFactory {
                return new MutantProcessFactory(
                    $container->getTestFrameworkAdapter(),
                    $container->getConfiguration()->getProcessTimeout(),
                    $container->getEventDispatcher(),
                    $container->getMutantExecutionResultFactory()
                );
            },
            MutationGenerator::class => static function (self $container): MutationGenerator {
                $config = $container->getConfiguration();

                return new MutationGenerator(
                    $container->getFilteredEnrichedTraceProvider(),
                    $config->getMutators(),
                    $container->getEventDispatcher(),
                    $container->getFileMutationGenerator(),
                    $config->noProgress()
                );
            },
            MutationTestingRunner::class => static function (self $container): MutationTestingRunner {
                return new MutationTestingRunner(
                    $container->getMutantProcessFactory(),
                    $container->getMutantFactory(),
                    $container->getProcessRunner(),
                    $container->getEventDispatcher(),
                    $container->getConfiguration()->isDryRun()
                        ? new DummyFileSystem()
                        : $container->getFileSystem(),
                    $container->getConfiguration()->noProgress()
                );
            },
            LineRangeCalculator::class => static function (): LineRangeCalculator {
                return new LineRangeCalculator();
            },
            TestFrameworkFinder::class => static function (): TestFrameworkFinder {
                return new TestFrameworkFinder();
            },
            TestFrameworkExtraOptionsFilter::class => static function (): TestFrameworkExtraOptionsFilter {
                return new TestFrameworkExtraOptionsFilter();
            },
            AdapterInstallationDecider::class => static function (): AdapterInstallationDecider {
                return new AdapterInstallationDecider(new QuestionHelper());
            },
            AdapterInstaller::class => static function (): AdapterInstaller {
                return new AdapterInstaller(new ComposerExecutableFinder());
            },
            MutantExecutionResultFactory::class => static function (self $container): MutantExecutionResultFactory {
                return new MutantExecutionResultFactory($container->getTestFrameworkAdapter());
            },
            CiDetector::class => static function (): CiDetector {
                return new CiDetector();
            },
        ]);

        return $container->withValues(
            self::DEFAULT_CONFIG_FILE,
            self::DEFAULT_MUTATORS_INPUT,
            self::DEFAULT_SHOW_MUTATIONS,
            self::DEFAULT_LOG_VERBOSITY,
            self::DEFAULT_DEBUG,
            self::DEFAULT_ONLY_COVERED,
            self::DEFAULT_FORMATTER,
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
            self::DEFAULT_DRY_RUN
        );
    }

    public function withValues(
        ?string $configFile,
        string $mutatorsInput,
        bool $showMutations,
        string $logVerbosity,
        bool $debug,
        bool $onlyCovered,
        string $formatter,
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
        bool $dryRun
    ): self {
        $clone = clone $this;

        if ($forceProgress) {
            Assert::false($noProgress, 'Cannot force progress and set no progress at the same time');
        }

        $clone->offsetSet(
            CiDetector::class,
            static function () use ($forceProgress): CiDetector {
                return $forceProgress ? new NullCiDetector() : new MemoizedCiDetector();
            }
        );

        $clone->offsetSet(
            SchemaConfiguration::class,
            static function (self $container) use ($configFile): SchemaConfiguration {
                return $container->getSchemaConfigurationLoader()->loadConfiguration(
                    array_filter(
                        [
                            $configFile,
                            SchemaConfigurationLoader::DEFAULT_CONFIG_FILE,
                            SchemaConfigurationLoader::DEFAULT_DIST_CONFIG_FILE,
                        ]
                    )
                );
            }
        );

        $clone->offsetSet(
            Configuration::class,
            static function (self $container) use (
                $existingCoveragePath,
                $initialTestsPhpOptions,
                $skipInitialTests,
                $logVerbosity,
                $debug,
                $onlyCovered,
                $formatter,
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
                $dryRun
            ): Configuration {
                return $container->getConfigurationFactory()->create(
                    $container->getSchemaConfiguration(),
                    $existingCoveragePath,
                    $initialTestsPhpOptions,
                    $skipInitialTests,
                    $logVerbosity,
                    $debug,
                    $onlyCovered,
                    $formatter,
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
                    $dryRun
                );
            }
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
        return $this->defaultJUnitPath ?? $this->defaultJUnitPath = sprintf(
            '%s/%s',
            Path::canonicalize(
                $this->getConfiguration()->getCoveragePath()
            ),
            'junit.xml'
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

    public function getFilteredEnrichedTraceProvider(): FilteredEnrichedTraceProvider
    {
        return $this->get(FilteredEnrichedTraceProvider::class);
    }

    public function getSourceFileFilter(): SourceFileFilter
    {
        return $this->get(SourceFileFilter::class);
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

    public function getLexer(): Lexer
    {
        return $this->get(Lexer::class);
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

    public function getInitialTestsConsoleLoggerSubscriberFactory(): InitialTestsConsoleLoggerSubscriberFactory
    {
        return $this->get(InitialTestsConsoleLoggerSubscriberFactory::class);
    }

    public function getMutationGeneratingConsoleLoggerSubscriberFactory(): MutationGeneratingConsoleLoggerSubscriberFactory
    {
        return $this->get(MutationGeneratingConsoleLoggerSubscriberFactory::class);
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

    public function getLoggerFactory(): LoggerFactory
    {
        return $this->get(LoggerFactory::class);
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
