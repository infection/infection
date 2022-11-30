<?php

namespace _HumbugBoxb47773b41c19\Amp;

final class NullCancellationToken implements CancellationToken
{
    public function subscribe(callable $callback) : string
    {
        return "null-token";
    }
    public function unsubscribe(string $id)
    {
    }
    public function isRequested() : bool
    {
        return \false;
    }
    public function throwIfRequested()
    {
    }
}
