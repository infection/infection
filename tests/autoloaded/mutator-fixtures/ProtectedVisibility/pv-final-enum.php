<?php

namespace ProtectedVisibilityFinal;

enum TestEnum
{
    protected function &foo(int $param, $test = 1) : bool
    {
        echo 1;
        return false;
    }
}
