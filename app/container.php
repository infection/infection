<?php

declare(strict_types=1);

use Pimple\Container;
use Symfony\Component\Console\Application;
use Infection\Utils\TempDirectoryCreator;
use Infection\TestFramework\Factory;
use Infection\Mutant\Generator\MutationsGenerator;
use Infection\Differ\Differ;
use Infection\Mutant\MutantCreator;
use Infection\Command\InfectionCommand;
use Infection\Process\Runner\Parallel\ParallelProcessRunner;
use Infection\EventDispatcher\EventDispatcher;
use Infection\Finder\Locator;
use Infection\TestFramework\PhpUnit\Config\Path\PathReplacer;
use Infection\TestFramework\Config\ConfigLocator;

$c = new Container();

$c['src.dir'] = 'src';
$c['project.dir'] = getcwd();
$c['config.dir'] = getcwd();

$c['temp.dir'] = function (Container $c) : string {
    return $c['temp.dir.creator']->createAndGet();
};

$c['temp.dir.creator'] = function () : TempDirectoryCreator {
    return new TempDirectoryCreator();
};

$c['locator'] = function (Container $c) : Locator {
    return new Locator($c['project.dir']);
};

$c['path.replacer'] = function(Container $c) : PathReplacer {
    return new PathReplacer($c['locator']);
};

$c['test.framework.factory'] = function (Container $c) : Factory {
    return new Factory($c['temp.dir'], $c['config.locator'], $c['path.replacer']);
};

$c['mutations.generator'] = function (Container $c) : MutationsGenerator {
    return new MutationsGenerator($c['src.dir']);
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

$c['config.locator'] = function (Container $c) : ConfigLocator {
    return new ConfigLocator($c['config.dir']);
};


$c['application'] = function (Container $container) : Application {
    $application = new Application();
    $infectionCommand = new InfectionCommand($container);

    $application->add($infectionCommand);

    $application->setDefaultCommand($infectionCommand->getName(), true);

    return $application;
};

return $c;