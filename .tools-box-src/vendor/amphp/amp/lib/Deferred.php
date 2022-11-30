<?php

namespace _HumbugBoxb47773b41c19\Amp;

/**
@template
*/
final class Deferred
{
    private $resolver;
    private $promise;
    public function __construct()
    {
        $this->resolver = new class implements Promise
        {
            use Internal\Placeholder {
                resolve as public;
                fail as public;
                isResolved as public;
            }
        };
        $this->promise = new Internal\PrivatePromise($this->resolver);
    }
    public function promise() : Promise
    {
        return $this->promise;
    }
    /**
    @psalm-param
    */
    public function resolve($value = null)
    {
        /**
        @psalm-suppress */
        $this->resolver->resolve($value);
    }
    public function fail(\Throwable $reason)
    {
        /**
        @psalm-suppress */
        $this->resolver->fail($reason);
    }
    public function isResolved() : bool
    {
        return $this->resolver->isResolved();
    }
}
