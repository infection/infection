<?php

namespace Infection\E2ETests\Turbocov\Lib;

class Counter
{
    public function next(int $current): int
    {
        return $current + 1;
    }
}
