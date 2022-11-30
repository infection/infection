<?php

namespace _HumbugBoxb47773b41c19\Amp\Parallel\Context;

use _HumbugBoxb47773b41c19\Amp\Loop;
use _HumbugBoxb47773b41c19\Amp\Parallel\Sync\ChannelException;
use _HumbugBoxb47773b41c19\Amp\Parallel\Sync\ChannelledSocket;
use _HumbugBoxb47773b41c19\Amp\Parallel\Sync\ExitFailure;
use _HumbugBoxb47773b41c19\Amp\Parallel\Sync\ExitResult;
use _HumbugBoxb47773b41c19\Amp\Parallel\Sync\ExitSuccess;
use _HumbugBoxb47773b41c19\Amp\Parallel\Sync\SerializationException;
use _HumbugBoxb47773b41c19\Amp\Parallel\Sync\SynchronizationError;
use _HumbugBoxb47773b41c19\Amp\Promise;
use _HumbugBoxb47773b41c19\Amp\TimeoutException;
use parallel\Runtime;
use function _HumbugBoxb47773b41c19\Amp\call;
final class Parallel implements Context
{
    const EXIT_CHECK_FREQUENCY = 250;
    const KEY_LENGTH = 32;
    private static $autoloadPath;
    private static $nextId = 1;
    private $hub;
    private $id;
    private $runtime;
    private $channel;
    private $script;
    private $args = [];
    private $oid = 0;
    private $killed = \false;
    public static function isSupported() : bool
    {
        return \extension_loaded('parallel');
    }
    public static function run($script) : Promise
    {
        $thread = new self($script);
        return call(function () use($thread) : \Generator {
            (yield $thread->start());
            return $thread;
        });
    }
    public function __construct($script)
    {
        if (!self::isSupported()) {
            throw new \Error("The parallel extension is required to create parallel threads.");
        }
        $this->hub = Loop::getState(self::class);
        if (!$this->hub instanceof Internal\ParallelHub) {
            $this->hub = new Internal\ParallelHub();
            Loop::setState(self::class, $this->hub);
        }
        if (\is_array($script)) {
            $this->script = (string) \array_shift($script);
            $this->args = \array_values(\array_map("strval", $script));
        } else {
            $this->script = (string) $script;
        }
        if (self::$autoloadPath === null) {
            $paths = [\dirname(__DIR__, 2) . \DIRECTORY_SEPARATOR . "vendor" . \DIRECTORY_SEPARATOR . "autoload.php", \dirname(__DIR__, 4) . \DIRECTORY_SEPARATOR . "autoload.php"];
            foreach ($paths as $path) {
                if (\file_exists($path)) {
                    self::$autoloadPath = $path;
                    break;
                }
            }
            if (self::$autoloadPath === null) {
                throw new \Error("Could not locate autoload.php");
            }
        }
    }
    public function __clone()
    {
        $this->runtime = null;
        $this->channel = null;
        $this->id = null;
        $this->oid = 0;
        $this->killed = \false;
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
        $this->runtime = new Runtime(self::$autoloadPath);
        $this->id = self::$nextId++;
        $future = $this->runtime->run(static function (int $id, string $uri, string $key, string $path, array $argv) : int {
            \define("AMP_CONTEXT", "parallel");
            \define("AMP_CONTEXT_ID", $id);
            if (!($socket = \stream_socket_client($uri, $errno, $errstr, 5, \STREAM_CLIENT_CONNECT))) {
                \trigger_error("Could not connect to IPC socket", \E_USER_ERROR);
                return 1;
            }
            $channel = new ChannelledSocket($socket, $socket);
            try {
                Promise\wait($channel->send($key));
            } catch (\Throwable $exception) {
                \trigger_error("Could not send key to parent", \E_USER_ERROR);
                return 1;
            }
            try {
                Loop::unreference(Loop::repeat(self::EXIT_CHECK_FREQUENCY, function () : void {
                }));
                try {
                    if (!\is_file($path)) {
                        throw new \Error(\sprintf("No script found at '%s' (be sure to provide the full path to the script)", $path));
                    }
                    $argc = \array_unshift($argv, $path);
                    try {
                        $callable = (function () use($argc, $argv) : callable {
                            return require $argv[0];
                        })->bindTo(null, null)();
                    } catch (\TypeError $exception) {
                        throw new \Error(\sprintf("Script '%s' did not return a callable function", $path), 0, $exception);
                    } catch (\ParseError $exception) {
                        throw new \Error(\sprintf("Script '%s' contains a parse error", $path), 0, $exception);
                    }
                    $result = new ExitSuccess(Promise\wait(call($callable, $channel)));
                } catch (\Throwable $exception) {
                    $result = new ExitFailure($exception);
                }
                Promise\wait(call(function () use($channel, $result) : \Generator {
                    try {
                        (yield $channel->send($result));
                    } catch (SerializationException $exception) {
                        (yield $channel->send(new ExitFailure($exception)));
                    }
                }));
            } catch (\Throwable $exception) {
                \trigger_error("Could not send result to parent; be sure to shutdown the child before ending the parent", \E_USER_ERROR);
                return 1;
            } finally {
                $channel->close();
            }
            return 0;
        }, [$this->id, $this->hub->getUri(), $this->hub->generateKey($this->id, self::KEY_LENGTH), $this->script, $this->args]);
        return call(function () use($future) : \Generator {
            try {
                $this->channel = (yield $this->hub->accept($this->id));
                $this->hub->add($this->id, $this->channel, $future);
            } catch (\Throwable $exception) {
                $this->kill();
                throw new ContextException("Starting the parallel runtime failed", 0, $exception);
            }
            if ($this->killed) {
                $this->kill();
            }
            return $this->id;
        });
    }
    public function kill() : void
    {
        $this->killed = \true;
        if ($this->runtime !== null) {
            try {
                $this->runtime->kill();
            } finally {
                $this->close();
            }
        }
    }
    private function close() : void
    {
        $this->runtime = null;
        if ($this->channel !== null) {
            $this->channel->close();
        }
        $this->channel = null;
        $this->hub->remove($this->id);
    }
    public function join() : Promise
    {
        if ($this->channel === null) {
            throw new StatusError('The thread has not been started or has already finished.');
        }
        return call(function () : \Generator {
            try {
                $response = (yield $this->channel->receive());
                $this->close();
            } catch (\Throwable $exception) {
                $this->kill();
                throw new ContextException("Failed to receive result from thread", 0, $exception);
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
            throw new StatusError('The thread has not been started.');
        }
        return call(function () : \Generator {
            try {
                $data = (yield $this->channel->receive());
            } catch (ChannelException $e) {
                throw new ContextException("The thread stopped responding, potentially due to a fatal error or calling exit", 0, $e);
            }
            if ($data instanceof ExitResult) {
                $data = $data->getResult();
                throw new SynchronizationError(\sprintf('Thread unexpectedly exited with result of type: %s', \is_object($data) ? \get_class($data) : \gettype($data)));
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
            try {
                return (yield $this->channel->send($data));
            } catch (ChannelException $e) {
                if ($this->channel === null) {
                    throw new ContextException("The thread stopped responding, potentially due to a fatal error or calling exit", 0, $e);
                }
                try {
                    $data = (yield Promise\timeout($this->join(), 100));
                } catch (ContextException|ChannelException|TimeoutException $ex) {
                    $this->kill();
                    throw new ContextException("The thread stopped responding, potentially due to a fatal error or calling exit", 0, $e);
                }
                throw new SynchronizationError(\sprintf('Thread unexpectedly exited with result of type: %s', \is_object($data) ? \get_class($data) : \gettype($data)), 0, $e);
            }
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
