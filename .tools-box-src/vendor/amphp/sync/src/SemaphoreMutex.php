<?php

namespace _HumbugBoxb47773b41c19\Amp\Sync;

use _HumbugBoxb47773b41c19\Amp\Promise;
use function _HumbugBoxb47773b41c19\Amp\call;
class SemaphoreMutex implements Mutex
{
    private $semaphore;
    public function __construct(Semaphore $semaphore)
    {
        $this->semaphore = $semaphore;
    }
    public function acquire() : Promise
    {
        return call(function () : \Generator {
            $lock = (yield $this->semaphore->acquire());
            if ($lock->getId() !== 0) {
                $lock->release();
                throw new \Error("Cannot use a semaphore with more than a single lock");
            }
            return $lock;
        });
    }
}
