<?php

namespace NewObject_ReturnTypehintFqcnDoesNotAllowNull;

class Test
{
    function test() : \stdClass
    {
        return new \stdClass();
    }
}