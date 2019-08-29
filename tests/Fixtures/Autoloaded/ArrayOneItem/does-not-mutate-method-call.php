<?php

namespace ArrayOneItem_MethodCall;

class Test
{
    public function getCollection(): array
    {
        return $this->foo();
    }
}
