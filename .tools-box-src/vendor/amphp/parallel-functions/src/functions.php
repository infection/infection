<?php

namespace _HumbugBoxb47773b41c19\Amp\ParallelFunctions;

use _HumbugBoxb47773b41c19\Amp\MultiReasonException;
use _HumbugBoxb47773b41c19\Amp\Parallel\Worker\Pool;
use _HumbugBoxb47773b41c19\Amp\Promise;
use _HumbugBoxb47773b41c19\Amp\Serialization\SerializationException;
use _HumbugBoxb47773b41c19\Laravel\SerializableClosure\SerializableClosure;
use function _HumbugBoxb47773b41c19\Amp\call;
use function _HumbugBoxb47773b41c19\Amp\Parallel\Worker\enqueue;
use function _HumbugBoxb47773b41c19\Amp\Promise\any;
function parallel(callable $callable, Pool $pool = null) : callable
{
    if ($callable instanceof \Closure) {
        $callable = new SerializableClosure($callable);
    }
    try {
        $callable = \serialize($callable);
    } catch (\Throwable $e) {
        throw new SerializationException("Unsupported callable: " . $e->getMessage(), 0, $e);
    }
    return function (...$args) use($pool, $callable) : Promise {
        $task = new Internal\SerializedCallableTask($callable, $args);
        return $pool ? $pool->enqueue($task) : enqueue($task);
    };
}
function parallelMap(array $array, callable $callable, Pool $pool = null) : Promise
{
    return call(function () use($array, $callable, $pool) {
        [$errors, $results] = (yield any(\array_map(parallel($callable, $pool), $array)));
        if ($errors) {
            throw new MultiReasonException($errors);
        }
        return $results;
    });
}
function parallelFilter(array $array, callable $callable = null, int $flag = 0, Pool $pool = null) : Promise
{
    return call(function () use($array, $callable, $flag, $pool) {
        if ($callable === null) {
            if ($flag === \ARRAY_FILTER_USE_BOTH || $flag === \ARRAY_FILTER_USE_KEY) {
                throw new \Error('A valid $callable must be provided if $flag is set.');
            }
            $callable = function ($value) {
                return (bool) $value;
            };
        }
        if ($flag === \ARRAY_FILTER_USE_BOTH) {
            [$errors, $results] = (yield any(\array_map(parallel($callable, $pool), $array, \array_keys($array))));
        } elseif ($flag === \ARRAY_FILTER_USE_KEY) {
            [$errors, $results] = (yield any(\array_map(parallel($callable, $pool), \array_keys($array))));
        } else {
            [$errors, $results] = (yield any(\array_map(parallel($callable, $pool), $array)));
        }
        if ($errors) {
            throw new MultiReasonException($errors);
        }
        foreach ($array as $key => $arg) {
            if (!$results[$key]) {
                unset($array[$key]);
            }
        }
        return $array;
    });
}
