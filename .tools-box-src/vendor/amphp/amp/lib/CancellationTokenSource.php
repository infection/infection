<?php

namespace _HumbugBoxb47773b41c19\Amp;

use _HumbugBoxb47773b41c19\React\Promise\PromiseInterface as ReactPromise;
use function _HumbugBoxb47773b41c19\Amp\Promise\rethrow;
final class CancellationTokenSource
{
    private $token;
    private $onCancel;
    public function __construct()
    {
        $onCancel = null;
        $this->token = new class($onCancel) implements CancellationToken
        {
            private $nextId = "a";
            private $callbacks = [];
            private $exception;
            /**
            @param-out
            */
            public function __construct(&$onCancel)
            {
                /**
                @psalm-suppress */
                $onCancel = function (\Throwable $exception) {
                    $this->exception = $exception;
                    $callbacks = $this->callbacks;
                    $this->callbacks = [];
                    foreach ($callbacks as $callback) {
                        $this->invokeCallback($callback);
                    }
                };
            }
            private function invokeCallback(callable $callback)
            {
                try {
                    $result = $callback($this->exception);
                    if ($result instanceof \Generator) {
                        /**
                        @psalm-var */
                        $result = new Coroutine($result);
                    }
                    if ($result instanceof Promise || $result instanceof ReactPromise) {
                        rethrow($result);
                    }
                } catch (\Throwable $exception) {
                    Loop::defer(static function () use($exception) {
                        throw $exception;
                    });
                }
            }
            public function subscribe(callable $callback) : string
            {
                $id = $this->nextId++;
                if ($this->exception) {
                    $this->invokeCallback($callback);
                } else {
                    $this->callbacks[$id] = $callback;
                }
                return $id;
            }
            public function unsubscribe(string $id)
            {
                unset($this->callbacks[$id]);
            }
            public function isRequested() : bool
            {
                return isset($this->exception);
            }
            public function throwIfRequested()
            {
                if (isset($this->exception)) {
                    throw $this->exception;
                }
            }
        };
        $this->onCancel = $onCancel;
    }
    public function getToken() : CancellationToken
    {
        return $this->token;
    }
    public function cancel(\Throwable $previous = null)
    {
        if ($this->onCancel === null) {
            return;
        }
        $onCancel = $this->onCancel;
        $this->onCancel = null;
        $onCancel(new CancelledException($previous));
    }
}
