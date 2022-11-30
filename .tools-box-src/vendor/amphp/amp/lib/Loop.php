<?php

namespace _HumbugBoxb47773b41c19\Amp;

use _HumbugBoxb47773b41c19\Amp\Loop\Driver;
use _HumbugBoxb47773b41c19\Amp\Loop\DriverFactory;
use _HumbugBoxb47773b41c19\Amp\Loop\InvalidWatcherError;
use _HumbugBoxb47773b41c19\Amp\Loop\UnsupportedFeatureException;
use _HumbugBoxb47773b41c19\Amp\Loop\Watcher;
final class Loop
{
    private static $driver;
    private function __construct()
    {
    }
    public static function set(Driver $driver)
    {
        try {
            self::$driver = new class extends Driver
            {
                protected function activate(array $watchers)
                {
                    throw new \Error("Can't activate watcher during garbage collection.");
                }
                protected function dispatch(bool $blocking)
                {
                    throw new \Error("Can't dispatch during garbage collection.");
                }
                protected function deactivate(Watcher $watcher)
                {
                }
                public function getHandle()
                {
                    return null;
                }
            };
            \gc_collect_cycles();
        } finally {
            self::$driver = $driver;
        }
    }
    public static function run(callable $callback = null)
    {
        if ($callback) {
            self::$driver->defer($callback);
        }
        self::$driver->run();
    }
    public static function stop()
    {
        self::$driver->stop();
    }
    public static function defer(callable $callback, $data = null) : string
    {
        return self::$driver->defer($callback, $data);
    }
    public static function delay(int $delay, callable $callback, $data = null) : string
    {
        return self::$driver->delay($delay, $callback, $data);
    }
    public static function repeat(int $interval, callable $callback, $data = null) : string
    {
        return self::$driver->repeat($interval, $callback, $data);
    }
    public static function onReadable($stream, callable $callback, $data = null) : string
    {
        return self::$driver->onReadable($stream, $callback, $data);
    }
    public static function onWritable($stream, callable $callback, $data = null) : string
    {
        return self::$driver->onWritable($stream, $callback, $data);
    }
    public static function onSignal(int $signo, callable $callback, $data = null) : string
    {
        return self::$driver->onSignal($signo, $callback, $data);
    }
    public static function enable(string $watcherId)
    {
        self::$driver->enable($watcherId);
    }
    public static function disable(string $watcherId)
    {
        if (\PHP_VERSION_ID < 70200 && !isset(self::$driver)) {
            return;
        }
        self::$driver->disable($watcherId);
    }
    public static function cancel(string $watcherId)
    {
        if (\PHP_VERSION_ID < 70200 && !isset(self::$driver)) {
            return;
        }
        self::$driver->cancel($watcherId);
    }
    public static function reference(string $watcherId)
    {
        self::$driver->reference($watcherId);
    }
    public static function unreference(string $watcherId)
    {
        if (\PHP_VERSION_ID < 70200 && !isset(self::$driver)) {
            return;
        }
        self::$driver->unreference($watcherId);
    }
    public static function now() : int
    {
        return self::$driver->now();
    }
    public static function setState(string $key, $value)
    {
        self::$driver->setState($key, $value);
    }
    public static function getState(string $key)
    {
        return self::$driver->getState($key);
    }
    public static function setErrorHandler(callable $callback = null)
    {
        return self::$driver->setErrorHandler($callback);
    }
    public static function getInfo() : array
    {
        return self::$driver->getInfo();
    }
    public static function get() : Driver
    {
        return self::$driver;
    }
}
Loop::set((new DriverFactory())->create());
