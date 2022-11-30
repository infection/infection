<?php

namespace _HumbugBoxb47773b41c19\Symfony\Component\Finder\Iterator;

/**
@template-covariant
@template-covariant
@extends
*/
class DepthRangeFilterIterator extends \FilterIterator
{
    private int $minDepth = 0;
    public function __construct(\RecursiveIteratorIterator $iterator, int $minDepth = 0, int $maxDepth = \PHP_INT_MAX)
    {
        $this->minDepth = $minDepth;
        $iterator->setMaxDepth(\PHP_INT_MAX === $maxDepth ? -1 : $maxDepth);
        parent::__construct($iterator);
    }
    public function accept() : bool
    {
        return $this->getInnerIterator()->getDepth() >= $this->minDepth;
    }
}
