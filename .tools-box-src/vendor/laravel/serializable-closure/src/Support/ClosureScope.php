<?php

namespace _HumbugBoxb47773b41c19\Laravel\SerializableClosure\Support;

use SplObjectStorage;
class ClosureScope extends SplObjectStorage
{
    public $serializations = 0;
    public $toSerialize = 0;
}
