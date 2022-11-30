<?php

namespace _HumbugBox9658796bb9f0\Symfony\Component\Console\Event;

final class ConsoleCommandEvent extends ConsoleEvent
{
    public const RETURN_CODE_DISABLED = 113;
    private $commandShouldRun = \true;
    public function disableCommand() : bool
    {
        return $this->commandShouldRun = \false;
    }
    public function enableCommand() : bool
    {
        return $this->commandShouldRun = \true;
    }
    public function commandShouldRun() : bool
    {
        return $this->commandShouldRun;
    }
}
