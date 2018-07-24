<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Console;

use Infection\Config\Exception\InvalidConfigException;
use Infection\Config\InfectionConfig;
use Infection\Differ\DiffColorizer;
use Infection\Differ\Differ;
use Infection\EventDispatcher\EventDispatcher;
use Infection\EventDispatcher\EventDispatcherInterface;
use Infection\Finder\Locator;
use Infection\Mutant\MetricsCalculator;
use Infection\Mutant\MutantCreator;
use Infection\Mutator\Util\MutatorsGenerator;
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

        $this['src.dirs'] = function (): array {
            return $this->getInfectionConfig()->getSourceDirs();
        };

        $this['exclude.paths'] = function (): array {
            return $this->getInfectionConfig()->getSourceExcludePaths();
        };

        $this['project.dir'] = getcwd();

        $this['phpunit.config.dir'] = function (): string {
            return $this->getInfectionConfig()->getPhpUnitConfigDir();
        };

        $this['filesystem'] = function (): Filesystem {
            return new Filesystem();
        };

        $this['tmp.dir.creator'] = function (): TmpDirectoryCreator {
            return new TmpDirectoryCreator($this['filesystem']);
        };

        $this['tmp.dir'] = function (): string {
            return $this['tmp.dir.creator']->createAndGet($this->getInfectionConfig()->getTmpDir());
        };

        $this['coverage.dir.phpunit'] = function () {
            return sprintf('%s/%s', $this['coverage.path'], CodeCoverageData::PHP_UNIT_COVERAGE_DIR);
        };

        $this['coverage.dir.phpspec'] = function () {
            return sprintf('%s/%s', $this['coverage.path'], CodeCoverageData::PHP_SPEC_COVERAGE_DIR);
        };

        $this['phpunit.junit.file.path'] = function () {
            return sprintf('%s/%s', $this['coverage.path'], PhpUnitAdapter::JUNIT_FILE_NAME);
        };

        $this['locator'] = function (): Locator {
            return new Locator([$this['project.dir']], $this['filesystem']);
        };

        $this['path.replacer'] = function (): PathReplacer {
            return new PathReplacer($this['filesystem'], $this['phpunit.config.dir']);
        };

        $this['test.framework.factory'] = function (): Factory {
            return new Factory(
                $this['tmp.dir'],
                $this['project.dir'],
                $this['testframework.config.locator'],
                $this['xml.configuration.helper'],
                $this['phpunit.junit.file.path'],
                $this->getInfectionConfig(),
                $this['version.parser']
            );
        };

        $this['xml.configuration.helper'] = function (): XmlConfigurationHelper {
            return new XmlConfigurationHelper($this['path.replacer']);
        };

        $this['mutant.creator'] = function (): MutantCreator {
            return new MutantCreator(
                $this['tmp.dir'],
                $this['differ'],
                $this['pretty.printer']
            );
        };

        $this['differ'] = function (): Differ {
            return new Differ(
                new BaseDiffer()
            );
        };

        $this['dispatcher'] = function (): EventDispatcherInterface {
            return new EventDispatcher();
        };

        $this['parallel.process.runner'] = function (): ParallelProcessRunner {
            return new ParallelProcessRunner($this['dispatcher']);
        };

        $this['testframework.config.locator'] = function (): TestFrameworkConfigLocator {
            return new TestFrameworkConfigLocator(
                $this['phpunit.config.dir'] /*[phpunit.dir, phpspec.dir, ...]*/
            );
        };

        $this['diff.colorizer'] = function (): DiffColorizer {
            return new DiffColorizer();
        };

        $this['test.file.data.provider.phpunit'] = function (): TestFileDataProvider {
            return new CachedTestFileDataProvider(
                new PhpUnitTestFileDataProvider($this['phpunit.junit.file.path'])
            );
        };

        $this['version.parser'] = function (): VersionParser {
            return new VersionParser();
        };

        $this['lexer'] = function (): Lexer {
            return new Lexer\Emulative([
                'usedAttributes' => [
                    'comments', 'startLine', 'endLine', 'startTokenPos', 'endTokenPos', 'startFilePos', 'endFilePos',
                ],
            ]);
        };

        $this['parser'] = function (): Parser {
            return (new ParserFactory())->create(ParserFactory::PREFER_PHP7, $this['lexer']);
        };

        $this['pretty.printer'] = function (): Standard {
            return new Standard();
        };

        $this['mutators'] = function (): array {
            $mutatorConfig = $this->getInfectionConfig()->getMutatorsConfiguration();

            return (new MutatorsGenerator($mutatorConfig))->generate();
        };

        $this['metrics'] = function (): MetricsCalculator {
            return new MetricsCalculator();
        };
    }

    private function getInfectionConfig(): InfectionConfig
    {
        return $this['infection.config'];
    }

    public function buildDynamicDependencies(InputInterface $input): void
    {
        $this['infection.config'] = function () use ($input): InfectionConfig {
            try {
                $configPaths = [];
                $customConfigPath = $input->getOption('configuration');

                if ($customConfigPath) {
                    $configPaths[] = $customConfigPath;
                }

                $configPaths = array_merge(
                    $configPaths,
                    InfectionConfig::POSSIBLE_CONFIG_FILE_NAMES
                );

                $infectionConfigFile = $this['locator']->locateAnyOf($configPaths);
                $configLocation = \pathinfo($infectionConfigFile, PATHINFO_DIRNAME);
                $json = file_get_contents($infectionConfigFile);
            } catch (\Exception $e) {
                $infectionConfigFile = null;
                $json = '{}';
                $configLocation = getcwd();
            }

            \assert(\is_string($json));
            $config = json_decode($json);

            if (\is_string($infectionConfigFile) && null === $config && JSON_ERROR_NONE !== json_last_error()) {
                throw InvalidConfigException::invalidJson(
                    $infectionConfigFile,
                    json_last_error_msg()
                );
            }

            // getcwd() may return false in rare circumstances
            \assert(\is_string($configLocation));

            return new InfectionConfig($config, $this['filesystem'], $configLocation);
        };

        $this['coverage.path'] = function () use ($input): string {
            $existingCoveragePath = trim($input->getOption('coverage'));

            if ($existingCoveragePath === '') {
                return $this['tmp.dir'];
            }

            return $this['filesystem']->isAbsolutePath($existingCoveragePath)
                ? $existingCoveragePath
                : sprintf('%s/%s', getcwd(), $existingCoveragePath);
        };

        $this['coverage.checker'] = function () use ($input): CoverageRequirementChecker {
            return new CoverageRequirementChecker(
                \strlen(trim($input->getOption('coverage'))) > 0,
                $input->getOption('initial-tests-php-options')
            );
        };

        $this['test.run.constraint.checker'] = function () use ($input): TestRunConstraintChecker {
            return new TestRunConstraintChecker(
                $this['metrics'],
                $input->getOption('ignore-msi-with-no-mutations'),
                (float) $input->getOption('min-msi'),
                (float) $input->getOption('min-covered-msi')
            );
        };

        $this['subscriber.builder'] = function () use ($input): SubscriberBuilder {
            return new SubscriberBuilder(
                $input,
                $this['metrics'],
                $this['dispatcher'],
                $this['diff.colorizer'],
                $this['infection.config'],
                $this['filesystem'],
                $this['tmp.dir']
            );
        };
    }
}
