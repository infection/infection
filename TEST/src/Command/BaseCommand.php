<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Command;

use _HumbugBox9658796bb9f0\Infection\Console\Application;
use _HumbugBox9658796bb9f0\Infection\Console\IO;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Command\Command;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Input\InputInterface;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Output\OutputInterface;
use _HumbugBox9658796bb9f0\Webmozart\Assert\Assert;
abstract class BaseCommand extends Command
{
    private ?IO $io = null;
    public final function getApplication() : Application
    {
        $application = parent::getApplication();
        Assert::isInstanceOf($application, Application::class, 'Cannot access to the command application if the command has not been ' . 'registered to the application yet');
        return $application;
    }
    protected function initialize(InputInterface $input, OutputInterface $output) : void
    {
        parent::initialize($input, $output);
        $this->io = new IO($input, $output);
    }
    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        return $this->executeCommand($this->getIO()) ? 0 : 1;
    }
    protected abstract function executeCommand(IO $io) : bool;
    protected final function getIO() : IO
    {
        Assert::notNull($this->io, 'Cannot retrieve the IO object before the command was initialized');
        return $this->io;
    }
}
