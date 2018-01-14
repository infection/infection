<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

use Pimple\Container;
use Symfony\Component\Console\Application;
use Infection\Utils\TempDirectoryCreator;
use Infection\TestFramework\Factory;
use Infection\Differ\Differ;
use Infection\Mutant\MutantCreator;
use Infection\Process\Runner\Parallel\ParallelProcessRunner;
use Infection\EventDispatcher\EventDispatcher;
use Infection\Filesystem\Filesystem;
use Infection\Finder\Locator;
use Infection\TestFramework\PhpUnit\Config\Path\PathReplacer;
use Infection\TestFramework\Config\TestFrameworkConfigLocator;
use Infection\TestFramework\PhpUnit\Coverage\PhpUnitTestFileDataProvider;
use Infection\TestFramework\Coverage\TestFileDataProvider;
use Infection\TestFramework\Coverage\CodeCoverageData;
use Infection\Differ\DiffColorizer;
use Infection\TestFramework\PhpUnit\Adapter\PhpUnitAdapter;
use Infection\Config\InfectionConfig;
use Infection\Utils\VersionParser;
use Infection\Command;
use Infection\TestFramework\Coverage\CachedTestFileDataProvider;
use Infection\TestFramework\PhpUnit\Config\XmlConfigurationHelper;
use SebastianBergmann\Diff\Differ as BaseDiffer;
use PhpParser\Lexer;
use PhpParser\ParserFactory;
use PhpParser\Parser;
use PhpParser\PrettyPrinter\Standard;

$c = new Container();

$c['src.dirs'] = function (Container $c): array {
    return $c['infection.config']->getSourceDirs();
};

$c['exclude.paths'] = function (Container $c): array {
    return $c['infection.config']->getSourceExcludePaths();
};

$c['project.dir'] = getcwd();

$c['phpunit.config.dir'] = function (Container $c): string {
    return $c['infection.config']->getPhpUnitConfigDir();
};

$c['temp.dir'] = function (Container $c): string {
    return $c['temp.dir.creator']->createAndGet();
};

$c['filesystem'] = function (): Filesystem {
    return new Filesystem();
};

$c['temp.dir.creator'] = function (): TempDirectoryCreator {
    return new TempDirectoryCreator();
};

$c['coverage.dir.phpunit'] = function (Container $c): string {
    return $c['temp.dir'] . '/' . CodeCoverageData::PHP_UNIT_COVERAGE_DIR;
};

$c['coverage.dir.phpspec'] = function (Container $c): string {
    return $c['temp.dir'] . '/' . CodeCoverageData::PHP_SPEC_COVERAGE_DIR;
};

$c['locator'] = function (Container $c): Locator {
    return new Locator([$c['project.dir']]);
};

$c['path.replacer'] = function (Container $c): PathReplacer {
    return new PathReplacer($c['locator'], $c['phpunit.config.dir']);
};

$c['test.framework.factory'] = function (Container $c): Factory {
    return new Factory($c['temp.dir'], $c['project.dir'], $c['testframework.config.locator'], $c['xml.configuration.helper'], $c['phpunit.junit.file.path'], $c['infection.config'], $c['version.parser']);
};

$c['xml.configuration.helper'] = function (Container $c): XmlConfigurationHelper {
    return new XmlConfigurationHelper($c['path.replacer']);
};

$c['mutant.creator'] = function (Container $c): MutantCreator {
    return new MutantCreator($c['temp.dir'], $c['differ'], $c['parser'], $c['pretty.printer']);
};

$c['differ'] = function (): Differ {
    return new Differ(
        new BaseDiffer()
    );
};

$c['dispatcher'] = function (): EventDispatcher {
    return new EventDispatcher();
};

$c['parallel.process.runner'] = function (Container $c): ParallelProcessRunner {
    return new ParallelProcessRunner($c['dispatcher']);
};

$c['testframework.config.locator'] = function (Container $c): TestFrameworkConfigLocator {
    return new TestFrameworkConfigLocator($c['phpunit.config.dir']/*[phpunit.dir, phpspec.dir, ...]*/);
};

$c['diff.colorizer'] = function (): DiffColorizer {
    return new DiffColorizer();
};

$c['phpunit.junit.file.path'] = function (Container $c): string {
    return $c['temp.dir'] . '/' . PhpUnitAdapter::JUNIT_FILE_NAME;
};

$c['test.file.data.provider.phpunit'] = function (Container $c): TestFileDataProvider {
    return new CachedTestFileDataProvider(
        new PhpUnitTestFileDataProvider($c['phpunit.junit.file.path'])
    );
};

$c['version.parser'] = function (): VersionParser {
    return new VersionParser();
};

$c['lexer'] = function (): Lexer {
    return new Lexer([
        'usedAttributes' => [
            'comments', 'startLine', 'endLine', 'startTokenPos', 'endTokenPos', 'startFilePos', 'endFilePos',
        ],
    ]);
};

$c['parser'] = function ($c): Parser {
    return (new ParserFactory())->create(ParserFactory::PREFER_PHP7, $c['lexer']);
};

$c['pretty.printer'] = function (): Standard {
    return new Standard();
};

function registerMutators(array $mutators, Container $container)
{
    foreach ($mutators as $mutator) {
        $container[$mutator] = function () use ($mutator) {
            return new $mutator();
        };
    }
}

registerMutators(InfectionConfig::DEFAULT_MUTATORS, $c);

$c['application'] = function (Container $container): Application {
    $application = new Application(
        'Infection - PHP Mutation Testing Framework',
        '@package_version@'
    );

    $application->add(new Command\ConfigureCommand());
    $application->add(new Command\SelfUpdateCommand());
    $application->add(new Command\InfectionCommand($container));

    return $application;
};

return $c;
