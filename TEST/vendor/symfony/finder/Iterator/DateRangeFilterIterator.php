<?php

namespace _HumbugBox9658796bb9f0\Symfony\Component\Finder\Iterator;

use _HumbugBox9658796bb9f0\Symfony\Component\Finder\Comparator\DateComparator;
/**
@extends
*/
class DateRangeFilterIterator extends \FilterIterator
{
    private $comparators = [];
    public function __construct(\Iterator $iterator, array $comparators)
    {
        $this->comparators = $comparators;
        parent::__construct($iterator);
    }
    #[\ReturnTypeWillChange]
    public function accept()
    {
        $fileinfo = $this->current();
        if (!\file_exists($fileinfo->getPathname())) {
            return \false;
        }
        $filedate = $fileinfo->getMTime();
        foreach ($this->comparators as $compare) {
            if (!$compare->test($filedate)) {
                return \false;
            }
        }
        return \true;
    }
}
