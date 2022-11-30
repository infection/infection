<?php

namespace _HumbugBoxb47773b41c19\Amp;

/**
@template-covariant
@template-implements
*/
final class Delayed implements Promise
{
    use Internal\Placeholder;
    private $watcher;
    public function __construct(int $time, $value = null)
    {
        $this->watcher = Loop::delay($time, function () use($value) {
            $this->watcher = null;
            $this->resolve($value);
        });
    }
    public function reference() : self
    {
        if ($this->watcher !== null) {
            Loop::reference($this->watcher);
        }
        return $this;
    }
    public function unreference() : self
    {
        if ($this->watcher !== null) {
            Loop::unreference($this->watcher);
        }
        return $this;
    }
}
