<?php

namespace _HumbugBoxb47773b41c19\Amp\Sync;

use _HumbugBoxb47773b41c19\Amp\Promise;
use function _HumbugBoxb47773b41c19\Amp\call;
final class LocalKeyedSemaphore implements KeyedSemaphore
{
    private $semaphore = [];
    private $locks = [];
    private $maxLocks;
    public function __construct(int $maxLocks)
    {
        $this->maxLocks = $maxLocks;
    }
    public function acquire(string $key) : Promise
    {
        if (!isset($this->semaphore[$key])) {
            $this->semaphore[$key] = new LocalSemaphore($this->maxLocks);
            $this->locks[$key] = 0;
        }
        return call(function () use($key) {
            $this->locks[$key]++;
            $lock = (yield $this->semaphore[$key]->acquire());
            return new Lock(0, function () use($lock, $key) {
                if (--$this->locks[$key] === 0) {
                    unset($this->semaphore[$key], $this->locks[$key]);
                }
                $lock->release();
            });
        });
    }
}
