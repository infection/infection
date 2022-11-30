<?php

namespace _HumbugBoxb47773b41c19\Amp\Parallel\Worker;

use _HumbugBoxb47773b41c19\Amp\Loop;
use _HumbugBoxb47773b41c19\Amp\Promise;
const LOOP_POOL_IDENTIFIER = Pool::class;
const LOOP_FACTORY_IDENTIFIER = WorkerFactory::class;
function pool(Pool $pool = null) : Pool
{
    if ($pool === null) {
        $pool = Loop::getState(LOOP_POOL_IDENTIFIER);
        if ($pool) {
            return $pool;
        }
        $pool = new DefaultPool();
    }
    Loop::setState(LOOP_POOL_IDENTIFIER, $pool);
    return $pool;
}
function enqueue(Task $task) : Promise
{
    return pool()->enqueue($task);
}
function enqueueCallable(callable $callable, ...$args)
{
    return enqueue(new CallableTask($callable, $args));
}
function worker() : Worker
{
    return pool()->getWorker();
}
function create() : Worker
{
    return factory()->create();
}
function factory(WorkerFactory $factory = null) : WorkerFactory
{
    if ($factory === null) {
        $factory = Loop::getState(LOOP_FACTORY_IDENTIFIER);
        if ($factory) {
            return $factory;
        }
        $factory = new DefaultWorkerFactory();
    }
    Loop::setState(LOOP_FACTORY_IDENTIFIER, $factory);
    return $factory;
}
