<?php

namespace FunctionCall_ContainsAnotherFunctionAndNullAllowed;

class Test
{
    function test()
    {
        $a = function ($element) : ?int {
            return $element;
        };

        return count([]);
    }
}