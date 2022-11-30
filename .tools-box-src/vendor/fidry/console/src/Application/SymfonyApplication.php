<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Fidry\Console\Application;

use _HumbugBoxb47773b41c19\Fidry\Console\Command\Command as FidryCommand;
use _HumbugBoxb47773b41c19\Fidry\Console\Command\LazyCommand as FidryLazyCommand;
use _HumbugBoxb47773b41c19\Fidry\Console\Command\SymfonyCommand;
use _HumbugBoxb47773b41c19\Fidry\Console\Input\IO;
use LogicException;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Application as BaseSymfonyApplication;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Command\Command as BaseSymfonyCommand;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Command\LazyCommand as SymfonyLazyCommand;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\CommandLoader\CommandLoaderInterface;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Helper\HelperSet;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Input\InputDefinition;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Input\InputInterface;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Output\OutputInterface;
use _HumbugBoxb47773b41c19\Symfony\Contracts\Service\ResetInterface;
use function array_map;
use function array_values;
final class SymfonyApplication extends BaseSymfonyApplication
{
    private Application $application;
    public function __construct(Application $application)
    {
        $this->application = $application;
        parent::__construct($application->getName(), $application->getVersion());
        $this->setDefaultCommand($application->getDefaultCommand());
        $this->setAutoExit($application->isAutoExitEnabled());
        $this->setCatchExceptions($application->areExceptionsCaught());
    }
    public function reset() : void
    {
        if ($this->application instanceof ResetInterface) {
            $this->application->reset();
        }
    }
    public function setHelperSet(HelperSet $helperSet) : void
    {
        throw new LogicException('Not supported');
    }
    public function setDefinition(InputDefinition $definition) : void
    {
        throw new LogicException('Not supported');
    }
    public function getHelp() : string
    {
        return $this->application->getHelp();
    }
    public function getLongVersion() : string
    {
        return $this->application->getLongVersion();
    }
    public function setCommandLoader(CommandLoaderInterface $commandLoader) : void
    {
        throw new LogicException('Not supported');
    }
    public function setSignalsToDispatchEvent(int ...$signalsToDispatchEvent) : void
    {
        throw new LogicException('Not supported');
    }
    public function setName(string $name) : void
    {
        throw new LogicException('Not supported');
    }
    public function setVersion(string $version) : void
    {
        throw new LogicException('Not supported');
    }
    protected function configureIO(InputInterface $input, OutputInterface $output) : void
    {
        parent::configureIO($input, $output);
        if ($this->application instanceof ConfigurableIO) {
            $this->application->configureIO(new IO($input, $output));
        }
    }
    protected function getDefaultCommands() : array
    {
        return [...parent::getDefaultCommands(), ...$this->getSymfonyCommands()];
    }
    private function getSymfonyCommands() : array
    {
        return array_values(array_map(static fn(FidryCommand $command) => self::crateSymfonyCommand($command), $this->application->getCommands()));
    }
    private static function crateSymfonyCommand(FidryCommand $command) : BaseSymfonyCommand
    {
        if ($command instanceof FidryLazyCommand) {
            return new SymfonyLazyCommand($command::getName(), [], $command::getDescription(), \false, static fn() => new SymfonyCommand($command), \true);
        }
        return new SymfonyCommand($command);
    }
}
