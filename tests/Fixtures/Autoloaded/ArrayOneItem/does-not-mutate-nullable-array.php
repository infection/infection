<?php

namespace ArrayOneItem_NullableArray;

class Test
{
    public function getCollection(): ?array
    {
        $collection = [1, 2, 3];

        return $collection;
    }
}
