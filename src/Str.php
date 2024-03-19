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

namespace Infection;

use function array_values;
use function count;
use function explode;
use function implode;
use const PHP_EOL;
use function Safe\mb_convert_encoding;
use function str_replace;
use function trim;

/**
 * @internal
 */
final class Str
{
    use CannotBeInstantiated;

    public static function trimLineReturns(string $string): string
    {
        $lines = explode(
            "\n",
            str_replace("\r\n", "\n", $string),
        );
        $linesCount = count($lines);

        // Trim leading empty lines
        for ($i = 0; $i < $linesCount; ++$i) {
            $line = $lines[$i];

            if (trim($line) === '') {
                unset($lines[$i]);

                continue;
            }

            break;
        }

        $lines = array_values($lines);
        $linesCount = count($lines);

        // Trim trailing empty lines
        for ($i = $linesCount - 1; $i >= 0; --$i) {
            $line = $lines[$i];

            if (trim($line) === '') {
                unset($lines[$i]);

                continue;
            }

            break;
        }

        return implode(PHP_EOL, $lines);
    }

    public static function convertToUtf8(string $string): string
    {
        /** @var string $utf8String */
        $utf8String = mb_convert_encoding($string, 'UTF-8', 'UTF-8');

        return $utf8String;
    }
}
