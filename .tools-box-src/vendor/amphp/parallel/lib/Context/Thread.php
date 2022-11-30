<?php

namespace _HumbugBoxb47773b41c19\Amp\Parallel\Context;

use _HumbugBoxb47773b41c19\Amp\Failure;
use _HumbugBoxb47773b41c19\Amp\Loop;
use _HumbugBoxb47773b41c19\Amp\Parallel\Sync\ChannelledSocket;
use _HumbugBoxb47773b41c19\Amp\Parallel\Sync\ExitResult;
use _HumbugBoxb47773b41c19\Amp\Parallel\Sync\SynchronizationError;
use _HumbugBoxb47773b41c19\Amp\Promise;
use _HumbugBoxb47773b41c19\Amp\Success;
use function _HumbugBoxb47773b41c19\Amp\call;
final class Thread implements Context
{
    const EXIT_CHECK_FREQUENCY = 250;
    private static $nextId = 1;
    private $thread;
    private $channel;
    private $socket;
    private $function;
    private $args;
    private $id;
    private $oid = 0;
    private $watcher;
    public static function isSupported() : bool
    {
        return \extension_loaded('pthreads');
    }
    public static function run(callable $function, ...$args) : Promise
    {
        $thread = new self($function, ...$args);
        return call(function () use($thread) : \Generator {
            (yield $thread->start());
            return $thread;
        });
    }
    public function __construct(callable $function, ...$args)
    {
        if (!self::isSupported()) {
            throw new \Error("The pthreads extension is required to create threads.");
        }
        $this->function = $function;
        $this->args = $args;
    }
    public function __clone()
    {
        $this->thread = null;
        $this->socket = null;
        $this->channel = null;
        $this->oid = 0;
    }
    public function __destruct()
    {
        if (\getmypid() === $this->oid) {
            $this->kill();
        }
    }
    public function isRunning() : bool
    {
        return $this->channel !== null;
    }
    public function start() : Promise
    {
        if ($this->oid !== 0) {
            throw new StatusError('The thread has already been started.');
        }
        $this->oid = \getmypid();
        $sockets = @\stream_socket_pair(\stripos(\PHP_OS, "win") === 0 ? \STREAM_PF_INET : \STREAM_PF_UNIX, \STREAM_SOCK_STREAM, \STREAM_IPPROTO_IP);
        if ($sockets === \false) {
            $message = "Failed to create socket pair";
            if ($error = \error_get_last()) {
                $message .= \sprintf(" Errno: %d; %s", $error["type"], $error["message"]);
            }
            return new Failure(new ContextException($message));
        }
        list($channel, $this->socket) = $sockets;
        $this->id = self::$nextId++;
        $thread = $this->thread = new Internal\Thread($this->id, $this->socket, $this->function, $this->args);
        if (!$this->thread->start(\PTHREADS_INHERIT_INI)) {
            return new Failure(new ContextException('Failed to start the thread.'));
        }
        $channel = $this->channel = new ChannelledSocket($channel, $channel);
        $this->watcher = Loop::repeat(self::EXIT_CHECK_FREQUENCY, static function ($watcher) use($thread, $channel) : void {
            if (!$thread->isRunning()) {
                Loop::delay(self::EXIT_CHECK_FREQUENCY, [$channel, "close"]);
                Loop::cancel($watcher);
            }
        });
        Loop::disable($this->watcher);
        return new Success($this->id);
    }
    public function kill() : void
    {
        if ($this->thread !== null) {
            try {
                if ($this->thread->isRunning() && !$this->thread->kill()) {
                    throw new ContextException('Could not kill thread.');
                }
            } finally {
                $this->close();
            }
        }
    }
    private function close() : void
    {
        if ($this->channel !== null) {
            $this->channel->close();
        }
        $this->channel = null;
        Loop::cancel($this->watcher);
    }
    public function join() : Promise
    {
        if ($this->channel == null || $this->thread === null) {
            throw new StatusError('The thread has not been started or has already finished.');
        }
        return call(function () : \Generator {
            Loop::enable($this->watcher);
            try {
                $response = (yield $this->channel->receive());
            } catch (\Throwable $exception) {
                $this->kill();
                throw new ContextException("Failed to receive result from thread", 0, $exception);
            } finally {
                Loop::disable($this->watcher);
                $this->close();
            }
            if (!$response instanceof ExitResult) {
                $this->kill();
                throw new SynchronizationError('Did not receive an exit result from thread.');
            }
            return $response->getResult();
        });
    }
    public function receive() : Promise
    {
        if ($this->channel === null) {
            throw new StatusError('The process has not been started.');
        }
        return call(function () : \Generator {
            Loop::enable($this->watcher);
            try {
                $data = (yield $this->channel->receive());
            } finally {
                Loop::disable($this->watcher);
            }
            if ($data instanceof ExitResult) {
                $data = $data->getResult();
                throw new SynchronizationError(\sprintf('Thread process unexpectedly exited with result of type: %s', \is_object($data) ? \get_class($data) : \gettype($data)));
            }
            return $data;
        });
    }
    public function send($data) : Promise
    {
        if ($this->channel === null) {
            throw new StatusError('The thread has not been started or has already finished.');
        }
        if ($data instanceof ExitResult) {
            throw new \Error('Cannot send exit result objects.');
        }
        return call(function () use($data) : \Generator {
            Loop::enable($this->watcher);
            try {
                $result = (yield $this->channel->send($data));
            } finally {
                Loop::disable($this->watcher);
            }
            return $result;
        });
    }
    public function getId() : int
    {
        if ($this->id === null) {
            throw new StatusError('The thread has not been started');
        }
        return $this->id;
    }
}
