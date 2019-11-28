<?php

declare(strict_types=1);

namespace Codeception_Basic;

class SourceClass
{
    public function add(float $a, float $b): float
    {
        return $a + $b;
    }
}
