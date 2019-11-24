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

namespace Infection\Tests\AutoReview\ProjectCode;

use function array_filter;
use function array_map;
use function current;
use function end;
use function explode;
use function implode;
use function substr;
use function trim;

class DocBlockParser
{
    public static function parse(string $docblock): string
    {
        $docblock = trim($docblock);

        if ('' === $docblock) {
            return '';
        }

        /** @var string[] $lines */
        $lines = array_map(
            'trim',
            explode("\n", $docblock)
        );

        /** @var string $firstLine */
        $firstLine = current($lines);

        $lines[0] = substr($firstLine, 3);

        $nbrOfLines = \count($lines);

        for ($i = 1; $i < $nbrOfLines; ++$i) {
            $lines[$i] = substr($lines[$i], 1);
        }

        end($lines);

        /** @var string $lastLine */
        $lastLine = current($lines);

        $lines[$nbrOfLines - 1] = substr($lastLine, 0, -2);

        return implode(
            "\n",
            array_filter(
                array_map('trim', $lines)
            )
        );
    }
}
