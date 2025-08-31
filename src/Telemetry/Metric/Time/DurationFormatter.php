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
use function number_format;
use function round;
use function trim;

/**
 * @internal
 */
final class DurationFormatter
{
    private const TIME_HORIZONS = [
        'min' => 60,
        's' => 1,
        'ms' => 0.001,
    ];

    /**
     * Formats time in seconds to a more human-friendly format.
     */
    public function toHumanReadableString(Duration $duration): string
    {
        $totalSeconds = $duration->seconds + ($duration->nanoseconds / 1_000_000_000);

        if ($totalSeconds >= 60) {
            $minutes = $totalSeconds / 60;
            if ($minutes == (int) $minutes) {
                return (int) $minutes . 'min';
            }

            return number_format($minutes, 1) . 'min';
        }

        if ($totalSeconds >= 1) {
            $roundedSeconds = round($totalSeconds);
            if ($roundedSeconds == $totalSeconds || abs($totalSeconds - $roundedSeconds) < 0.001) {
                return (int) $roundedSeconds . 's';
            }

            return number_format($totalSeconds, 1) . 's';
        }

        if ($totalSeconds == 0) {
            return '0ms';
        }

        $totalMs = $totalSeconds * 1000;

        if ($totalMs < 1) {
            return '>1ms';
        }

        if ($totalMs >= 1.05 && $totalMs < 1.5) {
            return '1ms';
        }

        if ($totalMs == floor($totalMs)) {
            return (int) $totalMs . 'ms';
        }

        if (abs($totalMs - round($totalMs * 10) / 10) < 0.001) {
            return number_format($totalMs, 1) . 'ms';
        }

        return (int) round($totalMs) . 'ms';
    }
}
