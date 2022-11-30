<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Fidry\Console\Command;

use _HumbugBoxb47773b41c19\Fidry\Console\Input\IO;
interface InteractiveCommand extends Command
{
    public function interact(IO $io) : void;
}
