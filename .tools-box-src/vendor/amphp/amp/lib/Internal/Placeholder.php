<?php

namespace _HumbugBoxb47773b41c19\Amp\Internal;

use _HumbugBoxb47773b41c19\Amp\Coroutine;
use _HumbugBoxb47773b41c19\Amp\Failure;
use _HumbugBoxb47773b41c19\Amp\Loop;
use _HumbugBoxb47773b41c19\Amp\Promise;
use _HumbugBoxb47773b41c19\React\Promise\PromiseInterface as ReactPromise;
trait Placeholder
{
    private $resolved = \false;
    private $result;
    private $onResolved;
    private $resolutionTrace;
    public function onResolve(callable $onResolved)
    {
        if ($this->resolved) {
            if ($this->result instanceof Promise) {
                $this->result->onResolve($onResolved);
                return;
            }
            try {
                $result = $onResolved(null, $this->result);
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
            return;
        }
        if (null === $this->onResolved) {
            $this->onResolved = $onResolved;
            return;
        }
        if (!$this->onResolved instanceof ResolutionQueue) {
            /**
            @psalm-suppress */
            $this->onResolved = new ResolutionQueue($this->onResolved);
        }
        /**
        @psalm-suppress */
        $this->onResolved->push($onResolved);
    }
    public function __destruct()
    {
        try {
            $this->result = null;
        } catch (\Throwable $e) {
            Loop::defer(static function () use($e) {
                throw $e;
            });
        }
    }
    private function resolve($value = null)
    {
        if ($this->resolved) {
            $message = "Promise has already been resolved";
            if (isset($this->resolutionTrace)) {
                $trace = formatStacktrace($this->resolutionTrace);
                $message .= ". Previous resolution trace:\n\n{$trace}\n\n";
            } else {
                $message .= ", define environment variable AMP_DEBUG or const AMP_DEBUG = true and enable assertions " . "for a stacktrace of the previous resolution.";
            }
            throw new \Error($message);
        }
        \assert((function () {
            $env = \getenv("AMP_DEBUG") ?: "0";
            if ($env !== "0" && $env !== "false" || \defined("AMP_DEBUG") && \AMP_DEBUG) {
                $trace = \debug_backtrace(\DEBUG_BACKTRACE_IGNORE_ARGS);
                \array_shift($trace);
                $this->resolutionTrace = $trace;
            }
            return \true;
        })());
        if ($value instanceof ReactPromise) {
            $value = Promise\adapt($value);
        }
        $this->resolved = \true;
        $this->result = $value;
        if ($this->onResolved === null) {
            return;
        }
        $onResolved = $this->onResolved;
        $this->onResolved = null;
        if ($this->result instanceof Promise) {
            $this->result->onResolve($onResolved);
            return;
        }
        try {
            $result = $onResolved(null, $this->result);
            $onResolved = null;
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
    private function fail(\Throwable $reason)
    {
        $this->resolve(new Failure($reason));
    }
    private function isResolved() : bool
    {
        return $this->resolved;
    }
}
