<?php

namespace PublicVisibilityStatic;

class Test
{
    public static function foo(int $param, $test = 1): bool
    {
        echo 1;
        return false;
    }
}