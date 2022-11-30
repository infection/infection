<?php

namespace _HumbugBox9658796bb9f0\Symfony\Component\Console\Helper;

class TableRows implements \IteratorAggregate
{
    private $generator;
    public function __construct(\Closure $generator)
    {
        $this->generator = $generator;
    }
    public function getIterator() : \Traversable
    {
        return ($this->generator)();
    }
}
