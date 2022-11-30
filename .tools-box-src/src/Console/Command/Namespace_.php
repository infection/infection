<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\KevinGH\Box\Console\Command;

use function current;
use function explode;
use _HumbugBoxb47773b41c19\Fidry\Console\Command\Command;
use _HumbugBoxb47773b41c19\Fidry\Console\Command\Configuration;
use _HumbugBoxb47773b41c19\Fidry\Console\ExitCode;
use _HumbugBoxb47773b41c19\Fidry\Console\Input\IO;
final class Namespace_ implements Command
{
    public function getConfiguration() : Configuration
    {
        return new Configuration('namespace', 'Prints the first part of the command namespace', <<<'HELP'
This command is purely for debugging purposes to ensure it is scoped correctly.
HELP
);
    }
    public function execute(IO $io) : int
    {
        $namespace = current(explode('\\', self::class));
        $io->writeln($namespace);
        return ExitCode::SUCCESS;
    }
}
