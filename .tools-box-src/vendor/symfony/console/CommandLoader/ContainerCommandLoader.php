<?php

namespace _HumbugBoxb47773b41c19\Symfony\Component\Console\CommandLoader;

use _HumbugBoxb47773b41c19\Psr\Container\ContainerInterface;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Command\Command;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Exception\CommandNotFoundException;
class ContainerCommandLoader implements CommandLoaderInterface
{
    private ContainerInterface $container;
    private array $commandMap;
    public function __construct(ContainerInterface $container, array $commandMap)
    {
        $this->container = $container;
        $this->commandMap = $commandMap;
    }
    public function get(string $name) : Command
    {
        if (!$this->has($name)) {
            throw new CommandNotFoundException(\sprintf('Command "%s" does not exist.', $name));
        }
        return $this->container->get($this->commandMap[$name]);
    }
    public function has(string $name) : bool
    {
        return isset($this->commandMap[$name]) && $this->container->has($this->commandMap[$name]);
    }
    public function getNames() : array
    {
        return \array_keys($this->commandMap);
    }
}
