<?php

namespace NewObject_ReturnTypehintFqcnAllowsNull;

class Test
{
    function test() : ?\stdClass
    {
        return new \stdClass();
    }
}