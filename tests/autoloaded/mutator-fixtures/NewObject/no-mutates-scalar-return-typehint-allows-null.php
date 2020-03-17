<?php

namespace NewObject_ScalarReturnTypehintsAllowsNull;

use stdClass;

class Test
{
    function test() : ?int
    {
        return new stdClass();
    }
}