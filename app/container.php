<?php

declare(strict_types=1);

use Pimple\Container;
use Symfony\Component\Console\Application;
use Infection\Utils\TempDirectoryCreator;
use Infection\TestFramework\Factory;
use Infection\Differ\Differ;
use Infection\Mutant\MutantCreator;
use Infection\Command\InfectionCommand;
use Infection\Process\Runner\Parallel\ParallelProcessRunner;
use Infection\EventDispatcher\EventDispatcher;
use Infection\Finder\Locator;
use Infection\TestFramework\PhpUnit\Config\Path\PathReplacer;
use Infection\TestFramework\Config\TestFrameworkConfigLocator;
use Infection\TestFramework\PhpUnit\Coverage\CoverageXmlParser;
use Infection\TestFramework\Coverage\CodeCoverageData;
use Infection\Utils\InfectionConfig;

$c = new Container();

$c['src.dir'] = 'src';
$c['project.dir'] = getcwd();
$c['timeout'] = 10; // seconds
$c['phpunit.config.dir'] = function (Container $c): string {
    return $c['infection.config']->getPhpUnitConfigDir();
};

$c['temp.dir'] = function (Container $c) : string {
    return $c['temp.dir.creator']->createAndGet();
};

$c['infection.config'] = function (Container $c) : InfectionConfig {
    try {
        $infectionConfigFile = $c['locator']->locateAnyOf(['infection.json', 'infection.json.dist']);
        $json = file_get_contents($infectionConfigFile);
    } catch (\Exception $e) {
        $json = '{}';
    }

    return new InfectionConfig(json_decode($json));
};

$c['temp.dir.creator'] = function () : TempDirectoryCreator {
    return new TempDirectoryCreator();
};

$c['coverage.dir'] = function (Container $c) : string {
    return $c['temp.dir'] . '/' . CodeCoverageData::COVERAGE_DIR;
};

$c['locator'] = function (Container $c) : Locator {
    return new Locator([$c['project.dir']]);
};

$c['path.replacer'] = function(Container $c) : PathReplacer {
    return new PathReplacer($c['locator'], $c['phpunit.config.dir']);
};

$c['test.framework.factory'] = function (Container $c) : Factory {
    return new Factory($c['temp.dir'], $c['testframework.config.locator'], $c['path.replacer']);
};

$c['mutant.creator'] = function (Container $c) : MutantCreator {
    return new MutantCreator($c['temp.dir'], $c['differ']);
};

$c['differ'] = function () : Differ {
    return new Differ();
};

$c['dispatcher'] = function () : EventDispatcher {
    return new EventDispatcher();
};

$c['parallel.process.runner'] = function (Container $c) : ParallelProcessRunner {
    return new ParallelProcessRunner($c['dispatcher']);
};

$c['testframework.config.locator'] = function (Container $c) : TestFrameworkConfigLocator {
    return new TestFrameworkConfigLocator($c['phpunit.config.dir']);
};

$c['coverage.parser'] = function (Container $c) : CoverageXmlParser {
    return new CoverageXmlParser($c['coverage.dir'], $c['src.dir']);
};

$c['coverage.data'] = function (Container $c) : CodeCoverageData {
    return new CodeCoverageData($c['coverage.dir'], $c['coverage.parser']);
};

$c['application'] = function (Container $container) : Application {
    $application = new Application();
    $infectionCommand = new InfectionCommand($container);

    $application->add($infectionCommand);

    $application->setDefaultCommand($infectionCommand->getName(), true);

    return $application;
};

return $c;