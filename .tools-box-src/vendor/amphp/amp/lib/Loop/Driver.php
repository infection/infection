<?php

namespace _HumbugBoxb47773b41c19\Amp\Loop;

use _HumbugBoxb47773b41c19\Amp\Coroutine;
use _HumbugBoxb47773b41c19\Amp\Promise;
use _HumbugBoxb47773b41c19\React\Promise\PromiseInterface as ReactPromise;
use function _HumbugBoxb47773b41c19\Amp\Promise\rethrow;
abstract class Driver
{
    const MILLISEC_PER_SEC = 1000;
    const MICROSEC_PER_SEC = 1000000;
    private $nextId = "a";
    private $watchers = [];
    private $enableQueue = [];
    private $deferQueue = [];
    private $nextTickQueue = [];
    private $errorHandler;
    private $running = \false;
    private $registry = [];
    public function run()
    {
        $this->running = \true;
        try {
            while ($this->running) {
                if ($this->isEmpty()) {
                    return;
                }
                $this->tick();
            }
        } finally {
            $this->stop();
        }
    }
    private function isEmpty() : bool
    {
        foreach ($this->watchers as $watcher) {
            if ($watcher->enabled && $watcher->referenced) {
                return \false;
            }
        }
        return \true;
    }
    private function tick()
    {
        if (empty($this->deferQueue)) {
            $this->deferQueue = $this->nextTickQueue;
        } else {
            $this->deferQueue = \array_merge($this->deferQueue, $this->nextTickQueue);
        }
        $this->nextTickQueue = [];
        $this->activate($this->enableQueue);
        $this->enableQueue = [];
        foreach ($this->deferQueue as $watcher) {
            if (!isset($this->deferQueue[$watcher->id])) {
                continue;
            }
            unset($this->watchers[$watcher->id], $this->deferQueue[$watcher->id]);
            try {
                $result = ($watcher->callback)($watcher->id, $watcher->data);
                if ($result === null) {
                    continue;
                }
                if ($result instanceof \Generator) {
                    $result = new Coroutine($result);
                }
                if ($result instanceof Promise || $result instanceof ReactPromise) {
                    rethrow($result);
                }
            } catch (\Throwable $exception) {
                $this->error($exception);
            }
        }
        /**
        @psalm-suppress */
        $this->dispatch(empty($this->nextTickQueue) && empty($this->enableQueue) && $this->running && !$this->isEmpty());
    }
    protected abstract function activate(array $watchers);
    protected abstract function dispatch(bool $blocking);
    public function stop()
    {
        $this->running = \false;
    }
    public function defer(callable $callback, $data = null) : string
    {
        /**
        @psalm-var */
        $watcher = new Watcher();
        $watcher->type = Watcher::DEFER;
        $watcher->id = $this->nextId++;
        $watcher->callback = $callback;
        $watcher->data = $data;
        $this->watchers[$watcher->id] = $watcher;
        $this->nextTickQueue[$watcher->id] = $watcher;
        return $watcher->id;
    }
    public function delay(int $delay, callable $callback, $data = null) : string
    {
        if ($delay < 0) {
            throw new \Error("Delay must be greater than or equal to zero");
        }
        /**
        @psalm-var */
        $watcher = new Watcher();
        $watcher->type = Watcher::DELAY;
        $watcher->id = $this->nextId++;
        $watcher->callback = $callback;
        $watcher->value = $delay;
        $watcher->expiration = $this->now() + $delay;
        $watcher->data = $data;
        $this->watchers[$watcher->id] = $watcher;
        $this->enableQueue[$watcher->id] = $watcher;
        return $watcher->id;
    }
    public function repeat(int $interval, callable $callback, $data = null) : string
    {
        if ($interval < 0) {
            throw new \Error("Interval must be greater than or equal to zero");
        }
        /**
        @psalm-var */
        $watcher = new Watcher();
        $watcher->type = Watcher::REPEAT;
        $watcher->id = $this->nextId++;
        $watcher->callback = $callback;
        $watcher->value = $interval;
        $watcher->expiration = $this->now() + $interval;
        $watcher->data = $data;
        $this->watchers[$watcher->id] = $watcher;
        $this->enableQueue[$watcher->id] = $watcher;
        return $watcher->id;
    }
    public function onReadable($stream, callable $callback, $data = null) : string
    {
        /**
        @psalm-var */
        $watcher = new Watcher();
        $watcher->type = Watcher::READABLE;
        $watcher->id = $this->nextId++;
        $watcher->callback = $callback;
        $watcher->value = $stream;
        $watcher->data = $data;
        $this->watchers[$watcher->id] = $watcher;
        $this->enableQueue[$watcher->id] = $watcher;
        return $watcher->id;
    }
    public function onWritable($stream, callable $callback, $data = null) : string
    {
        /**
        @psalm-var */
        $watcher = new Watcher();
        $watcher->type = Watcher::WRITABLE;
        $watcher->id = $this->nextId++;
        $watcher->callback = $callback;
        $watcher->value = $stream;
        $watcher->data = $data;
        $this->watchers[$watcher->id] = $watcher;
        $this->enableQueue[$watcher->id] = $watcher;
        return $watcher->id;
    }
    public function onSignal(int $signo, callable $callback, $data = null) : string
    {
        /**
        @psalm-var */
        $watcher = new Watcher();
        $watcher->type = Watcher::SIGNAL;
        $watcher->id = $this->nextId++;
        $watcher->callback = $callback;
        $watcher->value = $signo;
        $watcher->data = $data;
        $this->watchers[$watcher->id] = $watcher;
        $this->enableQueue[$watcher->id] = $watcher;
        return $watcher->id;
    }
    public function enable(string $watcherId)
    {
        if (!isset($this->watchers[$watcherId])) {
            throw new InvalidWatcherError($watcherId, "Cannot enable an invalid watcher identifier: '{$watcherId}'");
        }
        $watcher = $this->watchers[$watcherId];
        if ($watcher->enabled) {
            return;
        }
        $watcher->enabled = \true;
        switch ($watcher->type) {
            case Watcher::DEFER:
                $this->nextTickQueue[$watcher->id] = $watcher;
                break;
            case Watcher::REPEAT:
            case Watcher::DELAY:
                \assert(\is_int($watcher->value));
                $watcher->expiration = $this->now() + $watcher->value;
                $this->enableQueue[$watcher->id] = $watcher;
                break;
            default:
                $this->enableQueue[$watcher->id] = $watcher;
                break;
        }
    }
    public function cancel(string $watcherId)
    {
        $this->disable($watcherId);
        unset($this->watchers[$watcherId]);
    }
    public function disable(string $watcherId)
    {
        if (!isset($this->watchers[$watcherId])) {
            return;
        }
        $watcher = $this->watchers[$watcherId];
        if (!$watcher->enabled) {
            return;
        }
        $watcher->enabled = \false;
        $id = $watcher->id;
        switch ($watcher->type) {
            case Watcher::DEFER:
                if (isset($this->nextTickQueue[$id])) {
                    unset($this->nextTickQueue[$id]);
                } else {
                    unset($this->deferQueue[$id]);
                }
                break;
            default:
                if (isset($this->enableQueue[$id])) {
                    unset($this->enableQueue[$id]);
                } else {
                    $this->deactivate($watcher);
                }
                break;
        }
    }
    protected abstract function deactivate(Watcher $watcher);
    public function reference(string $watcherId)
    {
        if (!isset($this->watchers[$watcherId])) {
            throw new InvalidWatcherError($watcherId, "Cannot reference an invalid watcher identifier: '{$watcherId}'");
        }
        $this->watchers[$watcherId]->referenced = \true;
    }
    public function unreference(string $watcherId)
    {
        if (!isset($this->watchers[$watcherId])) {
            return;
        }
        $this->watchers[$watcherId]->referenced = \false;
    }
    public final function setState(string $key, $value)
    {
        if ($value === null) {
            unset($this->registry[$key]);
        } else {
            $this->registry[$key] = $value;
        }
    }
    public final function getState(string $key)
    {
        return isset($this->registry[$key]) ? $this->registry[$key] : null;
    }
    public function setErrorHandler(callable $callback = null)
    {
        $previous = $this->errorHandler;
        $this->errorHandler = $callback;
        return $previous;
    }
    protected function error(\Throwable $exception)
    {
        if ($this->errorHandler === null) {
            throw $exception;
        }
        ($this->errorHandler)($exception);
    }
    public function now() : int
    {
        return (int) (\microtime(\true) * self::MILLISEC_PER_SEC);
    }
    public abstract function getHandle();
    public function __debugInfo()
    {
        return $this->getInfo();
    }
    public function getInfo() : array
    {
        $watchers = ["referenced" => 0, "unreferenced" => 0];
        $defer = $delay = $repeat = $onReadable = $onWritable = $onSignal = ["enabled" => 0, "disabled" => 0];
        foreach ($this->watchers as $watcher) {
            switch ($watcher->type) {
                case Watcher::READABLE:
                    $array =& $onReadable;
                    break;
                case Watcher::WRITABLE:
                    $array =& $onWritable;
                    break;
                case Watcher::SIGNAL:
                    $array =& $onSignal;
                    break;
                case Watcher::DEFER:
                    $array =& $defer;
                    break;
                case Watcher::DELAY:
                    $array =& $delay;
                    break;
                case Watcher::REPEAT:
                    $array =& $repeat;
                    break;
                default:
                    throw new \Error("Unknown watcher type");
            }
            if ($watcher->enabled) {
                ++$array["enabled"];
                if ($watcher->referenced) {
                    ++$watchers["referenced"];
                } else {
                    ++$watchers["unreferenced"];
                }
            } else {
                ++$array["disabled"];
            }
        }
        return ["enabled_watchers" => $watchers, "defer" => $defer, "delay" => $delay, "repeat" => $repeat, "on_readable" => $onReadable, "on_writable" => $onWritable, "on_signal" => $onSignal, "running" => (bool) $this->running];
    }
}
