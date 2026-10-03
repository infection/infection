<?php

declare(strict_types=1);

namespace Infection\E2ETests\PHPUnitTIA;

final class Calculator
{
    public function calculate(int $a, int $b): int
    {
        return $a + $b;
    }
}
