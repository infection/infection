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
use Infection\Configuration\Configuration;
use Infection\Configuration\ConfigurationFactory;
use Infection\Configuration\Schema\SchemaConfiguration;
use Infection\Configuration\Schema\SchemaConfigurationFactory;
use Infection\Configuration\Schema\SchemaConfigurationFileLoader;
use Infection\Configuration\Schema\SchemaConfigurationLoader;
use Infection\Configuration\Schema\SchemaValidator;
use Infection\Differ\DiffColorizer;
use Infection\Differ\Differ;
use Infection\Event\EventDispatcher\EventDispatcher;
use Infection\Event\EventDispatcher\SyncEventDispatcher;
use Infection\Event\MutantProcessWasFinished;
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
use Infection\Mutant\MetricsCalculator;
use Infection\Mutant\MutantCodeFactory;
use Infection\Mutant\MutantExecutionResult;
use Infection\Mutant\MutantFactory;
use Infection\Mutation\FileMutationGenerator;
use Infection\Mutation\Mutation;
use Infection\Mutation\MutationGenerator;
use Infection\Mutator\MutatorFactory;
use Infection\Mutator\MutatorParser;
use Infection\Mutator\MutatorResolver;
use Infection\PhpParser\FileParser;
use Infection\PhpParser\NodeTraverserFactory;
use Infection\Process\Builder\InitialTestRunProcessBuilder;
use Infection\Process\Builder\MutantProcessBuilder;
use Infection\Process\Builder\SubscriberBuilder;
use Infection\Process\MutantProcess;
use Infection\Process\Runner\DryProcessRunner;
use Infection\Process\Runner\InitialTestsRunner;
use Infection\Process\Runner\MutationTestingRunner;
use Infection\Process\Runner\ParallelProcessRunner;
use Infection\Process\Runner\ProcessRunner;
use Infection\Process\Runner\TestRunConstraintChecker;
use Infection\Resource\Memory\MemoryFormatter;
use Infection\Resource\Memory\MemoryLimiter;
use Infection\Resource\Time\Stopwatch;
use Infection\Resource\Time\TimeFormatter;
use Infection\TestFramework\AdapterInstallationDecider;
use Infection\TestFramework\AdapterInstaller;
use Infection\TestFramework\CommandLineBuilder;
use Infection\TestFramework\Config\TestFrameworkConfigLocator;
use Infection\TestFramework\Coverage\BufferedSourceFileFilter;
use Infection\TestFramework\Coverage\CoverageChecker;
use Infection\TestFramework\Coverage\CoveredTraceProvider;
use Infection\TestFramework\Coverage\JUnit\JUnitTestExecutionInfoAdder;
use Infection\TestFramework\Coverage\JUnit\JUnitTestFileDataProvider;
use Infection\TestFramework\Coverage\JUnit\MemoizedTestFileDataProvider;
use Infection\TestFramework\Coverage\JUnit\TestFileDataProvider;
use Infection\TestFramework\Coverage\LineRangeCalculator;
use Infection\TestFramework\Coverage\UncoveredTraceProvider;
use Infection\TestFramework\Coverage\UnionTraceProvider;
use Infection\TestFramework\Coverage\XmlReport\IndexXmlCoverageParser;
use Infection\TestFramework\Coverage\XmlReport\IndexXmlCoverageReader;
use Infection\TestFramework\Coverage\XmlReport\PhpUnitXmlCoverageTraceProvider;
use Infection\TestFramework\Coverage\XmlReport\XmlCoverageParser;
use Infection\TestFramework\Factory;
use Infection\TestFramework\TestFrameworkExtraOptionsFilter;
use InvalidArgumentException;
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
     * @param array<class-string<object>, Closure(self): object> $values
     */
    public function __construct(array $values)
    {
        foreach ($values as $id => $value) {
            $this->offsetAdd($id, $value);
        }
    }

    public static function create(): self
    {
        return new self([
            Filesystem::class => static function (): Filesystem {
                return new Filesystem();
            },
            TmpDirProvider::class => static function (): TmpDirProvider {
                return new TmpDirProvider();
            },
            IndexXmlCoverageParser::class => static function (self $container): IndexXmlCoverageParser {
                return new IndexXmlCoverageParser(
                    $container->getConfiguration()->getCoveragePath(),
                );
            },
            XmlCoverageParser::class => static function (): XmlCoverageParser {
                // TODO XmlCoverageParser might want to notify ProcessRunner if it can't parse another file due to lack of RAM
                return new XmlCoverageParser();
            },
            CoveredTraceProvider::class => static function (self $container): CoveredTraceProvider {
                return new CoveredTraceProvider(
                    $container->getPhpUnitXmlCoverageTraceProvider(),
                    $container->getJUnitTestExecutionInfoAdder(),
                    $container->getBufferedSourceFileFilter(),
                );
            },
            UnionTraceProvider::class => static function (self $container): UnionTraceProvider {
                return new UnionTraceProvider(
                    $container->getCoveredTraceProvider(),
                    $container->getUncoveredTraceProvider(),
                    $container->getConfiguration()->mutateOnlyCoveredCode()
                );
            },
            BufferedSourceFileFilter::class => static function (self $container): BufferedSourceFileFilter {
                return new BufferedSourceFileFilter(
                    $container->getSourceFileFilter(),
                    $container->getConfiguration()->getSourceFiles(),
                );
            },
            UncoveredTraceProvider::class => static function (self $container): UncoveredTraceProvider {
                return new UncoveredTraceProvider(
                    $container->getBufferedSourceFileFilter()
                );
            },
            SourceFileFilter::class => static function (self $container): SourceFileFilter {
                return new SourceFileFilter(
                    $container->getConfiguration()->getSourceFilesFilter(),
                    $container->getSchemaConfiguration()->getSource()->getExcludes()
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
                    $container->getIndexXmlCoverageReader(),
                    $container->getIndexXmlCoverageParser(),
                    $container->getXmlCoverageParser()
                );
            },
            IndexXmlCoverageReader::class => static function (self $container): IndexXmlCoverageReader {
                return new IndexXmlCoverageReader(
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
                    $container->getJUnitFilePath(),
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
                return new ParallelProcessRunner(
                    static function (MutantProcess $mutantProcess) use ($container): void {
                        $container->getEventDispatcher()->dispatch(new MutantProcessWasFinished(
                            MutantExecutionResult::createFromProcess($mutantProcess)
                        ));
                    },
                    $container->getConfiguration()->getThreadCount()
                );
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
                    new JUnitTestFileDataProvider($container->getJUnitFilePath())
                );
            },
            Lexer::class => static function (): Lexer {
                $attributes = Mutation::ATTRIBUTE_KEYS;
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
            MetricsCalculator::class => static function (): MetricsCalculator {
                return new MetricsCalculator();
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
                return new MemoryLimiter($container->getFileSystem(), php_ini_loaded_file());
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
                    $container->getSourceFileCollector()
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
                    $testFrameworkAdapter->hasJUnitReport()
                        ? $container->getJUnitFilePath()
                        : null,
                    $testFrameworkAdapter->getName(),
                    $container->getIndexXmlCoverageReader()
                );
            },
            TestRunConstraintChecker::class => static function (self $container): TestRunConstraintChecker {
                $config = $container->getConfiguration();

                return new TestRunConstraintChecker(
                    $container->getMetricsCalculator(),
                    $config->ignoreMsiWithNoMutations(),
                    (float) $config->getMinMsi(),
                    (float) $config->getMinCoveredMsi()
                );
            },
            SubscriberBuilder::class => static function (self $container): SubscriberBuilder {
                $config = $container->getConfiguration();

                return new SubscriberBuilder(
                    $config->showMutations(),
                    $config->isDebugEnabled(),
                    $config->getFormatter(),
                    $config->noProgress(),
                    $container->getMetricsCalculator(),
                    $container->getEventDispatcher(),
                    $container->getDiffColorizer(),
                    $config,
                    $container->getFileSystem(),
                    $config->getTmpDir(),
                    $container->getStopwatch(),
                    $container->getTimeFormatter(),
                    $container->getMemoryFormatter(),
                    $container->getLoggerFactory()
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
                    $config->mutateOnlyCoveredCode()
                );
            },
            TestFrameworkAdapter::class => static function (self $container): TestFrameworkAdapter {
                $config = $container->getConfiguration();

                return $container->getFactory()->create(
                    $config->getTestFramework(),
                    $config->shouldSkipCoverage()
                );
            },
            InitialTestRunProcessBuilder::class => static function (self $container): InitialTestRunProcessBuilder {
                return new InitialTestRunProcessBuilder(
                    $container->getTestFrameworkAdapter()
                );
            },
            InitialTestsRunner::class => static function (self $container): InitialTestsRunner {
                return new InitialTestsRunner(
                    $container->getInitialTestRunProcessBuilder(),
                    $container->getEventDispatcher()
                );
            },
            MutantProcessBuilder::class => static function (self $container): MutantProcessBuilder {
                return new MutantProcessBuilder(
                    $container->getTestFrameworkAdapter(),
                    $container->getConfiguration()->getProcessTimeout()
                );
            },
            MutationGenerator::class => static function (self $container): MutationGenerator {
                $config = $container->getConfiguration();

                return new MutationGenerator(
                    $container->getUnionTraceProvider(),
                    $config->getMutators(),
                    $container->getEventDispatcher(),
                    $container->getFileMutationGenerator(),
                    $container->getConfiguration()->noProgress()
                );
            },
            MutationTestingRunner::class => static function (self $container): MutationTestingRunner {
                return new MutationTestingRunner(
                    $container->getMutantProcessBuilder(),
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
            AdapterInstallationDecider::class => static function (self $container): AdapterInstallationDecider {
                return new AdapterInstallationDecider(new QuestionHelper());
            },
            AdapterInstaller::class => static function (): AdapterInstaller {
                return new AdapterInstaller(new ComposerExecutableFinder());
            },
        ]);
    }

    public function withDynamicParameters(
        ?string $configFile,
        string $mutatorsInput,
        bool $showMutations,
        string $logVerbosity,
        bool $debug,
        bool $onlyCovered,
        string $formatter,
        bool $noProgress,
        ?string $existingCoveragePath,
        ?string $initialTestsPhpOptions,
        bool $skipInitialTests,
        bool $ignoreMsiWithNoMutations,
        ?float $minMsi,
        ?float $minCoveredMsi,
        ?string $testFramework,
        ?string $testFrameworkExtraOptions,
        string $filter,
        int $threadCount,
        bool $dryRun
    ): self {
        $clone = clone $this;

        $clone->offsetAdd(
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

        $clone->offsetAdd(
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

    public function getJUnitFilePath(): string
    {
        return sprintf(
            '%s/%s',
            Path::canonicalize(
                $this->getConfiguration()->getCoveragePath() . '/..'
            ),
            'junit.xml'
        );
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

    public function getIndexXmlCoverageReader(): IndexXmlCoverageReader
    {
        return $this->get(IndexXmlCoverageReader::class);
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

    public function getTestRunConstraintChecker(): TestRunConstraintChecker
    {
        return $this->get(TestRunConstraintChecker::class);
    }

    public function getSubscriberBuilder(): SubscriberBuilder
    {
        return $this->get(SubscriberBuilder::class);
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

    public function getInitialTestRunProcessBuilder(): InitialTestRunProcessBuilder
    {
        return $this->get(InitialTestRunProcessBuilder::class);
    }

    public function getInitialTestsRunner(): InitialTestsRunner
    {
        return $this->get(InitialTestsRunner::class);
    }

    public function getMutantProcessBuilder(): MutantProcessBuilder
    {
        return $this->get(MutantProcessBuilder::class);
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

    /**
     * @param class-string<object> $id
     * @param Closure(self): object $value
     */
    private function offsetAdd(string $id, Closure $value): void
    {
        $this->keys[$id] = true;

        $this->factories[$id] = $value;
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
