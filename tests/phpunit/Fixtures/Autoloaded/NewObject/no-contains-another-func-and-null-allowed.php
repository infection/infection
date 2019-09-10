<?php

namespace NewObject_ContainsAnotherFunctionAndNullAllowed;

class Test
{
    function test()
    {
        $a = function ($element) : ?\stdClass {
            return $element;
        };

        return new \stdClass();
    }
}