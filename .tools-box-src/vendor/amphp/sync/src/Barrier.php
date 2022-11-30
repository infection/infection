<?php

namespace _HumbugBoxb47773b41c19\Amp\Sync;

use _HumbugBoxb47773b41c19\Amp\Deferred;
use _HumbugBoxb47773b41c19\Amp\Promise;
final class Barrier
{
    private $count;
    private $deferred;
    public function __construct(int $count)
    {
        if ($count < 1) {
            throw new \Error('Count must be positive, got ' . $count);
        }
        $this->count = $count;
        $this->deferred = new Deferred();
    }
    public function getCount() : int
    {
        return $this->count;
    }
    public function arrive(int $count = 1) : void
    {
        if ($count < 1) {
            throw new \Error('Count must be at least 1, got ' . $count);
        }
        if ($count > $this->count) {
            throw new \Error('Count cannot be greater than remaining count: ' . $count . ' > ' . $this->count);
        }
        $this->count -= $count;
        if ($this->count === 0) {
            $this->deferred->resolve();
        }
    }
    public function register(int $count = 1) : void
    {
        if ($count < 1) {
            throw new \Error('Count must be at least 1, got ' . $count);
        }
        if ($this->count === 0) {
            throw new \Error('Can\'t increase count, because the barrier already broke');
        }
        $this->count += $count;
    }
    public function await() : Promise
    {
        return $this->deferred->promise();
    }
}
