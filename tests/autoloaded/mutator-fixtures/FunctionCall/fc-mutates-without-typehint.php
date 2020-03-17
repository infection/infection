<?php

namespace FunctionCall_MutatesWithoutTypehint;

class Test
{
    function test()
    {
        return count([]);
    }
}
