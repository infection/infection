<?php

namespace FunctionCall_ScalarReturnTypehintAllowsNull;

class Test
{
    function test() : ?int
    {
        return count([]);
    }
}