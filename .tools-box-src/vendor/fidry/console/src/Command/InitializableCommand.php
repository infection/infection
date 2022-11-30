<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Fidry\Console\Command;

use _HumbugBoxb47773b41c19\Fidry\Console\Input\IO;
interface InitializableCommand extends Command
{
    public function initialize(IO $io) : void;
}
