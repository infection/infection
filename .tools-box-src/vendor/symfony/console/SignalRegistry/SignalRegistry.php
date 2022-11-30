<?php

namespace _HumbugBoxb47773b41c19\Symfony\Component\Console\SignalRegistry;

final class SignalRegistry
{
    private array $signalHandlers = [];
    public function __construct()
    {
        if (\function_exists('pcntl_async_signals')) {
            \pcntl_async_signals(\true);
        }
    }
    public function register(int $signal, callable $signalHandler) : void
    {
        if (!isset($this->signalHandlers[$signal])) {
            $previousCallback = \pcntl_signal_get_handler($signal);
            if (\is_callable($previousCallback)) {
                $this->signalHandlers[$signal][] = $previousCallback;
            }
        }
        $this->signalHandlers[$signal][] = $signalHandler;
        \pcntl_signal($signal, $this->handle(...));
    }
    public static function isSupported() : bool
    {
        return \function_exists('pcntl_signal');
    }
    public function handle(int $signal) : void
    {
        $count = \count($this->signalHandlers[$signal]);
        foreach ($this->signalHandlers[$signal] as $i => $signalHandler) {
            $hasNext = $i !== $count - 1;
            $signalHandler($signal, $hasNext);
        }
    }
}
