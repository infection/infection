<?php

namespace Infection\E2ETests\PreloadedSource;

class Calculator
{
    public function add(int $left, int $right): int
    {
        return $left + $right;
    }
}
