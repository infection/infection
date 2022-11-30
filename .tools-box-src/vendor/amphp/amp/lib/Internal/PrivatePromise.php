<?php

namespace _HumbugBoxb47773b41c19\Amp\Internal;

use _HumbugBoxb47773b41c19\Amp\Promise;
final class PrivatePromise implements Promise
{
    private $promise;
    public function __construct(Promise $promise)
    {
        $this->promise = $promise;
    }
    public function onResolve(callable $onResolved)
    {
        $this->promise->onResolve($onResolved);
    }
}
