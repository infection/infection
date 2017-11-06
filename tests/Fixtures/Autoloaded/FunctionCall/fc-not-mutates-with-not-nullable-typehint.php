<?php

namespace FunctionCall_NotMutatesWithNotNullableTypehint;

class Test
{
    function test(): bool
    {
        return count([]);
    }
}