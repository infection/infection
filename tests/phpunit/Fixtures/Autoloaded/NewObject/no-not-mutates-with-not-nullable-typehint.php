<?php

namespace NewObject_NotMutatesWithNotNullableTypehint;

use stdClass;

class Test
{
    function test() : stdClass
    {
        return new stdClass();
    }
}