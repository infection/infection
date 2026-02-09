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

use InvalidArgumentException;
use function sprintf;

final readonly class HRTime
{
    private const NANOSECONDS_MAX = 1e9;

    /**
     * @param positive-int|0 $seconds
     * @param int<0, 999999999> $nanoseconds
     */
    private function __construct(
        public int $seconds,
        public int $nanoseconds,
    ) {
        self::assertIsANatural($seconds, 'seconds');
        self::assertIsANatural($nanoseconds, 'nanoseconds');
        self::assertIsValidNanoSeconds($nanoseconds);
    }

    /**
     * @param positive-int|0 $seconds
     * @param int<0, 999999999> $nanoseconds
     */
    public static function fromSecondsAndNanoseconds(int $seconds, int $nanoseconds): self
    {
        return new self(
            $seconds,
            $nanoseconds,
        );
    }

    public function getDuration(self $start): Duration
    {
        $seconds = $this->seconds - $start->seconds;
        $nanoseconds = $this->nanoseconds - $start->nanoseconds;

        if ($nanoseconds < 0) {
            --$seconds;

            $nanoseconds += (int) self::NANOSECONDS_MAX;
        }

        if ($seconds < 0) {
            return Duration::fromSecondsAndNanoseconds(0, 0);
        }

        return Duration::fromSecondsAndNanoseconds(
            $seconds,
            $nanoseconds,
        );
    }

    /**
     * @psalm-assert positive-int|0 $value
     */
    private static function assertIsANatural(int $value, string $type): void
    {
        if ($value < 0) {
            throw new InvalidArgumentException(
                sprintf(
                    'Value for %s must not be negative.',
                    $type,
                ),
            );
        }
    }

    private static function assertIsValidNanoSeconds(int $nanoseconds): void
    {
        if ($nanoseconds >= self::NANOSECONDS_MAX) {
            throw new InvalidArgumentException(
                sprintf(
                    'Value for nanoseconds must not be greater or equal than %s.',
                    self::NANOSECONDS_MAX,
                ),
            );
        }
    }
}
