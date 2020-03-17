<?php

namespace NewObject_ScalarReturnTypehintFqcnDoesNotAllowNull;

use stdClass;

class Test
{
    function test() : int
    {
        return new stdClass();
    }
}