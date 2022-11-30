<?php

namespace _HumbugBoxb47773b41c19\Amp\Sync;

use _HumbugBoxb47773b41c19\Amp\Promise;
use function _HumbugBoxb47773b41c19\Amp\call;
function synchronized(Mutex $mutex, callable $callback, ...$args) : Promise
{
    return call(static function () use($mutex, $callback, $args) : \Generator {
        $lock = (yield $mutex->acquire());
        try {
            return (yield call($callback, ...$args));
        } finally {
            $lock->release();
        }
    });
}
