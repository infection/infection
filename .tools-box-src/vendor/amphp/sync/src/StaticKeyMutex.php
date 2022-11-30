<?php

namespace _HumbugBoxb47773b41c19\Amp\Sync;

use _HumbugBoxb47773b41c19\Amp\Promise;
final class StaticKeyMutex implements Mutex
{
    private $mutex;
    private $key;
    public function __construct(KeyedMutex $mutex, string $key)
    {
        $this->mutex = $mutex;
        $this->key = $key;
    }
    public function acquire() : Promise
    {
        return $this->mutex->acquire($this->key);
    }
}
