<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Fidry\Console\Command;

use _HumbugBoxb47773b41c19\Symfony\Component\Console\Application;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Exception\CommandNotFoundException;
final class CommandRegistry
{
    private Application $application;
    public function __construct(Application $application)
    {
        $this->application = $application;
    }
    public function getCommand(string $name) : Command
    {
        return new ReversedSymfonyCommand($this->application->get($name));
    }
    public function findCommand(string $name) : Command
    {
        return new ReversedSymfonyCommand($this->application->find($name));
    }
}
