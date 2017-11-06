<?php

namespace ProtectedVisibilityStatic;

class Test
{
    protected static function foo(int $param, $test = 1): bool
    {
        echo 1;
        return false;
    }
}