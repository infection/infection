<?php

namespace _HumbugBoxb47773b41c19\Amp\Sync;

use _HumbugBoxb47773b41c19\Amp\CallableMaker;
use _HumbugBoxb47773b41c19\Amp\Deferred;
use _HumbugBoxb47773b41c19\Amp\Promise;
use _HumbugBoxb47773b41c19\Amp\Success;
class LocalMutex implements Mutex
{
    use CallableMaker;
    private $locked = \false;
    private $queue = [];
    public function acquire() : Promise
    {
        if (!$this->locked) {
            $this->locked = \true;
            return new Success(new Lock(0, \Closure::fromCallable([$this, 'release'])));
        }
        $this->queue[] = $deferred = new Deferred();
        return $deferred->promise();
    }
    private function release() : void
    {
        if (!empty($this->queue)) {
            $deferred = \array_shift($this->queue);
            $deferred->resolve(new Lock(0, \Closure::fromCallable([$this, 'release'])));
            return;
        }
        $this->locked = \false;
    }
}
