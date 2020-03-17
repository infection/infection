<?php

namespace PublicVisibilityNonAbstractInAbstractClass;

abstract class Test
{
    public function foo(int $param, $test = 1) : bool
    {
        echo 1;
        return false;
    }
}