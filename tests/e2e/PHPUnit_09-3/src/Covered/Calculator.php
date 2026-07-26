<?php

namespace Infection\E2ETests\PHPUnit_09_3\Covered;

class Calculator
{
    public function add(int $a, int $b): int
    {
        return $a + $b;
    }

    public function subtract(int $a, int $b): int
    {
        return $a - $b;
    }

    public function multiply(int $a, int $b): int
    {
        return $a * $b;
    }

    public function divide(int $a, int $b): float
    {
        if ($b === 0) {
            throw new \InvalidArgumentException('Division by zero');
        }

        return $a / $b;
    }

    public function isPositive(int $number): bool
    {
        return $number >= 0;
    }

    public function absolute(int $number): int
    {
        return $number <= 0 ? -$number : $number;
    }
}
