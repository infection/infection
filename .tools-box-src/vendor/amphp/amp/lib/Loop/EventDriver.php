<?php

namespace _HumbugBoxb47773b41c19\Amp\Loop;

use _HumbugBoxb47773b41c19\Amp\Coroutine;
use _HumbugBoxb47773b41c19\Amp\Promise;
use _HumbugBoxb47773b41c19\React\Promise\PromiseInterface as ReactPromise;
use function _HumbugBoxb47773b41c19\Amp\Internal\getCurrentTime;
use function _HumbugBoxb47773b41c19\Amp\Promise\rethrow;
class EventDriver extends Driver
{
    private static $activeSignals;
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
        $config = new \EventConfig();
        if (\DIRECTORY_SEPARATOR !== '\\') {
            $config->requireFeatures(\EventConfig::FEATURE_FDS);
        }
        $this->handle = new \EventBase($config);
        $this->nowOffset = getCurrentTime();
        $this->now = \random_int(0, $this->nowOffset);
        $this->nowOffset -= $this->now;
        if (self::$activeSignals === null) {
            self::$activeSignals =& $this->signals;
        }
        $this->ioCallback = function ($resource, $what, Watcher $watcher) {
            \assert(\is_resource($watcher->value));
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
        $this->timerCallback = function ($resource, $what, Watcher $watcher) {
            \assert(\is_int($watcher->value));
            if ($watcher->type & Watcher::DELAY) {
                $this->cancel($watcher->id);
            } else {
                $this->events[$watcher->id]->add($watcher->value / self::MILLISEC_PER_SEC);
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
        $this->signalCallback = function ($signum, $what, Watcher $watcher) {
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
        if (isset($this->events[$watcherId])) {
            $this->events[$watcherId]->free();
            unset($this->events[$watcherId]);
        }
    }
    public static function isSupported() : bool
    {
        return \extension_loaded("event");
    }
    public function __destruct()
    {
        $events = $this->events;
        $this->events = [];
        foreach ($events as $event) {
            if ($event !== null) {
                $event->free();
            }
        }
        if ($this->handle !== null) {
            $this->handle->free();
            $this->handle = null;
        }
    }
    public function run()
    {
        $active = self::$activeSignals;
        \assert($active !== null);
        foreach ($active as $event) {
            $event->del();
        }
        self::$activeSignals =& $this->signals;
        foreach ($this->signals as $event) {
            /**
            @psalm-suppress */
            $event->add();
        }
        try {
            parent::run();
        } finally {
            foreach ($this->signals as $event) {
                $event->del();
            }
            self::$activeSignals =& $active;
            foreach ($active as $event) {
                /**
                @psalm-suppress */
                $event->add();
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
    public function getHandle() : \EventBase
    {
        return $this->handle;
    }
    protected function dispatch(bool $blocking)
    {
        $this->handle->loop($blocking ? \EventBase::LOOP_ONCE : \EventBase::LOOP_ONCE | \EventBase::LOOP_NONBLOCK);
    }
    protected function activate(array $watchers)
    {
        $now = $this->now();
        foreach ($watchers as $watcher) {
            if (!isset($this->events[$id = $watcher->id])) {
                switch ($watcher->type) {
                    case Watcher::READABLE:
                        \assert(\is_resource($watcher->value));
                        $this->events[$id] = new \Event($this->handle, $watcher->value, \Event::READ | \Event::PERSIST, $this->ioCallback, $watcher);
                        break;
                    case Watcher::WRITABLE:
                        \assert(\is_resource($watcher->value));
                        $this->events[$id] = new \Event($this->handle, $watcher->value, \Event::WRITE | \Event::PERSIST, $this->ioCallback, $watcher);
                        break;
                    case Watcher::DELAY:
                    case Watcher::REPEAT:
                        \assert(\is_int($watcher->value));
                        $this->events[$id] = new \Event($this->handle, -1, \Event::TIMEOUT, $this->timerCallback, $watcher);
                        break;
                    case Watcher::SIGNAL:
                        \assert(\is_int($watcher->value));
                        $this->events[$id] = new \Event($this->handle, $watcher->value, \Event::SIGNAL | \Event::PERSIST, $this->signalCallback, $watcher);
                        break;
                    default:
                        throw new \Error("Unknown watcher type");
                }
            }
            switch ($watcher->type) {
                case Watcher::DELAY:
                case Watcher::REPEAT:
                    \assert(\is_int($watcher->value));
                    $interval = \max(0, $watcher->expiration - $now);
                    $this->events[$id]->add($interval > 0 ? $interval / self::MILLISEC_PER_SEC : 0);
                    break;
                case Watcher::SIGNAL:
                    $this->signals[$id] = $this->events[$id];
                default:
                    /**
                    @psalm-suppress */
                    $this->events[$id]->add();
                    break;
            }
        }
    }
    protected function deactivate(Watcher $watcher)
    {
        if (isset($this->events[$id = $watcher->id])) {
            $this->events[$id]->del();
            if ($watcher->type === Watcher::SIGNAL) {
                unset($this->signals[$id]);
            }
        }
    }
}
