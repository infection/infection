<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Metrics;

use function _HumbugBox9658796bb9f0\Safe\sprintf;
use UnexpectedValueException;
final class MinMsiCheckFailed extends UnexpectedValueException
{
    public static function createForMsi(float $minMsi, float $msi) : self
    {
        return new self(sprintf('The minimum required MSI percentage should be %s%%, but actual is %s%%. ' . 'Improve your tests!', $minMsi, $msi));
    }
    public static function createCoveredMsi(float $minMsi, float $coveredCodeMsi) : self
    {
        return new self(sprintf('The minimum required Covered Code MSI percentage should be %s%%, but actual is ' . '%s%%. Improve your tests!', $minMsi, $coveredCodeMsi));
    }
}
