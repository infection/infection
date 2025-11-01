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
use function array_slice;
use function count;
use function explode;
use function implode;
use Infection\CannotBeInstantiated;
use LogicException;
use const PHP_EOL;
use function Safe\mb_convert_encoding;
use function strtr;

/**
 * @internal
 */
final class Str
{
    use CannotBeInstantiated;

    private const SYSTEM_LINE_ENDINGS_REPLACEMENT = [
        "\r\n" => PHP_EOL,
        "\n" => PHP_EOL,
        "\r" => PHP_EOL,
    ];

    private const UNIX_LINE_ENDINGS_REPLACEMENT = [
        "\r\n" => "\n",
        "\r" => "\n",
    ];

    /**
     * @psalm-suppress InvalidReturnStatement,InvalidReturnType
     */
    public static function toSystemLineEndings(string $value): string
    {
        return strtr(
            $value,
            self::SYSTEM_LINE_ENDINGS_REPLACEMENT,
        );
    }

    /**
     * @psalm-suppress InvalidReturnStatement,InvalidReturnType
     */
    public static function toUnixLineEndings(string $value): string
    {
        return strtr(
            $value,
            self::UNIX_LINE_ENDINGS_REPLACEMENT,
        );
    }

    /**
     * Trim the whitespace from the end of all lines. The line endings are
     * replaced by the unix line ending.
     */
    public static function rTrimLines(string $value): string
    {
        return implode(
            "\n",
            self::splitIntoRTrimmedLines($value),
        );
    }

    /**
     * Trims the whitespace from the end of all lines and removes the leading and trailing blank lines. Line
     * endings are replaced by the unix line endings.
     */
    public static function cleanForDisplay(string $value): string
    {
        $lines = self::splitIntoRTrimmedLines($value);

        $firstEmptyBlankLineIndex = self::findFirstNonEmptyLineIndex($lines);

        if ($firstEmptyBlankLineIndex === null) {
            return '';  // All lines are blank
        }

        $lastNonEmptyLineIndex = self::findLastNonEmptyLineIndex(
            $lines,
            $firstEmptyBlankLineIndex,
        );

        return implode(
            "\n",
            array_slice(
                $lines,
                $firstEmptyBlankLineIndex,
                $lastNonEmptyLineIndex - $firstEmptyBlankLineIndex + 1,
            ),
        );
    }

    public static function convertToUtf8(string $string): string
    {
        /** @var string $utf8String */
        $utf8String = mb_convert_encoding($string, 'UTF-8', 'UTF-8');

        return $utf8String;
    }

    /**
     * @return list<string>
     */
    private static function splitIntoRTrimmedLines(string $value): array
    {
        return array_map(
            rtrim(...),
            explode(
                "\n",
                self::toUnixLineEndings($value),
            ),
        );
    }

    /**
     * @param list<string> $lines
     *
     * @return int<0,max>|null
     */
    private static function findFirstNonEmptyLineIndex(array $lines): ?int
    {
        foreach ($lines as $index => $line) {
            if ($line !== '') {
                return $index;
            }
        }

        return null;
    }

    /**
     * @psalm-suppress InvalidArrayOffset,InvalidReturnStatement,InvalidReturnType
     *
     * @param list<string> $lines
     * @param int<0,max> $firstNonEmptyLineIndex
     *
     * @return int<0,max>
     */
    private static function findLastNonEmptyLineIndex(
        array $lines,
        int $firstNonEmptyLineIndex,
    ): int {
        $linesCount = count($lines);

        for ($index = $linesCount - 1; $index >= $firstNonEmptyLineIndex; --$index) {
            if ($lines[$index] !== '') {
                return $index;
            }
        }

        throw new LogicException('This should never happen!');
    }
}
