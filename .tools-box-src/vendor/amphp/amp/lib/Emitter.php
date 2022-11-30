<?php

namespace _HumbugBoxb47773b41c19\Amp;

/**
@template
*/
final class Emitter
{
    private $emitter;
    private $iterator;
    public function __construct()
    {
        $this->emitter = new class implements Iterator
        {
            use Internal\Producer {
                emit as public;
                complete as public;
                fail as public;
            }
        };
        $this->iterator = new Internal\PrivateIterator($this->emitter);
    }
    /**
    @psalm-return
    */
    public function iterate() : Iterator
    {
        return $this->iterator;
    }
    /**
    @psalm-param
    @psalm-return
    @psalm-suppress
    @psalm-suppress
    */
    public function emit($value) : Promise
    {
        /**
        @psalm-suppress */
        return $this->emitter->emit($value);
    }
    public function complete()
    {
        /**
        @psalm-suppress */
        $this->emitter->complete();
    }
    public function fail(\Throwable $reason)
    {
        /**
        @psalm-suppress */
        $this->emitter->fail($reason);
    }
}
