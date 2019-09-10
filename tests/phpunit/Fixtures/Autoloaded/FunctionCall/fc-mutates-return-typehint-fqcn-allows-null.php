<?php

namespace FunctionCall_ReturnTypehintFqcnAllowsNull;

class Test
{
    function test() : ?\DateTime
    {
        return count([]);
    }
}