<?php

namespace _HumbugBox9658796bb9f0\Symfony\Component\Console\CommandLoader;

use _HumbugBox9658796bb9f0\Psr\Container\ContainerInterface;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Exception\CommandNotFoundException;
class ContainerCommandLoader implements CommandLoaderInterface
{
    private $container;
    private $commandMap;
    public function __construct(ContainerInterface $container, array $commandMap)
    {
        $this->container = $container;
        $this->commandMap = $commandMap;
    }
    public function get(string $name)
    {
        if (!$this->has($name)) {
            throw new CommandNotFoundException(\sprintf('Command "%s" does not exist.', $name));
        }
        return $this->container->get($this->commandMap[$name]);
    }
    public function has(string $name)
    {
        return isset($this->commandMap[$name]) && $this->container->has($this->commandMap[$name]);
    }
    public function getNames()
    {
        return \array_keys($this->commandMap);
    }
}
