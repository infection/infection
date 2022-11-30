<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Resource\Time;

use function trim;
final class TimeFormatter
{
    private const TIME_HORIZONS = ['h' => 3600, 'm' => 60, 's' => 1];
    public function toHumanReadableString(float $seconds) : string
    {
        if ($seconds < 1) {
            return '0s';
        }
        $resultString = '';
        foreach (self::TIME_HORIZONS as $unit => $unitValue) {
            $intQuotient = (int) ($seconds / $unitValue);
            if ($intQuotient !== 0) {
                $resultString .= $intQuotient . $unit . ' ';
            }
            $seconds -= $unitValue * $intQuotient;
        }
        return trim($resultString);
    }
}
