<?php

namespace FunctionCall_ReturnTypehintFqcnAllowsNull;

use DateTime;
class Test
{
    function test() : ?DateTime
    {
        return count([]);
    }
}