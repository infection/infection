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

namespace Infection\Console\Input;

use function count;
use function explode;
use Infection\CannotBeInstantiated;
use function max;
use const PHP_ROUND_HALF_UP;
use function round;
use function sprintf;
use function strlen;
use function trim;
use Webmozart\Assert\Assert;

/**
 * @internal
 */
final class MsiParser
{
    use CannotBeInstantiated;

    public const DEFAULT_PRECISION = 2;
    private const EXPLODE_PARTS = 2;

    public static function detectPrecision(?string ...$values): int
    {
        $precisions = [self::DEFAULT_PRECISION];

        foreach ($values as $value) {
            $value = trim((string) $value);

            if ($value === '') {
                continue;
            }

            $valueParts = explode('.', $value);

            if (count($valueParts) !== self::EXPLODE_PARTS) {
                continue;
            }

            $precisions[] = strlen($valueParts[1]);
        }

        return (int) max($precisions);
    }

    public static function parse(?string $value, int $precision, string $optionName): ?float
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        Assert::numeric(
            $value,
            sprintf('Expected %s to be a float. Got "%s"', $optionName, $value),
        );

        $roundedValue = round((float) $value, $precision, PHP_ROUND_HALF_UP);

        Assert::range(
            $roundedValue,
            0,
            100,
            sprintf('Expected %s to be an element of [0;100]. Got %%s', $optionName),
        );

        return $roundedValue;
    }
}
