<?php

namespace _HumbugBoxb47773b41c19\Amp\Sync;

use _HumbugBoxb47773b41c19\Amp\Promise;
use function _HumbugBoxb47773b41c19\Amp\call;
final class LocalKeyedMutex implements KeyedMutex
{
    private $mutex = [];
    private $locks = [];
    public function acquire(string $key) : Promise
    {
        if (!isset($this->mutex[$key])) {
            $this->mutex[$key] = new LocalMutex();
            $this->locks[$key] = 0;
        }
        return call(function () use($key) {
            $this->locks[$key]++;
            $lock = (yield $this->mutex[$key]->acquire());
            return new Lock(0, function () use($lock, $key) {
                if (--$this->locks[$key] === 0) {
                    unset($this->mutex[$key], $this->locks[$key]);
                }
                $lock->release();
            });
        });
    }
}
