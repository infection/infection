<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Fidry\Console\Application;

use _HumbugBoxb47773b41c19\Fidry\Console\Input\IO;
interface ConfigurableIO
{
    public function configureIO(IO $io) : void;
}
