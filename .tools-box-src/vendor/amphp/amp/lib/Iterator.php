<?php

namespace _HumbugBoxb47773b41c19\Amp;

/**
@template-covariant
*/
interface Iterator
{
    /**
    @psalm-return
    */
    public function advance() : Promise;
    /**
    @psalm-return
    */
    public function getCurrent();
}
