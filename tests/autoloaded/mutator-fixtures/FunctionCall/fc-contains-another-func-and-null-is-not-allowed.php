<?php

namespace FunctionCall_ContainsAnotherFunctionAndNullIsNotAllowed;

class Test
{
    function test(): int
    {
        $a = function ($element) : ?int {
            return $element;
        };

        return count([]);
    }
}