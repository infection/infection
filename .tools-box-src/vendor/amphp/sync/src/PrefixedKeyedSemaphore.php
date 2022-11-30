<?php

namespace _HumbugBoxb47773b41c19\Amp\Sync;

use _HumbugBoxb47773b41c19\Amp\Promise;
final class PrefixedKeyedSemaphore implements KeyedSemaphore
{
    private $semaphore;
    private $prefix;
    public function __construct(KeyedSemaphore $semaphore, string $prefix)
    {
        $this->semaphore = $semaphore;
        $this->prefix = $prefix;
    }
    public function acquire(string $key) : Promise
    {
        return $this->semaphore->acquire($this->prefix . $key);
    }
}
