<?php

namespace _HumbugBoxb47773b41c19\Symfony\Component\Console\Helper;

use _HumbugBoxb47773b41c19\Symfony\Component\Console\Exception\InvalidArgumentException;
/**
@implements
*/
class HelperSet implements \IteratorAggregate
{
    private array $helpers = [];
    public function __construct(array $helpers = [])
    {
        foreach ($helpers as $alias => $helper) {
            $this->set($helper, \is_int($alias) ? null : $alias);
        }
    }
    public function set(HelperInterface $helper, string $alias = null)
    {
        $this->helpers[$helper->getName()] = $helper;
        if (null !== $alias) {
            $this->helpers[$alias] = $helper;
        }
        $helper->setHelperSet($this);
    }
    public function has(string $name) : bool
    {
        return isset($this->helpers[$name]);
    }
    public function get(string $name) : HelperInterface
    {
        if (!$this->has($name)) {
            throw new InvalidArgumentException(\sprintf('The helper "%s" is not defined.', $name));
        }
        return $this->helpers[$name];
    }
    public function getIterator() : \Traversable
    {
        return new \ArrayIterator($this->helpers);
    }
}
