<?php

namespace _HumbugBox9658796bb9f0\Symfony\Component\Finder\Iterator;

use _HumbugBox9658796bb9f0\Symfony\Component\Finder\Comparator\NumberComparator;
/**
@extends
*/
class SizeRangeFilterIterator extends \FilterIterator
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
