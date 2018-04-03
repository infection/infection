<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Console;

use Infection\Config\InfectionConfig;
use Infection\Differ\DiffColorizer;
use Infection\Differ\Differ;
use Infection\EventDispatcher\EventDispatcher;
use Infection\Finder\Locator;
use Infection\Mutant\MutantCreator;
use Infection\Mutator\Util\MutatorsGenerator;
use Infection\Process\Runner\Parallel\ParallelProcessRunner;
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

        $this['dispatcher'] = function (): EventDispatcher {
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
    }

    private function getInfectionConfig(): InfectionConfig
    {
        return $this['infection.config'];
    }
}
