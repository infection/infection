<?php

namespace NewObject_MutatesWithoutTypehint;

use stdClass;

class Test
{
    function test()
    {
        return new stdClass();
    }
}
