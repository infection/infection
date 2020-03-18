<?php

namespace InfectionReflectionPlainFunctionInClass;

class Test
{
    public function foo()
    {
        function bar() {
            count([]);
        }
    }
}