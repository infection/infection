<?php

namespace _HumbugBox9658796bb9f0\Symfony\Component\Console\Helper;

use _HumbugBox9658796bb9f0\Symfony\Component\Console\Command\Command;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Exception\InvalidArgumentException;
/**
@implements
*/
class HelperSet implements \IteratorAggregate
{
    private $helpers = [];
    private $command;
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
    public function has(string $name)
    {
        return isset($this->helpers[$name]);
    }
    public function get(string $name)
    {
        if (!$this->has($name)) {
            throw new InvalidArgumentException(\sprintf('The helper "%s" is not defined.', $name));
        }
        return $this->helpers[$name];
    }
    public function setCommand(Command $command = null)
    {
        trigger_deprecation('symfony/console', '5.4', 'Method "%s()" is deprecated.', __METHOD__);
        $this->command = $command;
    }
    public function getCommand()
    {
        trigger_deprecation('symfony/console', '5.4', 'Method "%s()" is deprecated.', __METHOD__);
        return $this->command;
    }
    #[\ReturnTypeWillChange]
    public function getIterator()
    {
        return new \ArrayIterator($this->helpers);
    }
}
