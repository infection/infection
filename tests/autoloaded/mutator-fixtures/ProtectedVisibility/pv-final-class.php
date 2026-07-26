<?php

namespace ProtectedVisibilityFinal;

final class FinalTest
{
    protected function foo(int $param, $test = 1) : bool
    {
        echo 1;
        return false;
    }
}
