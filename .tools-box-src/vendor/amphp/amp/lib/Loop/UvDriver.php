<?php

namespace _HumbugBoxb47773b41c19\Amp\Loop;

use _HumbugBoxb47773b41c19\Amp\Coroutine;
use _HumbugBoxb47773b41c19\Amp\Promise;
use _HumbugBoxb47773b41c19\React\Promise\PromiseInterface as ReactPromise;
use function _HumbugBoxb47773b41c19\Amp\Promise\rethrow;
class UvDriver extends Driver
{
    private $handle;
    private $events = [];
    private $watchers = [];
    private $streams = [];
    private $ioCallback;
    private $timerCallback;
    private $signalCallback;
    public function __construct()
    {
        $this->handle = \uv_loop_new();
        $this->ioCallback = function ($event, $status, $events, $resource) {
            $watchers = $this->watchers[(int) $event];
            switch ($status) {
                case 0:
                    break;
                default:
                    $flags = 0;
                    foreach ($watchers as $watcher) {
                        $flags |= $watcher->enabled ? $watcher->type : 0;
                    }
                    \uv_poll_start($event, $flags, $this->ioCallback);
                    break;
            }
            foreach ($watchers as $watcher) {
                if (!($watcher->enabled && ($watcher->type & $events || ($events | 4) === 4))) {
                    continue;
                }
                try {
                    $result = ($watcher->callback)($watcher->id, $resource, $watcher->data);
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
        };
        $this->timerCallback = function ($event) {
            $watcher = $this->watchers[(int) $event][0];
            if ($watcher->type & Watcher::DELAY) {
                unset($this->events[$watcher->id], $this->watchers[(int) $event]);
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
        $this->signalCallback = function ($event, $signo) {
            $watcher = $this->watchers[(int) $event][0];
            try {
                $result = ($watcher->callback)($watcher->id, $signo, $watcher->data);
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
        if (!isset($this->events[$watcherId])) {
            return;
        }
        $event = $this->events[$watcherId];
        $eventId = (int) $event;
        if (isset($this->watchers[$eventId][0])) {
            unset($this->watchers[$eventId]);
        } elseif (isset($this->watchers[$eventId][$watcherId])) {
            $watcher = $this->watchers[$eventId][$watcherId];
            unset($this->watchers[$eventId][$watcherId]);
            if (empty($this->watchers[$eventId])) {
                unset($this->watchers[$eventId], $this->streams[(int) $watcher->value]);
            }
        }
        unset($this->events[$watcherId]);
    }
    public static function isSupported() : bool
    {
        return \extension_loaded("uv");
    }
    public function now() : int
    {
        \uv_update_time($this->handle);
        /**
        @psalm-suppress */
        return \uv_now($this->handle);
    }
    public function getHandle()
    {
        return $this->handle;
    }
    protected function dispatch(bool $blocking)
    {
        /**
        @psalm-suppress */
        \uv_run($this->handle, $blocking ? \UV::RUN_ONCE : \UV::RUN_NOWAIT);
    }
    protected function activate(array $watchers)
    {
        $now = $this->now();
        foreach ($watchers as $watcher) {
            $id = $watcher->id;
            switch ($watcher->type) {
                case Watcher::READABLE:
                case Watcher::WRITABLE:
                    \assert(\is_resource($watcher->value));
                    $streamId = (int) $watcher->value;
                    if (isset($this->streams[$streamId])) {
                        $event = $this->streams[$streamId];
                    } elseif (isset($this->events[$id])) {
                        $event = $this->streams[$streamId] = $this->events[$id];
                    } else {
                        /**
                        @psalm-suppress */
                        $event = $this->streams[$streamId] = \_HumbugBoxb47773b41c19\uv_poll_init_socket($this->handle, $watcher->value);
                    }
                    $eventId = (int) $event;
                    $this->events[$id] = $event;
                    $this->watchers[$eventId][$id] = $watcher;
                    $flags = 0;
                    foreach ($this->watchers[$eventId] as $w) {
                        $flags |= $w->enabled ? $w->type : 0;
                    }
                    \uv_poll_start($event, $flags, $this->ioCallback);
                    break;
                case Watcher::DELAY:
                case Watcher::REPEAT:
                    \assert(\is_int($watcher->value));
                    if (isset($this->events[$id])) {
                        $event = $this->events[$id];
                    } else {
                        $event = $this->events[$id] = \uv_timer_init($this->handle);
                    }
                    $this->watchers[(int) $event] = [$watcher];
                    \uv_timer_start($event, \max(0, $watcher->expiration - $now), $watcher->type & Watcher::REPEAT ? $watcher->value : 0, $this->timerCallback);
                    break;
                case Watcher::SIGNAL:
                    \assert(\is_int($watcher->value));
                    if (isset($this->events[$id])) {
                        $event = $this->events[$id];
                    } else {
                        /**
                        @psalm-suppress */
                        $event = $this->events[$id] = \_HumbugBoxb47773b41c19\uv_signal_init($this->handle);
                    }
                    $this->watchers[(int) $event] = [$watcher];
                    /**
                    @psalm-suppress */
                    \_HumbugBoxb47773b41c19\uv_signal_start($event, $this->signalCallback, $watcher->value);
                    break;
                default:
                    throw new \Error("Unknown watcher type");
            }
        }
    }
    protected function deactivate(Watcher $watcher)
    {
        $id = $watcher->id;
        if (!isset($this->events[$id])) {
            return;
        }
        $event = $this->events[$id];
        if (!\uv_is_active($event)) {
            return;
        }
        switch ($watcher->type) {
            case Watcher::READABLE:
            case Watcher::WRITABLE:
                $flags = 0;
                foreach ($this->watchers[(int) $event] as $w) {
                    $flags |= $w->enabled ? $w->type : 0;
                }
                if ($flags) {
                    \uv_poll_start($event, $flags, $this->ioCallback);
                } else {
                    \uv_poll_stop($event);
                }
                break;
            case Watcher::DELAY:
            case Watcher::REPEAT:
                \uv_timer_stop($event);
                break;
            case Watcher::SIGNAL:
                \uv_signal_stop($event);
                break;
            default:
                throw new \Error("Unknown watcher type");
        }
    }
}
