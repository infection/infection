<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Fidry\Console\Command;

use _HumbugBoxb47773b41c19\Fidry\Console\Input\IO;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Command\Command as SymfonyCommand;
final class ReversedSymfonyCommand implements Command
{
    private SymfonyCommand $command;
    public function __construct(SymfonyCommand $command)
    {
        $this->command = $command;
    }
    public function getConfiguration() : Configuration
    {
        return new Configuration($this->command->getName() ?? '', $this->command->getDescription(), $this->command->getHelp(), $this->command->getDefinition()->getArguments(), $this->command->getDefinition()->getOptions());
    }
    public function execute(IO $io) : int
    {
        return $this->command->run($io->getInput(), $io->getOutput());
    }
}
