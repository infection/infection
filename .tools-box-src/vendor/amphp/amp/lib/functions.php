<?php

namespace _HumbugBoxb47773b41c19\Amp;

use _HumbugBoxb47773b41c19\React\Promise\PromiseInterface as ReactPromise;
/**
@template
@template
@template
@template
@template
@template
@formatter:off
@psalm-return(T is Promise ? (callable(mixed...): Promise<TPromise>) : (T is \Generator ? (TGenerator is Promise ? (callable(mixed...): Promise<TGeneratorPromise>) : (callable(mixed...): Promise<TGeneratorReturn>)) : (callable(mixed...): Promise<TReturn>)))
@formatter:on
@psalm-suppress
*/
function coroutine(callable $callback) : callable
{
    /**
    @psalm-suppress */
    return static function (...$args) use($callback) : Promise {
        return call($callback, ...$args);
    };
}
/**
@psalm-return
*/
function asyncCoroutine(callable $callback) : callable
{
    return static function (...$args) use($callback) {
        Promise\rethrow(call($callback, ...$args));
    };
}
/**
@template
@template
@template
@template
@template
@template
@formatter:off
@psalm-return(T is Promise ? Promise<TPromise> : (T is \Generator ? (TGenerator is Promise ? Promise<TGeneratorPromise> : Promise<TGeneratorReturn>) : Promise<TReturn>))
@formatter:on
*/
function call(callable $callback, ...$args) : Promise
{
    try {
        $result = $callback(...$args);
    } catch (\Throwable $exception) {
        return new Failure($exception);
    }
    if ($result instanceof \Generator) {
        return new Coroutine($result);
    }
    if ($result instanceof Promise) {
        return $result;
    }
    if ($result instanceof ReactPromise) {
        return Promise\adapt($result);
    }
    return new Success($result);
}
function asyncCall(callable $callback, ...$args)
{
    Promise\rethrow(call($callback, ...$args));
}
function delay(int $milliseconds) : Delayed
{
    return new Delayed($milliseconds);
}
function getCurrentTime() : int
{
    return Internal\getCurrentTime();
}
namespace _HumbugBoxb47773b41c19\Amp\Promise;

