<?php

namespace _HumbugBoxb47773b41c19\Symfony\Component\Console\Helper;

class TableRows implements \IteratorAggregate
{
    private \Closure $generator;
    public function __construct(\Closure $generator)
    {
        $this->generator = $generator;
    }
    public function getIterator() : \Traversable
    {
        return ($this->generator)();
    }
}
