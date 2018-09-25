<?php declare(strict_types=1);

namespace MultilineStatement;

class Calculator
{
    public function calculateInMultipleLines(
        float $value, // must be a new line here
        float $divider = 100,
        float $multiplier = 100
    ): float
    {
        $result = $value // must be a new line here
            / $divider // and here too
            * $multiplier;

        return $result;
    }

    public function calculateInSingleLine(float $value, float $divider = 100, float $multiplier = 100): float
    {
        $result = $value / $divider * $multiplier;

        return $result;
    }
}
