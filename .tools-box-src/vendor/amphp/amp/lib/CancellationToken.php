<?php

namespace _HumbugBoxb47773b41c19\Amp;

interface CancellationToken
{
    public function subscribe(callable $callback) : string;
    public function unsubscribe(string $id);
    public function isRequested() : bool;
    public function throwIfRequested();
}
