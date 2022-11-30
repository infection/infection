<?php

namespace _HumbugBoxb47773b41c19\Symfony\Component\Console\Event;

use _HumbugBoxb47773b41c19\Symfony\Component\Console\Command\Command;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Input\InputInterface;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Output\OutputInterface;
final class ConsoleSignalEvent extends ConsoleEvent
{
    private int $handlingSignal;
    public function __construct(Command $command, InputInterface $input, OutputInterface $output, int $handlingSignal)
    {
        parent::__construct($command, $input, $output);
        $this->handlingSignal = $handlingSignal;
    }
    public function getHandlingSignal() : int
    {
        return $this->handlingSignal;
    }
}
