<?php

namespace ArrayOneItem_FunctionCall;

class Test
{
    public function getCollection(): array
    {
        return foo();
    }
}
