<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Fidry\Console\Command;

use _HumbugBoxb47773b41c19\Fidry\Console\Input\IO;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Application;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Command\Command as BaseSymfonyCommand;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Input\InputInterface;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Output\OutputInterface;
final class SymfonyCommand extends BaseSymfonyCommand
{
    private Command $command;
    /**
    @psalm-suppress */
    private IO $io;
    /**
    @psalm-suppress */
    private CommandRegistry $commandRegistry;
    public function __construct(Command $command)
    {
        $this->command = $command;
        $name = $command->getConfiguration()->getName();
        parent::__construct($name);
    }
    public function setApplication(?Application $application = null) : void
    {
        parent::setApplication($application);
        if (null !== $application) {
            $this->commandRegistry = new CommandRegistry($application);
        }
    }
    protected function configure() : void
    {
        $configuration = $this->command->getConfiguration();
        $this->setDescription($configuration->getDescription())->setHelp($configuration->getHelp());
        $definition = $this->getDefinition();
        $definition->setArguments($configuration->getArguments());
        $definition->setOptions($configuration->getOptions());
    }
    protected function initialize(InputInterface $input, OutputInterface $output) : void
    {
        $this->io = new IO($input, $output);
        $command = $this->command;
        if ($command instanceof CommandAware) {
            $command->setCommandRegistry($this->commandRegistry);
        }
        if ($command instanceof InitializableCommand) {
            $command->initialize($this->io);
        }
    }
    protected function interact(InputInterface $input, OutputInterface $output) : void
    {
        $command = $this->command;
        if ($command instanceof InteractiveCommand) {
            $command->interact($this->io);
        }
    }
    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        return $this->command->execute($this->io);
    }
}
