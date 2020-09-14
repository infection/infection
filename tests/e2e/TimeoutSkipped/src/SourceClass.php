<?php

namespace TimeoutSkipped;

class SourceClass
{
    public function add(int $a, int $b): int
    {
        sleep(1);
        return $a + $b;
    }
}
