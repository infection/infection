<?php

namespace _HumbugBox9658796bb9f0\Symfony\Component\Finder\Iterator;

class LazyIterator implements \IteratorAggregate
{
    private $iteratorFactory;
    public function __construct(callable $iteratorFactory)
    {
        $this->iteratorFactory = $iteratorFactory;
    }
    public function getIterator() : \Traversable
    {
        yield from ($this->iteratorFactory)();
    }
}
