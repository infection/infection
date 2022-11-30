<?php

namespace _HumbugBoxb47773b41c19\Amp\Sync;

use _HumbugBoxb47773b41c19\Amp\Promise;
final class PrefixedKeyedMutex implements KeyedMutex
{
    private $mutex;
    private $prefix;
    public function __construct(KeyedMutex $mutex, string $prefix)
    {
        $this->mutex = $mutex;
        $this->prefix = $prefix;
    }
    public function acquire(string $key) : Promise
    {
        return $this->mutex->acquire($this->prefix . $key);
    }
}
