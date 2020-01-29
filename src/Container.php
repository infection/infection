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
use Infection\FileSystem\Locator\RootsFileLocator;
use Infection\FileSystem\Locator\RootsFileOrDirectoryLocator;
use Infection\FileSystem\SourceFileCollector;
use Infection\FileSystem\TmpDirProvider;
use Infection\Logger\LoggerFactory;
use Infection\Mutant\MetricsCalculator;
use Infection\Mutant\MutantCodeFactory;
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
use Infection\Process\Coverage\CoverageRequirementChecker;
use Infection\Process\Runner\InitialTestsRunner;
use Infection\Process\Runner\MutationTestingRunner;
use Infection\Process\Runner\Parallel\ParallelProcessRunner;
use Infection\Process\Runner\TestRunConstraintChecker;
use Infection\Resource\Memory\MemoryFormatter;
use Infection\Resource\Memory\MemoryLimiter;
use Infection\Resource\Time\Stopwatch;
use Infection\Resource\Time\TimeFormatter;
use Infection\TestFramework\CommandLineBuilder;
use Infection\TestFramework\Config\TestFrameworkConfigLocator;
use Infection\TestFramework\Coverage\XmlReport\JUnitTestFileDataProvider;
use Infection\TestFramework\Coverage\XmlReport\MemoizedTestFileDataProvider;
use Infection\TestFramework\Coverage\XmlReport\TestFileDataProvider;
use Infection\TestFramework\Coverage\XmlReport\XMLLineCodeCoverageFactory;
use Infection\TestFramework\Factory;
use Infection\TestFramework\PhpUnit\Config\Path\PathReplacer;
use Infection\TestFramework\PhpUnit\Config\XmlConfigurationHelper;
use Infection\TestFramework\PhpUnit\Coverage\IndexXmlCoverageParser;
use InvalidArgumentException;
use function is_callable;
use function php_ini_loaded_file;
use PhpParser\Lexer;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use PhpParser\PrettyPrinterAbstract;
use function Safe\getcwd;
use function Safe\sprintf;
use SebastianBergmann\Diff\Differ as BaseDiffer;
use Symfony\Component\Filesystem\Filesystem;
use Webmozart\PathUtil\Path;

/**
 * @internal
 */
final class Container
{
    private $keys = [];
    private $values = [];
    private $factories = [];