use _HumbugBoxb47773b41c19\Amp\Deferred;
use _HumbugBoxb47773b41c19\Amp\Loop;
use _HumbugBoxb47773b41c19\Amp\MultiReasonException;
use _HumbugBoxb47773b41c19\Amp\Promise;
use _HumbugBoxb47773b41c19\Amp\Success;
use _HumbugBoxb47773b41c19\Amp\TimeoutException;
use _HumbugBoxb47773b41c19\React\Promise\PromiseInterface as ReactPromise;
use function _HumbugBoxb47773b41c19\Amp\call;
use function _HumbugBoxb47773b41c19\Amp\Internal\createTypeError;
function rethrow($promise)
{
    if (!$promise instanceof Promise) {
        if ($promise instanceof ReactPromise) {
            $promise = adapt($promise);
        } else {
            throw createTypeError([Promise::class, ReactPromise::class], $promise);
        }
    }
    $promise->onResolve(static function ($exception) {
        if ($exception) {
            throw $exception;
        }
    });
}
/**
@template
@template
@psalm-param
@psalm-return(T is Promise ? TPromise : mixed)
*/
function wait($promise)
{
    if (!$promise instanceof Promise) {
        if ($promise instanceof ReactPromise) {
            $promise = adapt($promise);
        } else {
            throw createTypeError([Promise::class, ReactPromise::class], $promise);
        }
    }
    $resolved = \false;
    try {
        Loop::run(function () use(&$resolved, &$value, &$exception, $promise) {
            $promise->onResolve(function ($e, $v) use(&$resolved, &$value, &$exception) {
                Loop::stop();
                $resolved = \true;
                $exception = $e;
                $value = $v;
            });
        });
    } catch (\Throwable $throwable) {
        throw new \Error("Loop exceptionally stopped without resolving the promise", 0, $throwable);
    }
    if (!$resolved) {
        throw new \Error("Loop stopped without resolving the promise");
    }
    if ($exception) {
        throw $exception;
    }
    return $value;
}
/**
@template
*/
function timeout($promise, int $timeout) : Promise
{
    if (!$promise instanceof Promise) {
        if ($promise instanceof ReactPromise) {
            $promise = adapt($promise);
        } else {
            throw createTypeError([Promise::class, ReactPromise::class], $promise);
        }
    }
    $deferred = new Deferred();
    $watcher = Loop::delay($timeout, static function () use(&$deferred) {
        $temp = $deferred;
        $deferred = null;
        $temp->fail(new TimeoutException());
    });
    Loop::unreference($watcher);
    $promise->onResolve(function () use(&$deferred, $promise, $watcher) {
        if ($deferred !== null) {
            Loop::cancel($watcher);
            $deferred->resolve($promise);
        }
    });
    return $deferred->promise();
}
/**
@template
*/
function timeoutWithDefault($promise, int $timeout, $default = null) : Promise
{
    $promise = timeout($promise, $timeout);
    return call(static function () use($promise, $default) {
        try {
            return (yield $promise);
        } catch (TimeoutException $exception) {
            return $default;
        }
    });
}
function adapt($promise) : Promise
{
    if (!\is_object($promise)) {
        throw new \Error("Object must be provided");
    }
    $deferred = new Deferred();
    if (\method_exists($promise, 'done')) {
        $promise->done([$deferred, 'resolve'], [$deferred, 'fail']);
    } elseif (\method_exists($promise, 'then')) {
        $promise->then([$deferred, 'resolve'], [$deferred, 'fail']);
    } else {
        throw new \Error("Object must have a 'then' or 'done' method");
    }
    return $deferred->promise();
}
/**
@template
*/
function any(array $promises) : Promise
{
    return some($promises, 0);
}
/**
@template
@psalm-param
@psalm-assert
@psalm-return
*/
function all(array $promises) : Promise
{
    if (empty($promises)) {
        return new Success([]);
    }
    $deferred = new Deferred();
    $result = $deferred->promise();
    $pending = \count($promises);
    $values = [];
    foreach ($promises as $key => $promise) {
        if ($promise instanceof ReactPromise) {
            $promise = adapt($promise);
        } elseif (!$promise instanceof Promise) {
            throw createTypeError([Promise::class, ReactPromise::class], $promise);
        }
        $values[$key] = null;
        $promise->onResolve(function ($exception, $value) use(&$deferred, &$values, &$pending, $key) {
            if ($pending === 0) {
                return;
            }
            if ($exception) {
                $pending = 0;
                $deferred->fail($exception);
                $deferred = null;
                return;
            }
            $values[$key] = $value;
            if (0 === --$pending) {
                $deferred->resolve($values);
            }
        });
    }
    return $result;
}
/**
@template
*/
function first(array $promises) : Promise
{
    if (empty($promises)) {
        throw new \Error("No promises provided");
    }
    $deferred = new Deferred();
    $result = $deferred->promise();
    $pending = \count($promises);
    $exceptions = [];
    foreach ($promises as $key => $promise) {
        if ($promise instanceof ReactPromise) {
            $promise = adapt($promise);
        } elseif (!$promise instanceof Promise) {
            throw createTypeError([Promise::class, ReactPromise::class], $promise);
        }
        $exceptions[$key] = null;
        $promise->onResolve(function ($error, $value) use(&$deferred, &$exceptions, &$pending, $key) {
            if ($pending === 0) {
                return;
            }
            if (!$error) {
                $pending = 0;
                $deferred->resolve($value);
                $deferred = null;
                return;
            }
            $exceptions[$key] = $error;
            if (0 === --$pending) {
                $deferred->fail(new MultiReasonException($exceptions));
            }
        });
    }
    return $result;
}
/**
@template
*/
function some(array $promises, int $required = 1) : Promise
{
    if ($required < 0) {
        throw new \Error("Number of promises required must be non-negative");
    }
    $pending = \count($promises);
    if ($required > $pending) {
        throw new \Error("Too few promises provided");
    }
    if (empty($promises)) {
        return new Success([[], []]);
    }
    $deferred = new Deferred();
    $result = $deferred->promise();
    $values = [];
    $exceptions = [];
    foreach ($promises as $key => $promise) {
        if ($promise instanceof ReactPromise) {
            $promise = adapt($promise);
        } elseif (!$promise instanceof Promise) {
            throw createTypeError([Promise::class, ReactPromise::class], $promise);
        }
        $values[$key] = $exceptions[$key] = null;
        $promise->onResolve(static function ($exception, $value) use(&$values, &$exceptions, &$pending, $key, $required, $deferred) {
            if ($exception) {
                $exceptions[$key] = $exception;
                unset($values[$key]);
            } else {
                $values[$key] = $value;
                unset($exceptions[$key]);
            }
            if (0 === --$pending) {
                if (\count($values) < $required) {
                    $deferred->fail(new MultiReasonException($exceptions));
                } else {
                    $deferred->resolve([$exceptions, $values]);
                }
            }
        });
    }
    return $result;
}
function wrap($promise, callable $callback) : Promise
{
    if ($promise instanceof ReactPromise) {
        $promise = adapt($promise);
    } elseif (!$promise instanceof Promise) {
        throw createTypeError([Promise::class, ReactPromise::class], $promise);
    }
    $deferred = new Deferred();
    $promise->onResolve(static function (\Throwable $exception = null, $result) use($deferred, $callback) {
        try {
            $result = $callback($exception, $result);
        } catch (\Throwable $exception) {
            $deferred->fail($exception);
            return;
        }
        $deferred->resolve($result);
    });
    return $deferred->promise();
}
namespace _HumbugBoxb47773b41c19\Amp\Iterator;

