<?php
/**
 * This code is licensed under the BSD 3-Clause License.
 *
 * Copyright (c) 2017, Maks Rafalko
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * * Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 *
 * * Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 * * Neither the name of the copyright holder nor the names of its
 *   contributors may be used to endorse or promote products derived from
 *   this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

declare(strict_types=1);

namespace Infection\Telemetry\Metric\Time;

use function abs;
use function floor;
use function number_format;
use function round;

/**
 * @internal
 */
final class DurationFormatter
{
    private const MILLISECOND_DIVISOR = 0.001;

    private const THRESHOLDS = [
        [
            'threshold' => 60,
            'unit' => 'min',
            'divisor' => 60,
        ],
        [
            'threshold' => 1,
            'unit' => 's',
            'divisor' => 1,
        ],
        [
            'threshold' => 0,
            'unit' => 'ms',
            'divisor' => self::MILLISECOND_DIVISOR,
        ],
    ];

    /**
     * Formats duration to a more human-friendly format.
     */
    public function toHumanReadableString(Duration $duration): string
    {
        $totalSeconds = $duration->toSeconds();

        if ($totalSeconds < self::MILLISECOND_DIVISOR && $totalSeconds > 0) {
            return '>1ms';
        }

        foreach (self::THRESHOLDS as $config) {
            if ($totalSeconds >= $config['threshold']) {
                return self::formatForUnit($totalSeconds, $config);
            }
        }

        return '0ms';
    }

    private static function formatForUnit(float $totalSeconds, array $config): string
    {
        if ($config['unit'] === 'ms') {
            return self::formatMilliseconds($totalSeconds);
        }

        $value = $totalSeconds / $config['divisor'];
        $unit = $config['unit'];

        if (self::shouldDisplayAsWholeNumber($totalSeconds, $value)) {
            return (int) round($value) . $unit;
        }

        return number_format($value, 1) . $unit;
    }

    private static function formatMilliseconds(float $totalSeconds): string
    {
        if ($totalSeconds == 0) {
            return '0ms';
        }

        $totalMs = $totalSeconds * 1000;

        if (self::isInRoundingRange($totalMs, 1.05, 1.5)) {
            return '1ms';
        }

        if (self::isWholeNumber($totalMs)) {
            return (int) $totalMs . 'ms';
        }

        if (self::hasCleanDecimal($totalMs)) {
            return number_format($totalMs, 1) . 'ms';
        }

        return (int) round($totalMs) . 'ms';
    }

    private static function shouldDisplayAsWholeNumber(float $totalSeconds, float $value): bool
    {
        $rounded = round($value);
        $epsilon = abs($totalSeconds - ($rounded * ($totalSeconds / $value)));

        return $rounded == $value
            || $epsilon < self::MILLISECOND_DIVISOR;
    }

    private static function isInRoundingRange(float $value, float $min, float $max): bool
    {
        return $value >= $min && $value < $max;
    }

    private static function isWholeNumber(float $value): bool
    {
        return $value == floor($value);
    }

    private static function hasCleanDecimal(float $value): bool
    {
        $epsilon = abs($value - round($value * 10) / 10);

        return $epsilon < self::MILLISECOND_DIVISOR;
    }
}
