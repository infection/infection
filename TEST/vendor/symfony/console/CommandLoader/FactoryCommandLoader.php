<?php

namespace _HumbugBox9658796bb9f0\Symfony\Component\Console\CommandLoader;

use _HumbugBox9658796bb9f0\Symfony\Component\Console\Exception\CommandNotFoundException;
class FactoryCommandLoader implements CommandLoaderInterface
{
    private $factories;
    public function __construct(array $factories)
    {
        $this->factories = $factories;
    }
    public function has(string $name)
    {
        return isset($this->factories[$name]);
    }
    public function get(string $name)
    {
        if (!isset($this->factories[$name])) {
            throw new CommandNotFoundException(\sprintf('Command "%s" does not exist.', $name));
        }
        $factory = $this->factories[$name];
        return $factory();
    }
    public function getNames()
    {
        return \array_keys($this->factories);
    }
}
