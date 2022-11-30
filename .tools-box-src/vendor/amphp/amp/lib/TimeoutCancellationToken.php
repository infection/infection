<?php

namespace _HumbugBoxb47773b41c19\Amp;

use function _HumbugBoxb47773b41c19\Amp\Internal\formatStacktrace;
final class TimeoutCancellationToken implements CancellationToken
{
    private $watcher;
    private $token;
    public function __construct(int $timeout, string $message = "Operation timed out")
    {
        $source = new CancellationTokenSource();
        $this->token = $source->getToken();
        $trace = \debug_backtrace(\DEBUG_BACKTRACE_IGNORE_ARGS);
        $this->watcher = Loop::delay($timeout, static function () use($source, $message, $trace) {
            $trace = formatStacktrace($trace);
            $source->cancel(new TimeoutException("{$message}\r\nTimeoutCancellationToken was created here:\r\n{$trace}"));
        });
        Loop::unreference($this->watcher);
    }
    public function __destruct()
    {
        Loop::cancel($this->watcher);
    }
    public function subscribe(callable $callback) : string
    {
        return $this->token->subscribe($callback);
    }
    public function unsubscribe(string $id)
    {
        $this->token->unsubscribe($id);
    }
    public function isRequested() : bool
    {
        return $this->token->isRequested();
    }
    public function throwIfRequested()
    {
        $this->token->throwIfRequested();
    }
}
