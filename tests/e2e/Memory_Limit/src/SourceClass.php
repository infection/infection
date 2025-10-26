<?php

namespace MemoryLimit;

class SourceClass
{
    public function count(): int
    {
        $result = [];

        $condition = false;

        do {
            $result[] = new \SplFixedArray(1<<22);
        } while ($condition);

        return count($result);
    }
}
