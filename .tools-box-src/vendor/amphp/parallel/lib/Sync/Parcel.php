<?php

namespace _HumbugBoxb47773b41c19\Amp\Parallel\Sync;

use _HumbugBoxb47773b41c19\Amp\Promise;
interface Parcel
{
    public function synchronized(callable $callback) : Promise;
    public function unwrap() : Promise;
}
