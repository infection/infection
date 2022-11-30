<?php

namespace _HumbugBoxb47773b41c19\Amp\Sync;

use _HumbugBoxb47773b41c19\Amp\Promise;
class ThreadedSemaphore implements Semaphore
{
    private $semaphore;
    public function __construct(int $locks)
    {
        if ($locks < 1) {
            throw new \Error("The number of locks should be a positive integer");
        }
        $this->semaphore = new Internal\SemaphoreStorage($locks);
    }
    public function acquire() : Promise
    {
        return $this->semaphore->acquire();
    }
}
