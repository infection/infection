<?php

namespace _HumbugBoxb47773b41c19\Amp\Internal;

use _HumbugBoxb47773b41c19\Amp\Coroutine;
use _HumbugBoxb47773b41c19\Amp\Loop;
use _HumbugBoxb47773b41c19\Amp\Promise;
use _HumbugBoxb47773b41c19\React\Promise\PromiseInterface as ReactPromise;
/**
@psalm-internal
*/
class ResolutionQueue
{
    private $queue = [];
    /**
    @psalm-param
    */
    public function __construct(callable $callback = null)
    {
        if ($callback !== null) {
            $this->push($callback);
        }
    }
    /**
    @psalm-param
    */
    public function push(callable $callback)
    {
        if ($callback instanceof self) {
            $this->queue = \array_merge($this->queue, $callback->queue);
            return;
        }
        $this->queue[] = $callback;
    }
    public function __invoke($exception, $value)
    {
        foreach ($this->queue as $callback) {
            try {
                $result = $callback($exception, $value);
                if ($result === null) {
                    continue;
                }
                if ($result instanceof \Generator) {
                    $result = new Coroutine($result);
                }
                if ($result instanceof Promise || $result instanceof ReactPromise) {
                    Promise\rethrow($result);
                }
            } catch (\Throwable $exception) {
                Loop::defer(static function () use($exception) {
                    throw $exception;
                });
            }
        }
    }
}
