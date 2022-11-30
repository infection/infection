<?php

namespace _HumbugBoxb47773b41c19\Amp\Parallel\Sync;

use _HumbugBoxb47773b41c19\Amp\Promise;
use _HumbugBoxb47773b41c19\Amp\Success;
use _HumbugBoxb47773b41c19\Amp\Sync\ThreadedMutex;
use function _HumbugBoxb47773b41c19\Amp\call;
final class ThreadedParcel implements Parcel
{
    private $mutex;
    private $storage;
    public function __construct($value)
    {
        $this->mutex = new ThreadedMutex();
        $this->storage = new Internal\ParcelStorage($value);
    }
    public function unwrap() : Promise
    {
        return new Success($this->storage->get());
    }
    public function synchronized(callable $callback) : Promise
    {
        return call(function () use($callback) : \Generator {
            $lock = (yield $this->mutex->acquire());
            try {
                $result = (yield call($callback, $this->storage->get()));
                if ($result !== null) {
                    $this->storage->set($result);
                }
            } finally {
                $lock->release();
            }
            return $result;
        });
    }
}
