<?php

namespace ProtectedVisibilityFinal;

class Test
{
    private final function &foo(int $param, $test = 1) : bool
    {
        echo 1;
        return false;
    }
}
