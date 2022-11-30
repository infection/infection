<?php

namespace _HumbugBox9658796bb9f0\Symfony\Component\Console\Event;

use _HumbugBox9658796bb9f0\Symfony\Component\Console\Command\Command;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Input\InputInterface;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Output\OutputInterface;
use _HumbugBox9658796bb9f0\Symfony\Contracts\EventDispatcher\Event;
class ConsoleEvent extends Event
{
    protected $command;
    private $input;
    private $output;
    public function __construct(?Command $command, InputInterface $input, OutputInterface $output)
    {
        $this->command = $command;
        $this->input = $input;
        $this->output = $output;
    }
    public function getCommand()
    {
        return $this->command;
    }
    public function getInput()
    {
        return $this->input;
    }
    public function getOutput()
    {
        return $this->output;
    }
}
