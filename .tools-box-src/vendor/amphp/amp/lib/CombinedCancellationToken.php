<?php

namespace _HumbugBoxb47773b41c19\Amp;

final class CombinedCancellationToken implements CancellationToken
{
    private $tokens = [];
    private $nextId = "a";
    private $callbacks = [];
    private $exception;
    public function __construct(CancellationToken ...$tokens)
    {
        $thatException =& $this->exception;
        $thatCallbacks =& $this->callbacks;
        foreach ($tokens as $token) {
            $id = $token->subscribe(static function (CancelledException $exception) use(&$thatException, &$thatCallbacks) {
                $thatException = $exception;
                $callbacks = $thatCallbacks;
                $thatCallbacks = [];
                foreach ($callbacks as $callback) {
                    asyncCall($callback, $thatException);
                }
            });
            $this->tokens[] = [$token, $id];
        }
    }
    public function __destruct()
    {
        foreach ($this->tokens as list($token, $id)) {
            $token->unsubscribe($id);
        }
    }
    public function subscribe(callable $callback) : string
    {
        $id = $this->nextId++;
        if ($this->exception) {
            asyncCall($callback, $this->exception);
        } else {
            $this->callbacks[$id] = $callback;
        }
        return $id;
    }
    public function unsubscribe(string $id)
    {
        unset($this->callbacks[$id]);
    }
    public function isRequested() : bool
    {
        foreach ($this->tokens as list($token)) {
            if ($token->isRequested()) {
                return \true;
            }
        }
        return \false;
    }
    public function throwIfRequested()
    {
        foreach ($this->tokens as list($token)) {
            $token->throwIfRequested();
        }
    }
}
