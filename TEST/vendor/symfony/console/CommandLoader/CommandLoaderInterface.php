<?php

namespace _HumbugBox9658796bb9f0\Symfony\Component\Console\CommandLoader;

use _HumbugBox9658796bb9f0\Symfony\Component\Console\Command\Command;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Exception\CommandNotFoundException;
interface CommandLoaderInterface
{
    public function get(string $name);
    public function has(string $name);
    public function getNames();
}
