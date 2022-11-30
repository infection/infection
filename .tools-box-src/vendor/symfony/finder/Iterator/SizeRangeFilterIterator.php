<?php

namespace _HumbugBoxb47773b41c19\Symfony\Component\Finder\Iterator;

use _HumbugBoxb47773b41c19\Symfony\Component\Finder\Comparator\NumberComparator;
/**
@extends
*/
class SizeRangeFilterIterator extends \FilterIterator
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
        if (!$fileinfo->isFile()) {
            return \true;
        }
        $filesize = $fileinfo->getSize();
        foreach ($this->comparators as $compare) {
            if (!$compare->test($filesize)) {
                return \false;
            }
        }
        return \true;
    }
}
