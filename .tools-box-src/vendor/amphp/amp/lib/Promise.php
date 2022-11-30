<?php

namespace _HumbugBoxb47773b41c19\Amp;

/**
@template-covariant
@psalm-yield
*/
interface Promise
{
    /**
    @psalm-param
    */
    public function onResolve(callable $onResolved);
}
