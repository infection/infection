<?php

namespace _HumbugBoxb47773b41c19\Amp;

use _HumbugBoxb47773b41c19\React\Promise\PromiseInterface as ReactPromise;
/**
@template-covariant
@template-implements
*/
final class Success implements Promise
{
    private $value;
    /**
    @psalm-param
    */
    public function __construct($value = null)
    {
        if ($value instanceof Promise || $value instanceof ReactPromise) {
            throw new \Error("Cannot use a promise as success value");
        }
        $this->value = $value;
    }
    public function onResolve(callable $onResolved)
    {
        try {
            $result = $onResolved(null, $this->value);
            if ($result === null) {
                return;
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
