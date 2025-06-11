<?php

namespace TimeoutSkipped;

class SourceClass
{
    public function add(int $a, int $b): int
    {
        sleep(2);
        return $a + $b;
    }
}
