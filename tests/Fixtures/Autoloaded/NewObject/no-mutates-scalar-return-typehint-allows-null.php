<?php

namespace NewObject_ScalarReturnTypehintsAllowsNull;

class Test
{
    function test() : ?int
    {
        return new \stdClass();
    }
}