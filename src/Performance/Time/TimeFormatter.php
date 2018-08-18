<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Performance\Time;

/**
 * @internal
 */
final class TimeFormatter
{
    private const TIME_HORIZONS = [
        'h' => 3600,
        'm' => 60,
        's' => 1,
    ];

    public function toHumanReadableString(float $seconds): string
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
