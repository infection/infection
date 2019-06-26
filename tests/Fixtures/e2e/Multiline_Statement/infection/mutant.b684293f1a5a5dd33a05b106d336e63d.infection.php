<?php

declare (strict_types=1);
namespace MultilineStatement;

class Calculator
{
    public function calculateInMultipleLines(float $value, float $divider = 99, float $multiplier = 100) : float
    {
        $result = $value / $divider * $multiplier;
        return $result;
    }
    public function calculateInSingleLine(float $value, float $divider = 100, float $multiplier = 100) : float
    {
        $result = $value / $divider * $multiplier;
        return $result;
    }
}