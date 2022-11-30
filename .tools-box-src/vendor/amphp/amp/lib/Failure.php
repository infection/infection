<?php

namespace _HumbugBoxb47773b41c19\Amp;

use _HumbugBoxb47773b41c19\React\Promise\PromiseInterface as ReactPromise;
/**
@template-covariant
@template-implements
*/
final class Failure implements Promise
{
    private $exception;
    public function __construct(\Throwable $exception)
    {
        $this->exception = $exception;
    }
    public function onResolve(callable $onResolved)
    {
        try {
            $result = $onResolved($this->exception, null);
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
