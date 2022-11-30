<?php

namespace _HumbugBoxb47773b41c19\Amp\Sync;

use _HumbugBoxb47773b41c19\Amp\Promise;
class ThreadedMutex implements Mutex
{
    private $mutex;
    public function __construct()
    {
        $this->mutex = new Internal\MutexStorage();
    }
    public function acquire() : Promise
    {
        return $this->mutex->acquire();
    }
}
