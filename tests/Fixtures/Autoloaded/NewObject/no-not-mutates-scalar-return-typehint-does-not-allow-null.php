<?php

namespace NewObject_ScalarReturnTypehintFqcnDoesNotAllowNull;

class Test
{
    function test() : int
    {
        return new \stdClass();
    }
}