<?php

namespace TimeoutSkippedByFastestTest;

class SourceClass
{
    public function add(int $a, int $b): int
    {
        return $a + $b;
    }

    public function isPositive(int $x): bool
    {
        return $x > 0;
    }

    public function decrement(int $x): int
    {
        return $x - 1;
    }
}
