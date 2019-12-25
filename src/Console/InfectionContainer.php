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

namespace Infection\Console;

use function array_filter;
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
use Infection\EventDispatcher\EventDispatcher;
use Infection\EventDispatcher\EventDispatcherInterface;
use Infection\FileSystem\SourceFileCollector;
use Infection\FileSystem\TmpDirProvider;
use Infection\Locator\RootsFileLocator;
use Infection\Locator\RootsFileOrDirectoryLocator;
use Infection\Logger\LoggerFactory;
use Infection\Mutant\MetricsCalculator;
use Infection\Mutant\MutantCreator;
use Infection\Mutation\FileMutationGenerator;
use Infection\Mutation\FileParser;
use Infection\Mutation\MutationGenerator;
use Infection\Mutation\NodeTraverserFactory;
use Infection\Mutator\MutatorFactory;
use Infection\Mutator\MutatorParser;
use Infection\Performance\Limiter\MemoryLimiter;
use Infection\Performance\Memory\MemoryFormatter;
use Infection\Performance\Time\TimeFormatter;
use Infection\Performance\Time\Timer;
use Infection\Process\Builder\InitialTestRunProcessBuilder;
use Infection\Process\Builder\MutantProcessBuilder;
use Infection\Process\Builder\SubscriberBuilder;
use Infection\Process\Coverage\CoverageRequirementChecker;
use Infection\Process\Runner\InitialTestsRunner;
use Infection\Process\Runner\MutationTestingRunner;
use Infection\Process\Runner\Parallel\ParallelProcessRunner;
use Infection\Process\Runner\TestRunConstraintChecker;
use Infection\TestFramework\CommandLineBuilder;
use Infection\TestFramework\Config\TestFrameworkConfigLocator;
use Infection\TestFramework\Coverage\CachedTestFileDataProvider;
use Infection\TestFramework\Coverage\JUnitTestFileDataProvider;
use Infection\TestFramework\Coverage\TestFileDataProvider;
use Infection\TestFramework\Coverage\XMLLineCodeCoverageFactory;
use Infection\TestFramework\Factory;
use Infection\TestFramework\PhpUnit\Config\Path\PathReplacer;
use Infection\TestFramework\PhpUnit\Config\XmlConfigurationHelper;
use Infection\TestFramework\PhpUnit\Coverage\CoverageXmlParser;
use Infection\Utils\VersionParser;
use function php_ini_loaded_file;
use PhpParser\Lexer;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use Pimple\Container;
use function Safe\getcwd;
use function Safe\sprintf;
use SebastianBergmann\Diff\Differ as BaseDiffer;
use Symfony\Component\Filesystem\Filesystem;
use Webmozart\PathUtil\Path;

/**
 * @internal
 */