    /**
     * @param array<string, Closure|string|int|float|bool|object> $values
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
            'project.dir' => getcwd(),
            Filesystem::class => static function (): Filesystem {
                return new Filesystem();
            },
            TmpDirProvider::class => static function (): TmpDirProvider {
                return new TmpDirProvider();
            },
            'junit.file.path' => static function (self $container) {
                return sprintf(
                    '%s/%s',
                    Path::canonicalize(
                        $container->getConfiguration()->getCoveragePath() . '/..'
                    ),
                    TestFrameworkAdapter::JUNIT_FILE_NAME
                );
            },
            IndexXmlCoverageParser::class => static function (self $container): IndexXmlCoverageParser {
                return new IndexXmlCoverageParser($container->getConfiguration()->getCoveragePath());
            },
            XMLLineCodeCoverageFactory::class => static function (self $container): XMLLineCodeCoverageFactory {
                return new XMLLineCodeCoverageFactory(
                    $container->getConfiguration()->getCoveragePath(),
                    $container->getIndexXmlCoverageParser(),
                    $container->getMemoizedTestFileDataProvider()
                );
            },
            RootsFileOrDirectoryLocator::class => static function (self $container): RootsFileOrDirectoryLocator {
                return new RootsFileOrDirectoryLocator(
                    [$container->getProjectDir()],
                    $container->getFileSystem()
                );
            },
            PathReplacer::class => static function (self $container): PathReplacer {
                return new PathReplacer(
                    $container->getFileSystem(),
                    $container->getConfiguration()->getPhpUnit()->getConfigDir()
                );
            },
            Factory::class => static function (self $container): Factory {
                $config = $container->getConfiguration();

                return new Factory(
                    $config->getTmpDir(),
                    $container->getProjectDir(),
                    $container->getTestFrameworkConfigLocator(),
                    $container->getJUnitFilePath(),
                    $config
                );
            },
            XmlConfigurationHelper::class => static function (self $container): XmlConfigurationHelper {
                return new XmlConfigurationHelper(
                    $container->getPathReplacer(),
                    (string) $container->getConfiguration()->getPhpUnit()->getConfigDir()
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
                return new ParallelProcessRunner($container->getEventDispatcher());
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
            CoverageRequirementChecker::class => static function (self $container): CoverageRequirementChecker {
                $config = $container->getConfiguration();

                return new CoverageRequirementChecker(
                    $config->getCoveragePath() !== '',
                    $config->getInitialTestsPhpOptions() ?? ''
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
                    $config->showProgress(),
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
                    $container->getNodeTraverserFactory()
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
                    $config->getSourceFiles(),
                    $container->getXMLLineCodeCoverageFactory()->create(
                        $config->getTestFramework(),
                        $container->getTestFrameworkAdapter()
                    ),
                    $config->getMutators(),
                    $container->getEventDispatcher(),
                    $container->getFileMutationGenerator()
                );
            },
            MutationTestingRunner::class => static function (self $container): MutationTestingRunner {
                return new MutationTestingRunner(
                    $container->getMutantProcessBuilder(),
                    $container->getMutantFactory(),
                    $container->getParallelProcessRunner(),
                    $container->getEventDispatcher()
                );
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
        bool $ignoreMsiWithNoMutations,
        ?float $minMsi,
        ?float $minCoveredMsi,
        ?string $testFramework,
        ?string $testFrameworkExtraOptions,
        string $filter
    ): self {
        $clone = clone $this;

        $clone->offsetAdd(
            SchemaConfiguration::class,
            static function (self $container) use ($configFile): SchemaConfiguration {
                return $container->getSchemaConfigurationLoader()->loadConfiguration(
                    array_filter(
                        [
                            $configFile,
                            SchemaConfigurationLoader::DEFAULT_DIST_CONFIG_FILE,
                            SchemaConfigurationLoader::DEFAULT_CONFIG_FILE,
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
                $filter
            ): Configuration {
                return $container->getConfigurationFactory()->create(
                    $container->getSchemaConfiguration(),
                    $existingCoveragePath,
                    $initialTestsPhpOptions,
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
                    $filter
                );
            }
        );

        return $clone;
    }

    public function getProjectDir(): string
    {
        return $this->get('project.dir');
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
        return $this->get('junit.file.path');
    }

    public function getIndexXmlCoverageParser(): IndexXmlCoverageParser
    {
        return $this->get(IndexXmlCoverageParser::class);
    }

    public function getXMLLineCodeCoverageFactory(): XMLLineCodeCoverageFactory
    {
        return $this->get(XMLLineCodeCoverageFactory::class);
    }

    public function getRootsFileOrDirectoryLocator(): RootsFileOrDirectoryLocator
    {
        return $this->get(RootsFileOrDirectoryLocator::class);
    }

    public function getPathReplacer(): PathReplacer
    {
        return $this->get(PathReplacer::class);
    }

    public function getFactory(): Factory
    {
        return $this->get(Factory::class);
    }

    public function getXmlConfigurationHelper(): XmlConfigurationHelper
    {
        return $this->get(XmlConfigurationHelper::class);
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

    public function getParallelProcessRunner(): ParallelProcessRunner
    {
        return $this->get(ParallelProcessRunner::class);
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

    public function getCoverageRequirementChecker(): CoverageRequirementChecker
    {
        return $this->get(CoverageRequirementChecker::class);
    }

    public function getTestRunConstraintChecker(): TestRunConstraintChecker
    {
        return $this->get(TestRunConstraintChecker::class);
    }

    public function getSubscriberBuilder(): SubscriberBuilder
    {
        return $this->get(SubscriberBuilder::class);
    }

    public function getCommandLineBuilder(): CommandLineBuilder
    {
        return $this->get(CommandLineBuilder::class);
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

    /**
     * @param Closure|string|int|float|bool|object $value
     */
    private function offsetAdd(string $id, $value): void
    {
        $this->keys[$id] = true;

        if (is_callable($value)) {
            $this->factories[$id] = $value;
        } else {
            $this->values[$id] = $value;
        }
    }

    private function get(string $id)
    {
        if (!isset($this->keys[$id])) {
            throw new InvalidArgumentException(sprintf('Unknown service "%s"', $id));
        }

        if (array_key_exists($id, $this->values)) {
            return $this->values[$id];
        }

        return $this->values[$id] = $this->factories[$id]($this);
    }
}
