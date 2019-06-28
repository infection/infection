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

namespace Infection\Tests\AutoReview\PhpDoc;

use function array_column;
use function array_combine;
use function array_filter;
use function array_map;
use function preg_match;
use function rtrim;
use function trim;

/**
 * @internal
 *
 * @group autoReview
 */
final class ClassParser
{
    /**
     * Parses the tags and their values within the class phpdoc.
     *
     * @return array<string,string>
     */
    public static function parseFilePhpDoc(string $contents): array
    {
        $phpDocLines = self::collectPhpDocLines(
            explode(PHP_EOL, $contents)
        );

        $normalizedLines = array_filter(array_map([self::class, 'normalizeLine'], $phpDocLines));

        return array_combine(
            array_column($normalizedLines, 0),
            array_column($normalizedLines, 1)
        );
    }

    /**
     * @param string[] $lines
     *
     * @return string[]
     */
    private static function collectPhpDocLines(array $lines): array
    {
        $phpDocStart = false;
        $phpDocEnd = false;
        $classStatement = false;

        $phpDocLines = [];

        foreach ($lines as $line) {
            if (false === $phpDocStart) {
                if (1 === preg_match('/\/\*\*/', $line)) {
                    $phpDocStart = true;
                    $phpDocLines[] = $line;
                }

                if (1 === preg_match('/\*\//', $line)) {
                    $phpDocEnd = true;
                }

                continue;
            }

            if (false === $phpDocEnd) {
                if (1 === preg_match('/\*\//', $line)) {
                    $phpDocEnd = true;
                    $phpDocLines[] = $line;

                    continue;
                }

                $phpDocLines[] = $line;

                continue;
            }

            if (false === $classStatement) {
                if (1 === preg_match('/class \p{L}+/u', $line)) {
                    break;
                }

                $phpDocStart = false;
                $phpDocEnd = false;
                $phpDocLines = [];
            }

            $x = '';
        }

        return $phpDocLines;
    }

    /**
     * @return array<string|null>
     */
    private static function normalizeLine(string $line): ?array
    {
        $line = trim(rtrim($line, '*/'));

        if (1 !== preg_match('/(?<tag>@\p{L}+)(?: (?<value>.+))?$/u', $line, $matches)) {
            return null;
        }

        $tag = $matches['tag'];
        $value = trim($matches['value'] ?? '');

        return [
            $tag,
            '' === $value ? null : $value,
        ];
    }
}
