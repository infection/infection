<?php

namespace ParamCoverage;

class ArraySearcher extends AbstractSearcher
{
    private $items;

    public function __construct($items)
    {
        $this->items = $items;

    }

    public function search($value, bool $strict = false)
    {
        if (($return =  \array_search($value, $this->items, $strict)) === false) {
            throw new \Exception($value);
        }
        return $return;
    }
}
