<?php

namespace NewObject_ReturnTypehintFqcnDoesNotAllowNull;

use stdClass;

class Test
{
    function test() : stdClass
    {
        return new stdClass();
    }
}