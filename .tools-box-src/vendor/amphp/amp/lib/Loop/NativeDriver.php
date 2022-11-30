<?php

namespace _HumbugBoxb47773b41c19\Amp\Loop;

use _HumbugBoxb47773b41c19\Amp\CallableMaker;
use _HumbugBoxb47773b41c19\Amp\Coroutine;
use _HumbugBoxb47773b41c19\Amp\Promise;
use _HumbugBoxb47773b41c19\React\Promise\PromiseInterface as ReactPromise;
use function _HumbugBoxb47773b41c19\Amp\Internal\getCurrentTime;
use function _HumbugBoxb47773b41c19\Amp\Promise\rethrow;
class NativeDriver extends Driver
{
    use CallableMaker;
    private $readStreams = [];
    private $readWatchers = [];
    private $writeStreams = [];
    private $writeWatchers = [];
    private $timerQueue;
    private $signalWatchers = [];
    private $now;
    private $nowOffset;
    private $signalHandling;
    private $streamSelectErrorHandler;
    private $streamSelectIgnoreResult = \false;
    public function __construct()
    {
        $this->timerQueue = new Internal\TimerQueue();
        $this->signalHandling = \extension_loaded("pcntl");
        $this->nowOffset = getCurrentTime();
        $this->now = \random_int(0, $this->nowOffset);
        $this->nowOffset -= $this->now;
        $this->streamSelectErrorHandler = function ($errno, $message) {
            if (\stripos($message, "stream_select(): unable to select [4]: ") === 0) {
                $this->streamSelectIgnoreResult = \true;
                return;
            }
            if (\strpos($message, 'FD_SETSIZE') !== \false) {
                $message = \str_replace(["\r\n", "\n", "\r"], " ", $message);
                $pattern = '(stream_select\\(\\): You MUST recompile PHP with a larger value of FD_SETSIZE. It is set to (\\d+), but you have descriptors numbered at least as high as (\\d+)\\.)';
                if (\preg_match($pattern, $message, $match)) {
                    $helpLink = 'https://amphp.org/amp/event-loop/#implementations';
                    $message = 'You have reached the limits of stream_select(). It has a FD_SETSIZE of ' . $match[1] . ', but you have file descriptors numbered at least as high as ' . $match[2] . '. ' . "You can install one of the extensions listed on {$helpLink} to support a higher number of " . "concurrent file descriptors. If a large number of open file descriptors is unexpected, you " . "might be leaking file descriptors that aren't closed correctly.";
                }
            }
            throw new \Exception($message, $errno);
        };
    }
    public function onSignal(int $signo, callable $callback, $data = null) : string
    {
        if (!$this->signalHandling) {
            throw new UnsupportedFeatureException("Signal handling requires the pcntl extension");
        }
        return parent::onSignal($signo, $callback, $data);
    }
    public function now() : int
    {
        $this->now = getCurrentTime() - $this->nowOffset;
        return $this->now;
    }
    public function getHandle()
    {
        return null;
    }
    protected function dispatch(bool $blocking)
    {
        $this->selectStreams($this->readStreams, $this->writeStreams, $blocking ? $this->getTimeout() : 0);
        $now = $this->now();
        while ($watcher = $this->timerQueue->extract($now)) {
            if ($watcher->type & Watcher::REPEAT) {
                $watcher->enabled = \false;
                $this->enable($watcher->id);
            } else {
                $this->cancel($watcher->id);
            }
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
        if ($this->signalHandling) {
            \pcntl_signal_dispatch();
        }
    }
    protected function activate(array $watchers)
    {
        foreach ($watchers as $watcher) {
            switch ($watcher->type) {
                case Watcher::READABLE:
                    \assert(\is_resource($watcher->value));
                    $streamId = (int) $watcher->value;
                    $this->readWatchers[$streamId][$watcher->id] = $watcher;
                    $this->readStreams[$streamId] = $watcher->value;
                    break;
                case Watcher::WRITABLE:
                    \assert(\is_resource($watcher->value));
                    $streamId = (int) $watcher->value;
                    $this->writeWatchers[$streamId][$watcher->id] = $watcher;
                    $this->writeStreams[$streamId] = $watcher->value;
                    break;
                case Watcher::DELAY:
                case Watcher::REPEAT:
                    \assert(\is_int($watcher->value));
                    $this->timerQueue->insert($watcher);
                    break;
                case Watcher::SIGNAL:
                    \assert(\is_int($watcher->value));
                    if (!isset($this->signalWatchers[$watcher->value])) {
                        if (!@\pcntl_signal($watcher->value, $this->callableFromInstanceMethod('handleSignal'))) {
                            $message = "Failed to register signal handler";
                            if ($error = \error_get_last()) {
                                $message .= \sprintf("; Errno: %d; %s", $error["type"], $error["message"]);
                            }
                            throw new \Error($message);
                        }
                    }
                    $this->signalWatchers[$watcher->value][$watcher->id] = $watcher;
                    break;
                default:
                    throw new \Error("Unknown watcher type");
            }
        }
    }
    protected function deactivate(Watcher $watcher)
    {
        switch ($watcher->type) {
            case Watcher::READABLE:
                $streamId = (int) $watcher->value;
                unset($this->readWatchers[$streamId][$watcher->id]);
                if (empty($this->readWatchers[$streamId])) {
                    unset($this->readWatchers[$streamId], $this->readStreams[$streamId]);
                }
                break;
            case Watcher::WRITABLE:
                $streamId = (int) $watcher->value;
                unset($this->writeWatchers[$streamId][$watcher->id]);
                if (empty($this->writeWatchers[$streamId])) {
                    unset($this->writeWatchers[$streamId], $this->writeStreams[$streamId]);
                }
                break;
            case Watcher::DELAY:
            case Watcher::REPEAT:
                $this->timerQueue->remove($watcher);
                break;
            case Watcher::SIGNAL:
                \assert(\is_int($watcher->value));
                if (isset($this->signalWatchers[$watcher->value])) {
                    unset($this->signalWatchers[$watcher->value][$watcher->id]);
                    if (empty($this->signalWatchers[$watcher->value])) {
                        unset($this->signalWatchers[$watcher->value]);
                        @\pcntl_signal($watcher->value, \SIG_DFL);
                    }
                }
                break;
            default:
                throw new \Error("Unknown watcher type");
        }
    }
    private function selectStreams(array $read, array $write, int $timeout)
    {
        $timeout /= self::MILLISEC_PER_SEC;
        if (!empty($read) || !empty($write)) {
            if ($timeout >= 0) {
                $seconds = (int) $timeout;
                $microseconds = (int) (($timeout - $seconds) * self::MICROSEC_PER_SEC);
            } else {
                $seconds = null;
                $microseconds = null;
            }
            $except = null;
            if (\DIRECTORY_SEPARATOR === '\\') {
                $except = $write;
            }
            \set_error_handler($this->streamSelectErrorHandler);
            try {
                $result = \stream_select($read, $write, $except, $seconds, $microseconds);
            } finally {
                \restore_error_handler();
            }
            if ($this->streamSelectIgnoreResult || $result === 0) {
                $this->streamSelectIgnoreResult = \false;
                return;
            }
            if (!$result) {
                $this->error(new \Exception('Unknown error during stream_select'));
                return;
            }
            foreach ($read as $stream) {
                $streamId = (int) $stream;
                if (!isset($this->readWatchers[$streamId])) {
                    continue;
                }
                foreach ($this->readWatchers[$streamId] as $watcher) {
                    if (!isset($this->readWatchers[$streamId][$watcher->id])) {
                        continue;
                    }
                    try {
                        $result = ($watcher->callback)($watcher->id, $stream, $watcher->data);
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
            }
            \assert(\is_array($write));
            if ($except) {
                foreach ($except as $key => $socket) {
                    $write[$key] = $socket;
                }
            }
            foreach ($write as $stream) {
                $streamId = (int) $stream;
                if (!isset($this->writeWatchers[$streamId])) {
                    continue;
                }
                foreach ($this->writeWatchers[$streamId] as $watcher) {
                    if (!isset($this->writeWatchers[$streamId][$watcher->id])) {
                        continue;
                    }
                    try {
                        $result = ($watcher->callback)($watcher->id, $stream, $watcher->data);
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
            }
            return;
        }
        if ($timeout < 0) {
            \usleep(\PHP_INT_MAX);
            return;
        }
        if ($timeout > 0) {
            \usleep((int) ($timeout * self::MICROSEC_PER_SEC));
        }
    }
    private function getTimeout() : int
    {
        $expiration = $this->timerQueue->peek();
        if ($expiration === null) {
            return -1;
        }
        $expiration -= getCurrentTime() - $this->nowOffset;
        return $expiration > 0 ? $expiration : 0;
    }
    private function handleSignal(int $signo)
    {
        foreach ($this->signalWatchers[$signo] as $watcher) {
            if (!isset($this->signalWatchers[$signo][$watcher->id])) {
                continue;
            }
            try {
                $result = ($watcher->callback)($watcher->id, $signo, $watcher->data);
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
    }
}
