<?php

namespace NewObject_ReturnTypehintFqcnAllowsNull;

use stdClass;

class Test
{
    function test() : ?stdClass
    {
        return new stdClass();
    }
}