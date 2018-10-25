<?php

namespace ProtectedVisibilityStatic;

class Test
{
    private static function foo(int $param, $test = 1) : bool
    {
        echo 1;
        return false;
    }
}
