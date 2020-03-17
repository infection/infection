<?php

namespace ArrayOneItem_FunctionVariableCall;

class Test
{
    public function getCollection(): array
    {
        $foo = function () {
            return [];
        };

        return $foo();
    }
}
