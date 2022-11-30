<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\SebastianBergmann\Diff;

interface LongestCommonSubsequenceCalculator
{
    public function calculate(array $from, array $to) : array;
}
