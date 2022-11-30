<?php

namespace _HumbugBoxb47773b41c19\Amp\Sync;

use _HumbugBoxb47773b41c19\Amp\Promise;
interface Mutex extends Semaphore
{
    public function acquire() : Promise;
}
