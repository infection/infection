<?php

namespace _HumbugBoxb47773b41c19\Laravel\SerializableClosure\Support;

class SelfReference
{
    public $hash;
    public function __construct($hash)
    {
        $this->hash = $hash;
    }
}
