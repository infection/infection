<?php

namespace _HumbugBoxb47773b41c19\Amp\Sync\ConcurrentIterator;

use _HumbugBoxb47773b41c19\Amp\CancelledException;
use _HumbugBoxb47773b41c19\Amp\Iterator;
use _HumbugBoxb47773b41c19\Amp\Producer;
use _HumbugBoxb47773b41c19\Amp\Promise;
use _HumbugBoxb47773b41c19\Amp\Sync\Barrier;
use _HumbugBoxb47773b41c19\Amp\Sync\Lock;
use _HumbugBoxb47773b41c19\Amp\Sync\Semaphore;
use function _HumbugBoxb47773b41c19\Amp\asyncCall;
use function _HumbugBoxb47773b41c19\Amp\call;
use function _HumbugBoxb47773b41c19\Amp\coroutine;
function transform(Iterator $iterator, Semaphore $semaphore, callable $processor) : Iterator
{
    return new Producer(static function (callable $emit) use($iterator, $semaphore, $processor) {
        $barrier = new Barrier(1);
        $error = null;
        $locks = [];
        $gc = \false;
        $processor = coroutine($processor);
        $processor = static function (Lock $lock, $currentElement) use($processor, $emit, $barrier, &$locks, &$error, &$gc) {
            $done = \false;
            try {
                (yield $processor($currentElement, $emit));
                $done = \true;
            } catch (\Throwable $e) {
                $error = $error ?? $e;
                $done = \true;
            } finally {
                if (!$done) {
                    $gc = \true;
                }
                unset($locks[$lock->getId()]);
                $lock->release();
                $barrier->arrive();
            }
        };
        while ((yield $iterator->advance())) {
            if ($error) {
                break;
            }
            $lock = (yield $semaphore->acquire());
            if ($gc || isset($locks[$lock->getId()])) {
                return;
            }
            $locks[$lock->getId()] = \true;
            $barrier->register();
            asyncCall($processor, $lock, $iterator->getCurrent());
        }
        $barrier->arrive();
        (yield $barrier->await());
        if ($error) {
            throw $error;
        }
    });
}
function map(Iterator $iterator, Semaphore $semaphore, callable $processor) : Iterator
{
    $processor = coroutine($processor);
    return transform($iterator, $semaphore, static function ($value, callable $emit) use($processor) {
        $value = (yield $processor($value));
        (yield $emit($value));
    });
}
function filter(Iterator $iterator, Semaphore $semaphore, callable $filter) : Iterator
{
    $filter = coroutine($filter);
    return transform($iterator, $semaphore, static function ($value, callable $emit) use($filter) {
        $keep = (yield $filter($value));
        if (!\is_bool($keep)) {
            throw new \TypeError(__NAMESPACE__ . '\\filter\'s callable must resolve to a boolean value, got ' . \gettype($keep));
        }
        if ($keep) {
            (yield $emit($value));
        }
    });
}
function each(Iterator $iterator, Semaphore $semaphore, callable $processor) : Promise
{
    $processor = coroutine($processor);
    $iterator = transform($iterator, $semaphore, static function ($value, callable $emit) use($processor) {
        (yield $processor($value));
        (yield $emit(null));
    });
    return call(static function () use($iterator) {
        $count = 0;
        while ((yield $iterator->advance())) {
            $count++;
        }
        return $count;
    });
}
