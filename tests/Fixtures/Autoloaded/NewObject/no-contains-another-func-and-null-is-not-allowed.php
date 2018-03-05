<?php

namespace NewObject_ContainsAnotherFunctionAndNullIsNotAllowed;

class Test
{
    function test() : \stdClass
    {
        $a = function ($element) : ?\stdClass {
            return $element;
        };
        return new \stdClass();
}
}