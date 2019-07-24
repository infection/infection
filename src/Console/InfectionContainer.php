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
use Infection\TestFramework\Coverage\CodeCoverageData;
use Infection\TestFramework\Coverage\TestFileDataProvider;
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
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @internal
 */
final class InfectionContainer extends Container
{
    public function __construct(array $values = [])
    {
        parent::__construct($values);

        $this['project.dir'] = getcwd();

        $this['filesystem'] = static function (): Filesystem {
            return new Filesystem();
        };

        $this['tmp.dir.creator'] = static function (InfectionContainer $container): TmpDirectoryCreator {
            return new TmpDirectoryCreator($container['filesystem']);
        };

        $this['tmp.dir'] = static function (InfectionContainer $container): string {
            return $container['tmp.dir.creator']->createAndGet($container['infection.config']->getTmpDir());
        };

        $this['coverage.dir.phpunit'] = static function (InfectionContainer $container) {
            return sprintf(
                '%s/%s',
                $container['coverage.path'],
                CodeCoverageData::PHP_UNIT_COVERAGE_DIR
            );
        };

        $this['coverage.dir.phpspec'] = static function (InfectionContainer $container) {
            return sprintf(
                '%s/%s',
                $container['coverage.path'],
                CodeCoverageData::PHP_SPEC_COVERAGE_DIR
            );
        };

        $this['phpunit.junit.file.path'] = static function (InfectionContainer $container) {
            return sprintf(
                '%s/%s',
                $container['coverage.path'],
                PhpUnitAdapter::JUNIT_FILE_NAME
            );
        };

        $this[RootsFileOrDirectoryLocator::class] = static function (InfectionContainer $container): RootsFileOrDirectoryLocator {
            return new RootsFileOrDirectoryLocator(
                [$container['project.dir']], 
                $container['filesystem']
            );
        };

        $this['path.replacer'] = static function (InfectionContainer $container): PathReplacer {
            return new PathReplacer(
                $container['filesystem'], 
                $container['infection.config']->getPhpUnitConfigDir()
            );
        };

        $this['test.framework.factory'] = static function (InfectionContainer $container): Factory {
            return new Factory(
                $container['tmp.dir'],
                $container['project.dir'],
                $container['testframework.config.locator'],
                $container['xml.configuration.helper'],
                $container['phpunit.junit.file.path'],
                $container['infection.config'],
                $container['version.parser']
            );
        };

        $this['xml.configuration.helper'] = static function (InfectionContainer $container): XmlConfigurationHelper {
            return new XmlConfigurationHelper(
                $container['path.replacer'], 
                $container['infection.config']->getPhpUnitConfigDir()
            );
        };

        $this['mutant.creator'] = static function (InfectionContainer $container): MutantCreator {
            return new MutantCreator(
                $container['tmp.dir'],
                $container['differ'],
                $container['pretty.printer']
            );
        };

        $this['differ'] = static function (): Differ {
            return new Differ(
                new BaseDiffer()
            );
        };

        $this['dispatcher'] = static function (): EventDispatcherInterface {
            return new EventDispatcher();
        };

        $this['parallel.process.runner'] = static function (InfectionContainer $container): ParallelProcessRunner {
            return new ParallelProcessRunner($container['dispatcher']);
        };

        $this['testframework.config.locator'] = static function (InfectionContainer $container): TestFrameworkConfigLocator {
            return new TestFrameworkConfigLocator(
                $container['infection.config']->getPhpUnitConfigDir() /*[phpunit.dir, phpspec.dir, ...]*/
            );
        };

        $this['diff.colorizer'] = static function (): DiffColorizer {
            return new DiffColorizer();
        };

        $this['test.file.data.provider.phpunit'] = static function (InfectionContainer $container): TestFileDataProvider {
            return new CachedTestFileDataProvider(
                new PhpUnitTestFileDataProvider($container['phpunit.junit.file.path'])
            );
        };

        $this['version.parser'] = static function (): VersionParser {
            return new VersionParser();
        };

        $this['lexer'] = static function (): Lexer {
            return new Lexer\Emulative([
                'usedAttributes' => [
                    'comments', 'startLine', 'endLine', 'startTokenPos', 'endTokenPos', 'startFilePos', 'endFilePos',
                ],
            ]);
        };

        $this['parser'] = static function (InfectionContainer $container): Parser {
            return (new ParserFactory())->create(ParserFactory::PREFER_PHP7, $container['lexer']);
        };

        $this['pretty.printer'] = static function (): Standard {
            return new Standard();
        };

        $this['mutators.config'] = static function (InfectionContainer $container): array {
            return (new MutatorsGenerator(
                $container['infection.config']->getMutatorsConfiguration()
            ))->generate();
        };

        $this['metrics'] = static function (): MetricsCalculator {
            return new MetricsCalculator();
        };

        $this['timer'] = static function (): Timer {
            return new Timer();
        };

        $this['time.formatter'] = static function (): TimeFormatter {
            return new TimeFormatter();
        };

        $this['memory.formatter'] = static function (): MemoryFormatter {
            return new MemoryFormatter();
        };

        $this['memory.limit.applier'] = static function (InfectionContainer $container): MemoryLimiter {
            return new MemoryLimiter($container['filesystem'], \php_ini_loaded_file());
        };
    }

    public function buildDynamicDependencies(InputInterface $input): void
    {
        $this['infection.config'] = static function (InfectionContainer $container) use ($input): InfectionConfig {
            $facade = new ConfigCreatorFacade(
                $container[RootsFileOrDirectoryLocator::class],
                $container['filesystem']
            );

            return $facade->createConfig($input->getOption('configuration'));
        };

        $this['coverage.path'] = static function (InfectionContainer $container) use ($input): string {
            $existingCoveragePath = '';

            if ($input->hasOption('coverage')) {
                $existingCoveragePath = trim($input->getOption('coverage'));
            }

            if ($existingCoveragePath === '') {
                return $container['tmp.dir'];
            }

            return $container['filesystem']->isAbsolutePath($existingCoveragePath)
                ? $existingCoveragePath
                : sprintf('%s/%s', getcwd(), $existingCoveragePath)
            ;
        };

        $this['coverage.checker'] = static function () use ($input): CoverageRequirementChecker {
            $initialTestsPhpOptions = $input->getOption('initial-tests-php-options') ?? '';

            if (!\is_string($initialTestsPhpOptions)) {
                throw new InvalidArgumentException(
                    \sprintf(
                        'Expected initial-tests-php-options to be string, %s given',
                        \gettype($initialTestsPhpOptions)
                    )
                );
            }

            return new CoverageRequirementChecker(
                \strlen(trim($input->getOption('coverage'))) > 0,
                $initialTestsPhpOptions
            );
        };

        $this['test.run.constraint.checker'] = static function (InfectionContainer $container) use ($input): TestRunConstraintChecker {
            return new TestRunConstraintChecker(
                $container['metrics'],
                $input->getOption('ignore-msi-with-no-mutations'),
                (float) $input->getOption('min-msi'),
                (float) $input->getOption('min-covered-msi')
            );
        };

        $this['subscriber.builder'] = static function (InfectionContainer $container) use ($input): SubscriberBuilder {
            return new SubscriberBuilder(
                $input,
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

        $this['mutators'] = static function (InfectionContainer $container) use ($input): array {
            $parser = new MutatorParser(
                $input->getOption('mutators'),
                $container['mutators.config']
            );

            return $parser->getMutators();
        };
    }
}
