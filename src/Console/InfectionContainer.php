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

use Infection\Config\ConfigCreatorFacade;
use Infection\Config\InfectionConfig;
use Infection\Differ\DiffColorizer;
use Infection\Differ\Differ;
use Infection\EventDispatcher\EventDispatcher;
use Infection\EventDispatcher\EventDispatcherInterface;
use Infection\Locator\RootsFileOrDirectoryLocator;
use Infection\Mutant\MetricsCalculator;
use Infection\Mutant\MutantCreator;
use Infection\Mutator\Util\MutatorParser;
use Infection\Mutator\Util\MutatorsGenerator;
use Infection\Performance\Limiter\MemoryLimiter;
use Infection\Performance\Memory\MemoryFormatter;
use Infection\Performance\Time\TimeFormatter;
use Infection\Performance\Time\Timer;
use Infection\Process\Builder\SubscriberBuilder;
use Infection\Process\Coverage\CoverageRequirementChecker;
use Infection\Process\Runner\Parallel\ParallelProcessRunner;
use Infection\Process\Runner\TestRunConstraintChecker;
use Infection\TestFramework\Config\TestFrameworkConfigLocator;
use Infection\TestFramework\Coverage\CachedTestFileDataProvider;
use Infection\TestFramework\Coverage\TestFileDataProvider;
use Infection\TestFramework\Coverage\XMLLineCodeCoverage;
use Infection\TestFramework\Factory;
use Infection\TestFramework\PhpUnit\Adapter\PhpUnitAdapter;
use Infection\TestFramework\PhpUnit\Config\Path\PathReplacer;
use Infection\TestFramework\PhpUnit\Config\XmlConfigurationHelper;
use Infection\TestFramework\PhpUnit\Coverage\PhpUnitTestFileDataProvider;
use Infection\Utils\TmpDirectoryCreator;
use Infection\Utils\VersionParser;
use PhpParser\Lexer;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use Pimple\Container;
use SebastianBergmann\Diff\Differ as BaseDiffer;
use Symfony\Component\Console\Exception\InvalidArgumentException;
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
            'tmp.dir.creator' => static function (self $container): TmpDirectoryCreator {
                return new TmpDirectoryCreator($container['filesystem']);
            },
            'tmp.dir' => static function (self $container): string {
                return $container['tmp.dir.creator']->createAndGet($container['infection.config']->getTmpDir());
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
            'phpunit.junit.file.path' => static function (self $container) {
                return sprintf(
                    '%s/%s',
                    $container['coverage.path'],
                    PhpUnitAdapter::JUNIT_FILE_NAME
                );
            },
            RootsFileOrDirectoryLocator::class => static function (self $container): RootsFileOrDirectoryLocator {
                return new RootsFileOrDirectoryLocator(
                    [$container['project.dir']],
                    $container['filesystem']
                );
            },
            'path.replacer' => static function (self $container): PathReplacer {
                return new PathReplacer(
                    $container['filesystem'],
                    $container['infection.config']->getPhpUnitConfigDir()
                );
            },
            'test.framework.factory' => static function (self $container): Factory {
                return new Factory(
                    $container['tmp.dir'],
                    $container['project.dir'],
                    $container['testframework.config.locator'],
                    $container['xml.configuration.helper'],
                    $container['phpunit.junit.file.path'],
                    $container['infection.config'],
                    $container['version.parser']
                );
            },
            'xml.configuration.helper' => static function (self $container): XmlConfigurationHelper {
                return new XmlConfigurationHelper(
                    $container['path.replacer'],
                    $container['infection.config']->getPhpUnitConfigDir()
                );
            },
            'mutant.creator' => static function (self $container): MutantCreator {
                return new MutantCreator(
                    $container['tmp.dir'],
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
                return new TestFrameworkConfigLocator(
                    $container['infection.config']->getPhpUnitConfigDir() /*[phpunit.dir, phpspec.dir, ...]*/
                );
            },
            'diff.colorizer' => static function (): DiffColorizer {
                return new DiffColorizer();
            },
            'test.file.data.provider.phpunit' => static function (self $container): TestFileDataProvider {
                return new CachedTestFileDataProvider(
                    new PhpUnitTestFileDataProvider($container['phpunit.junit.file.path'])
                );
            },
            'version.parser' => static function (): VersionParser {
                return new VersionParser();
            },
            'lexer' => static function (): Lexer {
                return new Lexer\Emulative([
                    'usedAttributes' => [
                        'comments', 'startLine', 'endLine', 'startTokenPos', 'endTokenPos', 'startFilePos', 'endFilePos',
                    ],
                ]);
            },
            'parser' => static function (self $container): Parser {
                return (new ParserFactory())->create(ParserFactory::PREFER_PHP7, $container['lexer']);
            },
            'pretty.printer' => static function (): Standard {
                return new Standard();
            },
            'mutators.config' => static function (self $container): array {
                return (new MutatorsGenerator(
                    $container['infection.config']->getMutatorsConfiguration()
                ))->generate();
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
                return new MemoryLimiter($container['filesystem'], \php_ini_loaded_file());
            },
        ]);
    }

    public function withDynamicParameters(
        ?string $configFile,
        ?string $mutators,
        bool $showMutations,
        string $logVerbosity,
        bool $debug,
        bool $onlyCovered,
        string $formatter,
        bool $noProgress,
        string $existingCoveragePath,
        string $initialTestsPhpOptions,
        bool $ignoreMsiWithNoMutations,
        float $minMsi,
        float $minCoveredMsi
    ): self {
        $clone = clone $this;

        $clone['infection.config'] = static function (self $container) use ($configFile): InfectionConfig {
            $facade = new ConfigCreatorFacade(
                $container[RootsFileOrDirectoryLocator::class],
                $container['filesystem']
            );

            return $facade->createConfig($configFile);
        };

        $clone['coverage.path'] = static function (self $container) use ($existingCoveragePath): string {
            if ($existingCoveragePath === '') {
                return $container['tmp.dir'];
            }

            return $container['filesystem']->isAbsolutePath($existingCoveragePath)
                ? $existingCoveragePath
                : sprintf('%s/%s', getcwd(), $existingCoveragePath)
            ;
        };

        $clone['coverage.checker'] = static function () use ($initialTestsPhpOptions, $existingCoveragePath): CoverageRequirementChecker {
            if (!\is_string($initialTestsPhpOptions)) {
                throw new InvalidArgumentException(
                    \sprintf(
                        'Expected initial-tests-php-options to be string, %s given',
                        \gettype($initialTestsPhpOptions)
                    )
                );
            }

            return new CoverageRequirementChecker(
                $existingCoveragePath !== '',
                $initialTestsPhpOptions
            );
        };

        $clone['test.run.constraint.checker'] = static function (self $container) use (
            $ignoreMsiWithNoMutations,
            $minMsi,
            $minCoveredMsi
        ): TestRunConstraintChecker {
            return new TestRunConstraintChecker(
                $container['metrics'],
                $ignoreMsiWithNoMutations,
                $minMsi,
                $minCoveredMsi
            );
        };

        $clone['subscriber.builder'] = static function (self $container) use (
            $showMutations,
            $logVerbosity,
            $debug,
            $onlyCovered,
            $formatter,
            $noProgress
        ): SubscriberBuilder {
            return new SubscriberBuilder(
                $showMutations,
                $logVerbosity,
                $debug,
                $onlyCovered,
                $formatter,
                $noProgress,
                $container['metrics'],
                $container['dispatcher'],
                $container['diff.colorizer'],
                $container['infection.config'],
                $container['filesystem'],
                $container['tmp.dir'],
                $container['timer'],
                $container['time.formatter'],
                $container['memory.formatter']
            );
        };

        $clone['mutators'] = static function (self $container) use ($mutators): array {
            $parser = new MutatorParser(
                $mutators,
                $container['mutators.config']
            );

            return $parser->getMutators();
        };

        return $clone;
    }
}
