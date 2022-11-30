<?php

namespace _HumbugBoxb47773b41c19\Amp\Parallel\Sync\Internal;

final class ParcelStorage extends \Threaded
{
    private $value;
    public function __construct($value)
    {
        $this->value = $value;
    }
    public function get()
    {
        return $this->value;
    }
    public function set($value) : void
    {
        $this->value = $value;
    }
}
