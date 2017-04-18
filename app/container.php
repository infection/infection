<?php

declare(strict_types=1);

use Symfony\Component\Console\Application;
use Infection\Utils\TempDirectoryCreator;
use Infection\TestFramework\Factory;
use Infection\Mutant\Generator\MutationsGenerator;
use Infection\Differ\Differ;
use Infection\Mutant\MutantCreator;
use Infection\Command\InfectionCommand;
use Infection\Process\Runner\Parallel\ParallelProcessRunner;
use Infection\EventDispatcher\EventDispatcher;

$c = new Pimple\Container();

$c['src.dir'] = 'src';

$c['temp.dir'] = function ($c) : string {
    return $c['temp.dir.creator']->createAndGet();
};

$c['temp.dir.creator'] = function () : TempDirectoryCreator {
    return new TempDirectoryCreator();
};

$c['test.framework.factory'] = function ($c) : Factory {
    return new Factory($c['temp.dir']);
};

$c['mutations.generator'] = function ($c) : MutationsGenerator {
    return new MutationsGenerator($c['src.dir']);
};

$c['mutant.creator'] = function ($c) : MutantCreator {
    return new MutantCreator($c['temp.dir'], $c['differ']);
};

$c['differ'] = function () : Differ {
    return new Differ();
};

$c['dispatcher'] = function () : EventDispatcher {
    return new EventDispatcher();
};

$c['parallel.process.runner'] = function ($c) : ParallelProcessRunner {
    return new ParallelProcessRunner($c['dispatcher']);
};

$c['application'] = function ($container) : Application {
    $application = new Application();
    $infectionCommand = new InfectionCommand($container);

    $application->add($infectionCommand);

    $application->setDefaultCommand($infectionCommand->getName(), true);

    return $application;
};

return $c;