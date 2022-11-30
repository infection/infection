<?php

namespace _HumbugBox9658796bb9f0\Symfony\Component\Console;

use _HumbugBox9658796bb9f0\Symfony\Component\Console\Command\Command;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Input\InputInterface;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Output\OutputInterface;
class SingleCommandApplication extends Command
{
    private $version = 'UNKNOWN';
    private $autoExit = \true;
    private $running = \false;
    public function setVersion(string $version) : self
    {
        $this->version = $version;
        return $this;
    }
    public function setAutoExit(bool $autoExit) : self
    {
        $this->autoExit = $autoExit;
        return $this;
    }
    public function run(InputInterface $input = null, OutputInterface $output = null) : int
    {
        if ($this->running) {
            return parent::run($input, $output);
        }
        $application = new Application($this->getName() ?: 'UNKNOWN', $this->version);
        $application->setAutoExit($this->autoExit);
        $this->setName($_SERVER['argv'][0]);
        $application->add($this);
        $application->setDefaultCommand($this->getName(), \true);
        $this->running = \true;
        try {
            $ret = $application->run($input, $output);
        } finally {
            $this->running = \false;
        }
        return $ret ?? 1;
    }
}
