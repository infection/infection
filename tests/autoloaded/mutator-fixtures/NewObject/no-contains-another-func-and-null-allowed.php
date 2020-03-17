<?php

namespace NewObject_ContainsAnotherFunctionAndNullAllowed;

use stdClass;

class Test
{
    function test()
    {
        $a = function ($element) : ?stdClass {
            return $element;
        };

        return new stdClass();
    }
}