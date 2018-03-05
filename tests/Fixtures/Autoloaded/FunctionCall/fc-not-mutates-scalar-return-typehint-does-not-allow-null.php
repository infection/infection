<?php

namespace FunctionCall_ScalarReturnTypehintFqcnDoesNotAllowNull;

class Test
{
    function test() : int
    {
        return count([]);
    }
}