use _HumbugBoxb47773b41c19\Amp\Delayed;
use _HumbugBoxb47773b41c19\Amp\Emitter;
use _HumbugBoxb47773b41c19\Amp\Iterator;
use _HumbugBoxb47773b41c19\Amp\Producer;
use _HumbugBoxb47773b41c19\Amp\Promise;
use function _HumbugBoxb47773b41c19\Amp\call;
use function _HumbugBoxb47773b41c19\Amp\coroutine;
use function _HumbugBoxb47773b41c19\Amp\Internal\createTypeError;
function fromIterable($iterable, int $delay = 0) : Iterator
{
    if (!$iterable instanceof \Traversable && !\is_array($iterable)) {
        throw createTypeError(["array", "Traversable"], $iterable);
    }
    if ($delay) {
        return new Producer(static function (callable $emit) use($iterable, $delay) {
            foreach ($iterable as $value) {
                (yield new Delayed($delay));
                (yield $emit($value));
            }
        });
    }
    return new Producer(static function (callable $emit) use($iterable) {
        foreach ($iterable as $value) {
            (yield $emit($value));
        }
    });
}
/**
@template
@template
*/
function map(Iterator $iterator, callable $onEmit) : Iterator
{
    return new Producer(static function (callable $emit) use($iterator, $onEmit) {
        while ((yield $iterator->advance())) {
            (yield $emit($onEmit($iterator->getCurrent())));
        }
    });
}
/**
@template
*/
function filter(Iterator $iterator, callable $filter) : Iterator
{
    return new Producer(static function (callable $emit) use($iterator, $filter) {
        while ((yield $iterator->advance())) {
            if ($filter($iterator->getCurrent())) {
                (yield $emit($iterator->getCurrent()));
            }
        }
    });
}
function merge(array $iterators) : Iterator
{
    $emitter = new Emitter();
    $result = $emitter->iterate();
    $coroutine = coroutine(static function (Iterator $iterator) use(&$emitter) {
        while ((yield $iterator->advance()) && $emitter !== null) {
            (yield $emitter->emit($iterator->getCurrent()));
        }
    });
    $coroutines = [];
    foreach ($iterators as $iterator) {
        if (!$iterator instanceof Iterator) {
            throw createTypeError([Iterator::class], $iterator);
        }
        $coroutines[] = $coroutine($iterator);
    }
    Promise\all($coroutines)->onResolve(static function ($exception) use(&$emitter) {
        if ($exception) {
            $emitter->fail($exception);
            $emitter = null;
        } else {
            $emitter->complete();
        }
    });
    return $result;
}
function concat(array $iterators) : Iterator
{
    foreach ($iterators as $iterator) {
        if (!$iterator instanceof Iterator) {
            throw createTypeError([Iterator::class], $iterator);
        }
    }
    $emitter = new Emitter();
    $previous = [];
    $promise = Promise\all($previous);
    $coroutine = coroutine(static function (Iterator $iterator, callable $emit) {
        while ((yield $iterator->advance())) {
            (yield $emit($iterator->getCurrent()));
        }
    });
    foreach ($iterators as $iterator) {
        $emit = coroutine(static function ($value) use($emitter, $promise) {
            static $pending = \true, $failed = \false;
            if ($failed) {
                return;
            }
            if ($pending) {
                try {
                    (yield $promise);
                    $pending = \false;
                } catch (\Throwable $exception) {
                    $failed = \true;
                    return;
                }
            }
            (yield $emitter->emit($value));
        });
        $previous[] = $coroutine($iterator, $emit);
        $promise = Promise\all($previous);
    }
    $promise->onResolve(static function ($exception) use($emitter) {
        if ($exception) {
            $emitter->fail($exception);
            return;
        }
        $emitter->complete();
    });
    return $emitter->iterate();
}
/**
@template
@psalm-param
@psalm-return
*/
function discard(Iterator $iterator) : Promise
{
    return call(static function () use($iterator) : \Generator {
        $count = 0;
        while ((yield $iterator->advance())) {
            $count++;
        }
        return $count;
    });
}
/**
@template
@psalm-param
@psalm-return
*/
function toArray(Iterator $iterator) : Promise
{
    return call(static function () use($iterator) {
        /**
        @psalm-var */
        $array = [];
        while ((yield $iterator->advance())) {
            $array[] = $iterator->getCurrent();
        }
        return $array;
    });
}
