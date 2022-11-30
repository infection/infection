<?php

namespace _HumbugBox9658796bb9f0\Symfony\Component\Console\Event;

use _HumbugBox9658796bb9f0\Symfony\Component\Console\Command\Command;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Input\InputInterface;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Output\OutputInterface;
final class ConsoleErrorEvent extends ConsoleEvent
{
    private $error;
    private $exitCode;
    public function __construct(InputInterface $input, OutputInterface $output, \Throwable $error, Command $command = null)
    {
        parent::__construct($command, $input, $output);
        $this->error = $error;
    }
    public function getError() : \Throwable
    {
        return $this->error;
    }
    public function setError(\Throwable $error) : void
    {
        $this->error = $error;
    }
    public function setExitCode(int $exitCode) : void
    {
        $this->exitCode = $exitCode;
        $r = new \ReflectionProperty($this->error, 'code');
        $r->setAccessible(\true);
        $r->setValue($this->error, $this->exitCode);
    }
    public function getExitCode() : int
    {
        return $this->exitCode ?? (\is_int($this->error->getCode()) && 0 !== $this->error->getCode() ? $this->error->getCode() : 1);
    }
}
