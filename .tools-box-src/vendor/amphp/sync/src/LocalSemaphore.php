<?php

namespace _HumbugBoxb47773b41c19\Amp\Sync;

use _HumbugBoxb47773b41c19\Amp\CallableMaker;
use _HumbugBoxb47773b41c19\Amp\Deferred;
use _HumbugBoxb47773b41c19\Amp\Promise;
use _HumbugBoxb47773b41c19\Amp\Success;
class LocalSemaphore implements Semaphore
{
    use CallableMaker;
    private $locks;
    private $queue = [];
    public function __construct(int $maxLocks)
    {
        if ($maxLocks < 1) {
            throw new \Error('The number of locks must be greater than 0');
        }
        $this->locks = \range(0, $maxLocks - 1);
    }
    public function acquire() : Promise
    {
        if (!empty($this->locks)) {
            return new Success(new Lock(\array_shift($this->locks), \Closure::fromCallable([$this, 'release'])));
        }
        $this->queue[] = $deferred = new Deferred();
        return $deferred->promise();
    }
    private function release(Lock $lock) : void
    {
        $id = $lock->getId();
        if (!empty($this->queue)) {
            $deferred = \array_shift($this->queue);
            $deferred->resolve(new Lock($id, \Closure::fromCallable([$this, 'release'])));
            return;
        }
        $this->locks[] = $id;
    }
}
