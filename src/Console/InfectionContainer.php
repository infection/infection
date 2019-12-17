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
use function getcwd;
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
use Infection\Mutation\FileParser;
use Infection\Mutation\NodeTraverserFactory;
use Infection\Mutator\MutatorFactory;
use Infection\Mutator\MutatorParser;
use Infection\Performance\Limiter\MemoryLimiter;
use Infection\Performance\Memory\MemoryFormatter;
use Infection\Performance\Time\TimeFormatter;
use Infection\Performance\Time\Timer;
use Infection\Process\Builder\SubscriberBuilder;
use Infection\Process\Coverage\CoverageRequirementChecker;
use Infection\Process\Runner\Parallel\ParallelProcessRunner;
use Infection\Process\Runner\TestRunConstraintChecker;
use Infection\TestFramework\CommandLineBuilder;
use Infection\TestFramework\Config\TestFrameworkConfigLocator;
use Infection\TestFramework\Coverage\CachedTestFileDataProvider;
use Infection\TestFramework\Coverage\JUnitTestFileDataProvider;
use Infection\TestFramework\Coverage\TestFileDataProvider;
use Infection\TestFramework\Coverage\XMLLineCodeCoverage;
use Infection\TestFramework\Factory;
use Infection\TestFramework\PhpUnit\Config\Path\PathReplacer;
use Infection\TestFramework\PhpUnit\Config\XmlConfigurationHelper;
use Infection\TestFramework\TestFrameworkAdapter;
use Infection\Utils\VersionParser;
use function php_ini_loaded_file;
use PhpParser\Lexer;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use Pimple\Container;
use function Safe\sprintf;
use SebastianBergmann\Diff\Differ as BaseDiffer;
use Symfony\Component\Filesystem\Filesystem;

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
            'coverage.dir.phpunit' => static function (self $container) {
                return sprintf(
                    '%s/%s',
                    $container['coverage.path'],
                    XMLLineCodeCoverage::PHP_UNIT_COVERAGE_DIR
                );
            },
            'coverage.dir.phpspec' => static function (self $container) {
                return sprintf(
                    '%s/%s',
                    $container['coverage.path'],
                    XMLLineCodeCoverage::PHP_SPEC_COVERAGE_DIR
                );
            },
            'coverage.dir.codeception' => static function (self $container) {
                return sprintf('%s/%s', $container['coverage.path'], XMLLineCodeCoverage::CODECEPTION_COVERAGE_DIR);
            },
            'junit.file.path' => static function (self $container) {
                return sprintf(
                    '%s/%s',
                    $container['coverage.path'],
                    TestFrameworkAdapter::JUNIT_FILE_NAME
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
                        'comments', 'startLine', 'endLine', 'startTokenPos', 'endTokenPos', 'startFilePos', 'endFilePos',
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
                return new MemoryLimiter($container['filesystem'], php_ini_loaded_file());
            },
            SchemaConfigurationLoader::class => static function (self $container): SchemaConfigurationLoader {
                return new SchemaConfigurationLoader(
                    $container[RootsFileLocator::class],
                    $container[SchemaConfigurationFileLoader::class]
                );
            },
            RootsFileLocator::class => static function (self $container): RootsFileLocator {
                return new RootsFileLocator(
                    [$container['project.dir']],
                    $container['filesystem']
                );
            },
            SchemaConfigurationFileLoader::class => static function (self $container): SchemaConfigurationFileLoader {
                return new SchemaConfigurationFileLoader(
                    $container[SchemaValidator::class],
                    $container[SchemaConfigurationFactory::class]
                );
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
            'coverage.path' => static function (self $container): string {
                /** @var Configuration $config */
                $config = $container[Configuration::class];

                $existingCoveragePath = (string) $config->getExistingCoveragePath();

                if ($existingCoveragePath === '') {
                    return $config->getTmpDir();
                }

                return $container['filesystem']->isAbsolutePath($existingCoveragePath)
                    ? $existingCoveragePath
                    : sprintf('%s/%s', getcwd(), $existingCoveragePath)
                ;
            },
            'coverage.checker' => static function (self $container): CoverageRequirementChecker {
                /** @var Configuration $config */
                $config = $container[Configuration::class];

                return new CoverageRequirementChecker(
                    (string) $config->getExistingCoveragePath() !== '',
                    $config->getInitialTestsPhpOptions() ?? ''
                );
            },
            'test.run.constraint.checker' => static function (self $container): TestRunConstraintChecker {
                /** @var Configuration $config */
                $config = $container[Configuration::class];

                return new TestRunConstraintChecker(
                    $container['metrics'],
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

                return new SubscriberBuilder(
                    $config->showMutations(),
                    $config->isDebugEnabled(),
                    $config->getFormatter(),
                    $config->showProgress(),
                    $container['metrics'],
                    $container['dispatcher'],
                    $container['diff.colorizer'],
                    $config,
                    $container['filesystem'],
                    $config->getTmpDir(),
                    $container['timer'],
                    $container['time.formatter'],
                    $container['memory.formatter'],
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
            LoggerFactory::class => static function (self $container): LoggerFactory {
                /** @var Configuration $config */
                $config = $container[Configuration::class];
                /** @var MetricsCalculator $metrics */
                $metrics = $container['metrics'];
                /** @var Filesystem $fileSystem */
                $fileSystem = $container['filesystem'];

                return new LoggerFactory(
                    $metrics,
                    $fileSystem,
                    $config->getLogVerbosity(),
                    $config->isDebugEnabled(),
                    $config->mutateOnlyCoveredCode()
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
        ?string $testFrameworkOptions,
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
            $testFrameworkOptions,
            $filter
        ): Configuration {
            /** @var ConfigurationFactory $configurationFactory */
            $configurationFactory = $container[ConfigurationFactory::class];

            return $configurationFactory->create(
                $container[SchemaConfiguration::class],
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
                $testFrameworkOptions,
                $filter
            );
        };

        return $clone;
    }
}
