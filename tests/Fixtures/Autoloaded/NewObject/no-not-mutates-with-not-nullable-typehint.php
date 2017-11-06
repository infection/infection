<?php

namespace NewObject_NotMutatesWithNotNullableTypehint;

class Test
{
    function test(): \stdClass
    {
        return new \stdClass();
    }
}