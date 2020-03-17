<?php

namespace PublicVisibilityFinal;

class Test
{
    final public function foo(int $param, $test = 1): bool
    {
        echo 1;
        return false;
    }
}