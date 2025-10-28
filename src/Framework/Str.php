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

namespace Infection\Framework;

use function array_map;
use function array_values;
use function count;
use function explode;
use function implode;
use Infection\CannotBeInstantiated;
use const PHP_EOL;
use function preg_replace;
use function Safe\mb_convert_encoding;
use function trim;

/**
 * @internal
 */
final class Str
{
    use CannotBeInstantiated;

    private const LINE_RETURNS_REGEX = '/\r\n|\r|\n/';

    public static function toSystemLineReturn(string $value): string
    {
        return preg_replace(
            self::LINE_RETURNS_REGEX,
            PHP_EOL,
            $value,
        );
    }

    public static function toLinuxLineReturn(string $value): string
    {
        return preg_replace(
            self::LINE_RETURNS_REGEX,
            "\n",
            $value,
        );
    }

    /**
     * Removes trailing spaces and normalizes the line return (to the unix/linux one).
     */
    public static function normalize(string $value): string
    {
        return implode(
            "\n",
            array_map(
                rtrim(...),
                explode(
                    "\n",
                    self::toLinuxLineReturn($value),
                ),
            ),
        );
    }

    public static function trimLineReturns(string $value): string
    {
        $lines = explode(
            "\n",
            self::toLinuxLineReturn($value),
        );

        $trimmedLines = self::removeTrailingBlankLines(
            self::removeLeadingBlankLines($lines),
        );

        return implode(PHP_EOL, $trimmedLines);
    }

    public static function convertToUtf8(string $string): string
    {
        /** @var string $utf8String */
        $utf8String = mb_convert_encoding($string, 'UTF-8', 'UTF-8');

        return $utf8String;
    }

    /**
     * @param list<string> $lines
     *
     * @return list<string>
     */
    private static function removeLeadingBlankLines(array $lines): array
    {
        $linesCount = count($lines);

        for ($index = 0; $index < $linesCount; ++$index) {
            $line = $lines[$index];

            if (trim($line) === '') {
                unset($lines[$index]);

                continue;
            }

            break;
        }

        return array_values($lines);
    }

    /**
     * @param list<string> $lines
     *
     * @return string[]
     */
    private static function removeTrailingBlankLines(array $lines): array
    {
        $linesCount = count($lines);

        for ($index = $linesCount - 1; $index > 0; --$index) {
            $line = $lines[$index];

            if (trim($line) === '') {
                unset($lines[$index]);

                continue;
            }

            break;
        }

        return $lines;
    }
}
