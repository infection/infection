<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Fidry\Console\Command;

interface CommandAware extends Command
{
    public function setCommandRegistry(CommandRegistry $commandRegistry) : void;
}
