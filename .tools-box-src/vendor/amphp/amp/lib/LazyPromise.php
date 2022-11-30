<?php

namespace _HumbugBoxb47773b41c19\Amp;

final class LazyPromise implements Promise
{
    private $promisor;
    private $promise;
    public function __construct(callable $promisor)
    {
        $this->promisor = $promisor;
    }
    public function onResolve(callable $onResolved)
    {
        if ($this->promise === null) {
            \assert($this->promisor !== null);
            $provider = $this->promisor;
            $this->promisor = null;
            $this->promise = call($provider);
        }
        \assert($this->promise !== null);
        $this->promise->onResolve($onResolved);
    }
}
