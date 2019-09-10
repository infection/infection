<?php

namespace FunctionCall_ReturnTypehintFqcnDoesNotAllowNull;

class Test
{
    function test() : \DateTime
    {
        return count([]);
    }
}