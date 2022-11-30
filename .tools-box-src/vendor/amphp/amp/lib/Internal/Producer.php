<?php

namespace _HumbugBoxb47773b41c19\Amp\Internal;

use _HumbugBoxb47773b41c19\Amp\Deferred;
use _HumbugBoxb47773b41c19\Amp\Failure;
use _HumbugBoxb47773b41c19\Amp\Promise;
use _HumbugBoxb47773b41c19\Amp\Success;
use _HumbugBoxb47773b41c19\React\Promise\PromiseInterface as ReactPromise;
/**
@template-covariant
*/
trait Producer
{
    private $complete;
    private $values = [];
    private $backPressure = [];
    private $consumePosition = -1;
    private $emitPosition = -1;
    private $waiting;
    private $resolutionTrace;
    public function advance() : Promise
    {
        if ($this->waiting !== null) {
            throw new \Error("The prior promise returned must resolve before invoking this method again");
        }
        unset($this->values[$this->consumePosition]);
        $position = ++$this->consumePosition;
        if (\array_key_exists($position, $this->values)) {
            \assert(isset($this->backPressure[$position]));
            $deferred = $this->backPressure[$position];
            unset($this->backPressure[$position]);
            $deferred->resolve();
            return new Success(\true);
        }
        if ($this->complete) {
            return $this->complete;
        }
        $this->waiting = new Deferred();
        return $this->waiting->promise();
    }
    public function getCurrent()
    {
        if (empty($this->values) && $this->complete) {
            throw new \Error("The iterator has completed");
        }
        if (!\array_key_exists($this->consumePosition, $this->values)) {
            throw new \Error("Promise returned from advance() must resolve before calling this method");
        }
        return $this->values[$this->consumePosition];
    }
    /**
    @psalm-return
    */
    private function emit($value) : Promise
    {
        if ($this->complete) {
            throw new \Error("Iterators cannot emit values after calling complete");
        }
        if ($value instanceof ReactPromise) {
            $value = Promise\adapt($value);
        }
        if ($value instanceof Promise) {
            $deferred = new Deferred();
            $value->onResolve(function ($e, $v) use($deferred) {
                if ($this->complete) {
                    $deferred->fail(new \Error("The iterator was completed before the promise result could be emitted"));
                    return;
                }
                if ($e) {
                    $this->fail($e);
                    $deferred->fail($e);
                    return;
                }
                $deferred->resolve($this->emit($v));
            });
            return $deferred->promise();
        }
        $position = ++$this->emitPosition;
        $this->values[$position] = $value;
        if ($this->waiting !== null) {
            $waiting = $this->waiting;
            $this->waiting = null;
            $waiting->resolve(\true);
            return new Success();
        }
        $this->backPressure[$position] = $pressure = new Deferred();
        return $pressure->promise();
    }
    private function complete()
    {
        if ($this->complete) {
            $message = "Iterator has already been completed";
            if (isset($this->resolutionTrace)) {
                $trace = formatStacktrace($this->resolutionTrace);
                $message .= ". Previous completion trace:\n\n{$trace}\n\n";
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
        $this->complete = new Success(\false);
        if ($this->waiting !== null) {
            $waiting = $this->waiting;
            $this->waiting = null;
            $waiting->resolve($this->complete);
        }
    }
    private function fail(\Throwable $exception)
    {
        $this->complete = new Failure($exception);
        if ($this->waiting !== null) {
            $waiting = $this->waiting;
            $this->waiting = null;
            $waiting->resolve($this->complete);
        }
    }
}
