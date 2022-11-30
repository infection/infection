<?php

namespace _HumbugBoxb47773b41c19\Symfony\Component\Console\Event;

final class ConsoleCommandEvent extends ConsoleEvent
{
    public const RETURN_CODE_DISABLED = 113;
    private bool $commandShouldRun = \true;
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
