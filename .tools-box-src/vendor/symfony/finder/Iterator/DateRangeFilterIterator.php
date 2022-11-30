<?php

namespace _HumbugBoxb47773b41c19\Symfony\Component\Finder\Iterator;

use _HumbugBoxb47773b41c19\Symfony\Component\Finder\Comparator\DateComparator;
/**
@extends
*/
class DateRangeFilterIterator extends \FilterIterator
{
    private array $comparators = [];
    public function __construct(\Iterator $iterator, array $comparators)
    {
        $this->comparators = $comparators;
        parent::__construct($iterator);
    }
    public function accept() : bool
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
