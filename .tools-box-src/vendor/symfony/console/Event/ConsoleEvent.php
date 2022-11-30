<?php

namespace _HumbugBoxb47773b41c19\Symfony\Component\Console\Event;

use _HumbugBoxb47773b41c19\Symfony\Component\Console\Command\Command;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Input\InputInterface;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Output\OutputInterface;
use _HumbugBoxb47773b41c19\Symfony\Contracts\EventDispatcher\Event;
class ConsoleEvent extends Event
{
    protected $command;
    private InputInterface $input;
    private OutputInterface $output;
    public function __construct(?Command $command, InputInterface $input, OutputInterface $output)
    {
        $this->command = $command;
        $this->input = $input;
        $this->output = $output;
    }
    public function getCommand() : ?Command
    {
        return $this->command;
    }
    public function getInput() : InputInterface
    {
        return $this->input;
    }
    public function getOutput() : OutputInterface
    {
        return $this->output;
    }
}
