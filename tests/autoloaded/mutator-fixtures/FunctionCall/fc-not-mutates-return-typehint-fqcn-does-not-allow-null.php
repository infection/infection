<?php

namespace FunctionCall_ReturnTypehintFqcnDoesNotAllowNull;

use DateTime;

class Test
{
    function test() : DateTime
    {
        return count([]);
    }
}