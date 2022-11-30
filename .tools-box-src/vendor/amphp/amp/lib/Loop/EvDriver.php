<?php

/**
@noinspection */
namespace _HumbugBoxb47773b41c19\Amp\Loop;

use _HumbugBoxb47773b41c19\Amp\Coroutine;
use _HumbugBoxb47773b41c19\Amp\Promise;
use _HumbugBoxb47773b41c19\React\Promise\PromiseInterface as ReactPromise;
use function _HumbugBoxb47773b41c19\Amp\Internal\getCurrentTime;
use function _HumbugBoxb47773b41c19\Amp\Promise\rethrow;
class EvDriver extends Driver
{
    private static $activeSignals;
    public static function isSupported() : bool
    {
        return \extension_loaded("ev");
    }
    private $handle;
    private $events = [];
    private $ioCallback;
    private $timerCallback;
    private $signalCallback;
    private $signals = [];
    private $now;
    private $nowOffset;
    public function __construct()
    {
        $this->handle = new \EvLoop();
        $this->nowOffset = getCurrentTime();
        $this->now = \random_int(0, $this->nowOffset);
        $this->nowOffset -= $this->now;
        if (self::$activeSignals === null) {
            self::$activeSignals =& $this->signals;
        }
        $this->ioCallback = function (\EvIO $event) {
            $watcher = $event->data;
            try {
                $result = ($watcher->callback)($watcher->id, $watcher->value, $watcher->data);
                if ($result === null) {
                    return;
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
        };
        $this->timerCallback = function (\EvTimer $event) {
            $watcher = $event->data;
            if ($watcher->type & Watcher::DELAY) {
                $this->cancel($watcher->id);
            } elseif ($watcher->value === 0) {
                $this->disable($watcher->id);
                $this->enable($watcher->id);
            }
            try {
                $result = ($watcher->callback)($watcher->id, $watcher->data);
                if ($result === null) {
                    return;
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
        };
        $this->signalCallback = function (\EvSignal $event) {
            $watcher = $event->data;
            try {
                $result = ($watcher->callback)($watcher->id, $watcher->value, $watcher->data);
                if ($result === null) {
                    return;
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
        };
    }
    public function cancel(string $watcherId)
    {
        parent::cancel($watcherId);
        unset($this->events[$watcherId]);
    }
    public function __destruct()
    {
        foreach ($this->events as $event) {
            /**
            @psalm-suppress */
            if ($event !== null) {
                $event->stop();
            }
        }
        $this->events = [];
    }
    public function run()
    {
        $active = self::$activeSignals;
        \assert($active !== null);
        foreach ($active as $event) {
            $event->stop();
        }
        self::$activeSignals =& $this->signals;
        foreach ($this->signals as $event) {
            $event->start();
        }
        try {
            parent::run();
        } finally {
            foreach ($this->signals as $event) {
                $event->stop();
            }
            self::$activeSignals =& $active;
            foreach ($active as $event) {
                $event->start();
            }
        }
    }
    public function stop()
    {
        $this->handle->stop();
        parent::stop();
    }
    public function now() : int
    {
        $this->now = getCurrentTime() - $this->nowOffset;
        return $this->now;
    }
    public function getHandle() : \EvLoop
    {
        return $this->handle;
    }
    protected function dispatch(bool $blocking)
    {
        $this->handle->run($blocking ? \Ev::RUN_ONCE : \Ev::RUN_ONCE | \Ev::RUN_NOWAIT);
    }
    protected function activate(array $watchers)
    {
        $this->handle->nowUpdate();
        $now = $this->now();
        foreach ($watchers as $watcher) {
            if (!isset($this->events[$id = $watcher->id])) {
                switch ($watcher->type) {
                    case Watcher::READABLE:
                        \assert(\is_resource($watcher->value));
                        $this->events[$id] = $this->handle->io($watcher->value, \Ev::READ, $this->ioCallback, $watcher);
                        break;
                    case Watcher::WRITABLE:
                        \assert(\is_resource($watcher->value));
                        $this->events[$id] = $this->handle->io($watcher->value, \Ev::WRITE, $this->ioCallback, $watcher);
                        break;
                    case Watcher::DELAY:
                    case Watcher::REPEAT:
                        \assert(\is_int($watcher->value));
                        $interval = $watcher->value / self::MILLISEC_PER_SEC;
                        $this->events[$id] = $this->handle->timer(\max(0, ($watcher->expiration - $now) / self::MILLISEC_PER_SEC), $watcher->type & Watcher::REPEAT ? $interval : 0, $this->timerCallback, $watcher);
                        break;
                    case Watcher::SIGNAL:
                        \assert(\is_int($watcher->value));
                        $this->events[$id] = $this->handle->signal($watcher->value, $this->signalCallback, $watcher);
                        break;
                    default:
                        throw new \Error("Unknown watcher type");
                }
            } else {
                $this->events[$id]->start();
            }
            if ($watcher->type === Watcher::SIGNAL) {
                /**
                @psalm-suppress */
                $this->signals[$id] = $this->events[$id];
            }
        }
    }
    protected function deactivate(Watcher $watcher)
    {
        if (isset($this->events[$id = $watcher->id])) {
            $this->events[$id]->stop();
            if ($watcher->type === Watcher::SIGNAL) {
                unset($this->signals[$id]);
            }
        }
    }
}
