<?php

namespace InfectionReflectionPartOfSignature;

class Test
{
    public function foo(int $param, $test = 2.0): bool
    {
        return count([]) === 1;
    }
}