final class InfectionContainer extends Container
{
    public static function create(): self
    {
        return new self([
            'project.dir' => getcwd(),
            'filesystem' => static function (): Filesystem {
                return new Filesystem();
            },
            TmpDirProvider::class => static function (): TmpDirProvider {
                return new TmpDirProvider();
            },
            'junit.file.path' => static function (self $container) {
                /** @var Configuration $config */
                $config = $container[Configuration::class];

                return sprintf(
                    '%s/%s',
                    Path::canonicalize($config->getCoveragePath() . '/..'),
                    TestFrameworkAdapter::JUNIT_FILE_NAME
                );
            },
            CoverageXmlParser::class => static function (self $container): CoverageXmlParser {
                /** @var Configuration $config */
                $config = $container[Configuration::class];

                return new CoverageXmlParser($config->getCoveragePath());
            },
            XMLLineCodeCoverageFactory::class => static function (self $container): XMLLineCodeCoverageFactory {
                /** @var Configuration $config */
                $config = $container[Configuration::class];

                /** @var CoverageXmlParser $coverageXmlParser */
                $coverageXmlParser = $container[CoverageXmlParser::class];

                /** @var CachedTestFileDataProvider $cachedTestFileDataProvider */
                $cachedTestFileDataProvider = $container[CachedTestFileDataProvider::class];

                return new XMLLineCodeCoverageFactory(
                    $config->getCoveragePath(),
                    $coverageXmlParser,
                    $cachedTestFileDataProvider
                );
            },
            RootsFileOrDirectoryLocator::class => static function (self $container): RootsFileOrDirectoryLocator {
                return new RootsFileOrDirectoryLocator(
                    [$container['project.dir']],
                    $container['filesystem']
                );
            },
            'path.replacer' => static function (self $container): PathReplacer {
                /** @var Configuration $config */
                $config = $container[Configuration::class];

                return new PathReplacer(
                    $container['filesystem'],
                    $config->getPhpUnit()->getConfigDir()
                );
            },
            'test.framework.factory' => static function (self $container): Factory {
                /** @var Configuration $config */
                $config = $container[Configuration::class];

                return new Factory(
                    $config->getTmpDir(),
                    $container['project.dir'],
                    $container['testframework.config.locator'],
                    $container['xml.configuration.helper'],
                    $container['junit.file.path'],
                    $container[Configuration::class],
                    $container[VersionParser::class],
                    $container['filesystem'],
                    $container[CommandLineBuilder::class]
                );
            },
            'xml.configuration.helper' => static function (self $container): XmlConfigurationHelper {
                /** @var Configuration $config */
                $config = $container[Configuration::class];

                return new XmlConfigurationHelper(
                    $container['path.replacer'],
                    (string) $config->getPhpUnit()->getConfigDir()
                );
            },
            'mutant.creator' => static function (self $container): MutantCreator {
                /** @var Configuration $config */
                $config = $container[Configuration::class];

                return new MutantCreator(
                    $config->getTmpDir(),
                    $container['differ'],
                    $container['pretty.printer']
                );
            },
            'differ' => static function (): Differ {
                return new Differ(
                    new BaseDiffer()
                );
            },
            'dispatcher' => static function (): EventDispatcherInterface {
                return new EventDispatcher();
            },
            'parallel.process.runner' => static function (self $container): ParallelProcessRunner {
                return new ParallelProcessRunner($container['dispatcher']);
            },
            'testframework.config.locator' => static function (self $container): TestFrameworkConfigLocator {
                /** @var Configuration $config */
                $config = $container[Configuration::class];

                return new TestFrameworkConfigLocator(
                    (string) $config->getPhpUnit()->getConfigDir()
                );
            },
            'diff.colorizer' => static function (): DiffColorizer {
                return new DiffColorizer();
            },
            CachedTestFileDataProvider::class => static function (self $container): TestFileDataProvider {
                return new CachedTestFileDataProvider(
                    new JUnitTestFileDataProvider($container['junit.file.path'])
                );
            },
            VersionParser::class => static function (): VersionParser {
                return new VersionParser();
            },
            Lexer::class => static function (): Lexer {
                return new Lexer\Emulative([
                    'usedAttributes' => [
                        'comments',
                        'startLine',
                        'endLine',
                        'startTokenPos',
                        'endTokenPos',
                        'startFilePos',
                        'endFilePos',
                    ],
                ]);
            },
            Parser::class => static function (self $container): Parser {
                /** @var Lexer $lexer */
                $lexer = $container[Lexer::class];

                return (new ParserFactory())->create(ParserFactory::PREFER_PHP7, $lexer);
            },
            FileParser::class => static function (self $container): FileParser {
                /** @var Parser $phpParser */
                $phpParser = $container[Parser::class];

                return new FileParser($phpParser);
            },
            'pretty.printer' => static function (): Standard {
                return new Standard();
            },
            'metrics' => static function (): MetricsCalculator {
                return new MetricsCalculator();
            },
            'timer' => static function (): Timer {
                return new Timer();
            },
            'time.formatter' => static function (): TimeFormatter {
                return new TimeFormatter();
            },
            'memory.formatter' => static function (): MemoryFormatter {
                return new MemoryFormatter();
            },
            'memory.limit.applier' => static function (self $container): MemoryLimiter {
                /** @var Filesystem $fileSystem */
                $fileSystem = $container['filesystem'];

                return new MemoryLimiter($fileSystem, php_ini_loaded_file());
            },
            SchemaConfigurationLoader::class => static function (self $container): SchemaConfigurationLoader {
                /** @var RootsFileLocator $rootsFileLocator */
                $rootsFileLocator = $container[RootsFileLocator::class];

                /** @var SchemaConfigurationFileLoader $schemaConfigFileLoader */
                $schemaConfigFileLoader = $container[SchemaConfigurationFileLoader::class];

                return new SchemaConfigurationLoader($rootsFileLocator, $schemaConfigFileLoader);
            },
            RootsFileLocator::class => static function (self $container): RootsFileLocator {
                /** @var string $projectDir */
                $projectDir = $container['project.dir'];

                /** @var Filesystem $fileSystem */
                $fileSystem = $container['filesystem'];

                return new RootsFileLocator([$projectDir], $fileSystem);
            },
            SchemaConfigurationFileLoader::class => static function (self $container): SchemaConfigurationFileLoader {
                /** @var SchemaValidator $schemaValidator */
                $schemaValidator = $container[SchemaValidator::class];

                /** @var SchemaConfigurationFactory $schemaConfigFactory */
                $schemaConfigFactory = $container[SchemaConfigurationFactory::class];

                return new SchemaConfigurationFileLoader($schemaValidator, $schemaConfigFactory);
            },
            SchemaValidator::class => static function (): SchemaValidator {
                return new SchemaValidator();
            },
            SchemaConfigurationFactory::class => static function (): SchemaConfigurationFactory {
                return new SchemaConfigurationFactory();
            },
            ConfigurationFactory::class => static function (self $container): ConfigurationFactory {
                /** @var TmpDirProvider $tmpDirProvider */
                $tmpDirProvider = $container[TmpDirProvider::class];

                /** @var MutatorFactory $mutatorFactory */
                $mutatorFactory = $container[MutatorFactory::class];

                /** @var MutatorParser $mutatorParser */
                $mutatorParser = $container[MutatorParser::class];

                /** @var SourceFileCollector $sourceFileCollector */
                $sourceFileCollector = $container[SourceFileCollector::class];

                return new ConfigurationFactory(
                    $tmpDirProvider,
                    $mutatorFactory,
                    $mutatorParser,
                    $sourceFileCollector
                );
            },
            MutatorFactory::class => static function (): MutatorFactory {
                return new MutatorFactory();
            },
            MutatorParser::class => static function (): MutatorParser {
                return new MutatorParser();
            },
            'coverage.checker' => static function (self $container): CoverageRequirementChecker {
                /** @var Configuration $config */
                $config = $container[Configuration::class];

                return new CoverageRequirementChecker(
                    $config->getCoveragePath() !== '',
                    $config->getInitialTestsPhpOptions() ?? ''
                );
            },
            'test.run.constraint.checker' => static function (self $container): TestRunConstraintChecker {
                /** @var Configuration $config */
                $config = $container[Configuration::class];

                /** @var MetricsCalculator $metricsCalculator */
                $metricsCalculator = $container['metrics'];

                return new TestRunConstraintChecker(
                    $metricsCalculator,
                    $config->ignoreMsiWithNoMutations(),
                    (float) $config->getMinMsi(),
                    (float) $config->getMinCoveredMsi()
                );
            },
            'subscriber.builder' => static function (self $container): SubscriberBuilder {
                /** @var Configuration $config */
                $config = $container[Configuration::class];

                /** @var LoggerFactory $loggerFactory */
                $loggerFactory = $container[LoggerFactory::class];

                /** @var MetricsCalculator $metricsCalculator */
                $metricsCalculator = $container['metrics'];

                /** @var EventDispatcherInterface $eventDispatcher */
                $eventDispatcher = $container['dispatcher'];

                /** @var DiffColorizer $diffColorizer */
                $diffColorizer = $container['diff.colorizer'];

                /** @var Filesystem $fileSystem */
                $fileSystem = $container['filesystem'];

                /** @var Timer $timer */
                $timer = $container['timer'];

                /** @var TimeFormatter $timeFormatter */
                $timeFormatter = $container['time.formatter'];

                /** @var MemoryFormatter $memoryFormatter */
                $memoryFormatter = $container['memory.formatter'];

                return new SubscriberBuilder(
                    $config->showMutations(),
                    $config->isDebugEnabled(),
                    $config->getFormatter(),
                    $config->showProgress(),
                    $metricsCalculator,
                    $eventDispatcher,
                    $diffColorizer,
                    $config,
                    $fileSystem,
                    $config->getTmpDir(),
                    $timer,
                    $timeFormatter,
                    $memoryFormatter,
                    $loggerFactory
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
                /** @var FileParser $fileParser */
                $fileParser = $container[FileParser::class];

                /** @var NodeTraverserFactory $nodeTraverserFactory */
                $nodeTraverserFactory = $container[NodeTraverserFactory::class];

                return new FileMutationGenerator($fileParser, $nodeTraverserFactory);
            },
            LoggerFactory::class => static function (self $container): LoggerFactory {
                /** @var Configuration $config */
                $config = $container[Configuration::class];

                /** @var MetricsCalculator $metricsCalculator */
                $metricsCalculator = $container['metrics'];

                /** @var Filesystem $fileSystem */
                $fileSystem = $container['filesystem'];

                return new LoggerFactory(
                    $metricsCalculator,
                    $fileSystem,
                    $config->getLogVerbosity(),
                    $config->isDebugEnabled(),
                    $config->mutateOnlyCoveredCode()
                );
            },
            TestFrameworkAdapter::class => static function (self $container): TestFrameworkAdapter {
                /** @var Configuration $config */
                $config = $container[Configuration::class];

                /** @var Factory $testFrameworkFactory */
                $testFrameworkFactory = $container['test.framework.factory'];

                return $testFrameworkFactory->create(
                    $config->getTestFramework(),
                    $config->shouldSkipCoverage()
                );
            },
            InitialTestRunProcessBuilder::class => static function (self $container): InitialTestRunProcessBuilder {
                /** @var TestFrameworkAdapter $adapter */
                $adapter = $container[TestFrameworkAdapter::class];

                /** @var VersionParser $versionParser */
                $versionParser = $container[VersionParser::class];

                return new InitialTestRunProcessBuilder($adapter, $versionParser);
            },
            InitialTestsRunner::class => static function (self $container): InitialTestsRunner {
                /** @var EventDispatcherInterface $eventDispatcher */
                $eventDispatcher = $container['dispatcher'];

                /** @var InitialTestRunProcessBuilder $processBuilder */
                $processBuilder = $container[InitialTestRunProcessBuilder::class];

                return new InitialTestsRunner($processBuilder, $eventDispatcher);
            },
            MutantProcessBuilder::class => static function (self $container): MutantProcessBuilder {
                /** @var TestFrameworkAdapter $adapter */
                $adapter = $container[TestFrameworkAdapter::class];

                /** @var Configuration $config */
                $config = $container[Configuration::class];

                /** @var VersionParser $versionParser */
                $versionParser = $container[VersionParser::class];

                return new MutantProcessBuilder(
                    $adapter,
                    $versionParser,
                    $config->getProcessTimeout()
                );
            },
            MutationGenerator::class => static function (self $container): MutationGenerator {
                /** @var TestFrameworkAdapter $adapter */
                $adapter = $container[TestFrameworkAdapter::class];

                /** @var Configuration $config */
                $config = $container[Configuration::class];

                /** @var EventDispatcherInterface $eventDispatcher */
                $eventDispatcher = $container['dispatcher'];

                /** @var XMLLineCodeCoverageFactory $codeCoverageFactory */
                $codeCoverageFactory = $container[XMLLineCodeCoverageFactory::class];

                /** @var FileMutationGenerator $fileMutationGenerator */
                $fileMutationGenerator = $container[FileMutationGenerator::class];

                return new MutationGenerator(
                    $config->getSourceFiles(),
                    $codeCoverageFactory->create($config->getTestFramework(), $adapter),
                    $config->getMutators(),
                    $eventDispatcher,
                    $fileMutationGenerator
                );
            },
            MutationTestingRunner::class => static function (self $container): MutationTestingRunner {
                /** @var EventDispatcherInterface $eventDispatcher */
                $eventDispatcher = $container['dispatcher'];

                /** @var MutantProcessBuilder $processBuilder */
                $processBuilder = $container[MutantProcessBuilder::class];

                /** @var ParallelProcessRunner $parallelProcessRunner */
                $parallelProcessRunner = $container['parallel.process.runner'];

                /** @var MutantCreator $mutantCreator */
                $mutantCreator = $container['mutant.creator'];

                return new MutationTestingRunner(
                    $processBuilder,
                    $parallelProcessRunner,
                    $mutantCreator,
                    $eventDispatcher
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

        $clone[SchemaConfiguration::class] = static function (self $container) use ($configFile): SchemaConfiguration {
            /** @var SchemaConfigurationLoader $schemaConfigLoader */
            $schemaConfigLoader = $container[SchemaConfigurationLoader::class];

            return $schemaConfigLoader->loadConfiguration(array_filter([
                $configFile,
                SchemaConfigurationLoader::DEFAULT_DIST_CONFIG_FILE,
                SchemaConfigurationLoader::DEFAULT_CONFIG_FILE,
            ]));
        };

        $clone[Configuration::class] = static function (self $container) use (
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
            /** @var ConfigurationFactory $configurationFactory */
            $configurationFactory = $container[ConfigurationFactory::class];

            /** @var SchemaConfiguration $schemaConfig */
            $schemaConfig = $container[SchemaConfiguration::class];

            return $configurationFactory->create(
                $schemaConfig,
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
        };

        return $clone;
    }
}
