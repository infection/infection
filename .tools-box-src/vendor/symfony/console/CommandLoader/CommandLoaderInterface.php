<?php

namespace _HumbugBoxb47773b41c19\Symfony\Component\Console\CommandLoader;

use _HumbugBoxb47773b41c19\Symfony\Component\Console\Command\Command;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Exception\CommandNotFoundException;
interface CommandLoaderInterface
{
    public function get(string $name) : Command;
    public function has(string $name) : bool;
    public function getNames() : array;
}
