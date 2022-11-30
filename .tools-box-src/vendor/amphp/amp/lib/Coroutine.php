<?php

namespace _HumbugBoxb47773b41c19\Amp;

use _HumbugBoxb47773b41c19\React\Promise\PromiseInterface as ReactPromise;
/**
@template-covariant
@template-implements
*/
final class Coroutine implements Promise
{
    use Internal\Placeholder;
    private static function transform($yielded, $generator) : Promise
    {
        $exception = null;
        try {
            if (\is_array($yielded)) {
                return Promise\all($yielded);
            }
            if ($yielded instanceof ReactPromise) {
                return Promise\adapt($yielded);
            }
        } catch (\Throwable $exception) {
        }
        return new Failure(new InvalidYieldError($generator, \sprintf("Unexpected yield; Expected an instance of %s or %s or an array of such instances", Promise::class, ReactPromise::class), $exception));
    }
    /**
    @psalm-param
    */
    public function __construct(\Generator $generator)
    {
        try {
            $yielded = $generator->current();
            if (!$yielded instanceof Promise) {
                if (!$generator->valid()) {
                    $this->resolve($generator->getReturn());
                    return;
                }
                $yielded = self::transform($yielded, $generator);
            }
        } catch (\Throwable $exception) {
            $this->fail($exception);
            return;
        }
        /**
        @psalm-suppress
        @psalm-suppress
        */
        $onResolve = function (\Throwable $e = null, $v) use($generator, &$onResolve) {
            static $immediate = \true;
            static $exception;
            static $value;
            $exception = $e;
            /**
            @psalm-suppress */
            $value = $v;
            if (!$immediate) {
                $immediate = \true;
                return;
            }
            try {
                try {
                    do {
                        if ($exception) {
                            $yielded = $generator->throw($exception);
                        } else {
                            $yielded = $generator->send($value);
                        }
                        if (!$yielded instanceof Promise) {
                            if (!$generator->valid()) {
                                $this->resolve($generator->getReturn());
                                $onResolve = null;
                                return;
                            }
                            $yielded = self::transform($yielded, $generator);
                        }
                        $immediate = \false;
                        $yielded->onResolve($onResolve);
                    } while ($immediate);
                    $immediate = \true;
                } catch (\Throwable $exception) {
                    $this->fail($exception);
                    $onResolve = null;
                } finally {
                    $exception = null;
                    $value = null;
                }
            } catch (\Throwable $e) {
                Loop::defer(static function () use($e) {
                    throw $e;
                });
            }
        };
        try {
            $yielded->onResolve($onResolve);
            unset($generator, $yielded, $onResolve);
        } catch (\Throwable $e) {
            Loop::defer(static function () use($e) {
                throw $e;
            });
        }
    }
}
