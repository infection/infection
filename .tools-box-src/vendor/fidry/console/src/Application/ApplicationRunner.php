<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Fidry\Console\Application;

use _HumbugBoxb47773b41c19\Fidry\Console\Input\IO;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Input\ArgvInput;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Input\InputInterface;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Output\ConsoleOutput;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Output\OutputInterface;
final class ApplicationRunner
{
    private SymfonyApplication $application;
    public function __construct(Application $application)
    {
        $this->application = new SymfonyApplication($application);
    }
    /**
    @psalm-suppress
    */
    public static function runApplication(Application $application, ?InputInterface $input = null, ?OutputInterface $output = null) : int
    {
        return (new self($application))->run(new IO($input ?? new ArgvInput(), $output ?? new ConsoleOutput()));
    }
    public function run(?IO $io = null) : int
    {
        if (null === $io) {
            $input = null;
            $output = null;
        } else {
            $input = $io->getInput();
            $output = $io->getOutput();
        }
        return $this->application->run($input, $output);
    }
}
