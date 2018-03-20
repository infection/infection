<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

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

$c['filesystem'] = function (): Filesystem {
    return new Filesystem();
};

$c['tmp.dir.creator'] = function (Container $c): TmpDirectoryCreator {
    return new TmpDirectoryCreator($c['filesystem']);
};

$c['tmp.dir'] = function (Container $c): string {
    return $c['tmp.dir.creator']->createAndGet($c['infection.config']->getTmpDir());
};

$c['coverage.dir.phpunit'] = function (Container $c) {
    return sprintf('%s/%s', $c['coverage.path'], CodeCoverageData::PHP_UNIT_COVERAGE_DIR);
};

$c['coverage.dir.phpspec'] = function (Container $c) {
    return sprintf('%s/%s', $c['coverage.path'], CodeCoverageData::PHP_SPEC_COVERAGE_DIR);
};

$c['phpunit.junit.file.path'] = function (Container $c) {
    return sprintf('%s/%s', $c['coverage.path'], PhpUnitAdapter::JUNIT_FILE_NAME);
};

$c['locator'] = function (Container $c): Locator {
    return new Locator([$c['project.dir']], $c['filesystem']);
};

$c['path.replacer'] = function (Container $c): PathReplacer {
    return new PathReplacer($c['filesystem'], $c['phpunit.config.dir']);
};

$c['test.framework.factory'] = function (Container $c): Factory {
    return new Factory($c['tmp.dir'], $c['project.dir'], $c['testframework.config.locator'], $c['xml.configuration.helper'], $c['phpunit.junit.file.path'], $c['infection.config'], $c['version.parser']);
};

$c['xml.configuration.helper'] = function (Container $c): XmlConfigurationHelper {
    return new XmlConfigurationHelper($c['path.replacer']);
};

$c['mutant.creator'] = function (Container $c): MutantCreator {
    return new MutantCreator($c['tmp.dir'], $c['differ'], $c['pretty.printer']);
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

$c['test.file.data.provider.phpunit'] = function (Container $c): TestFileDataProvider {
    return new CachedTestFileDataProvider(
        new PhpUnitTestFileDataProvider($c['phpunit.junit.file.path'])
    );
};

$c['version.parser'] = function (): VersionParser {
    return new VersionParser();
};

$c['lexer'] = function (): Lexer {
    return new Lexer\Emulative([
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

$c['mutators'] = function (Container $c): array {
    $mutatorConfig = $c['infection.config']->getMutatorsConfiguration();

    return (new MutatorsGenerator($mutatorConfig))->generate();
};

return $c;
