<?php

namespace ProtectedVisibilityAbstract;

abstract class Test
{
    protected abstract function foo(int $param, $test = 1) : bool;
}