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

namespace Infection\Configuration\SourceSymbol;

use function Safe\preg_match;
use function sprintf;
use function str_contains;
use function str_ends_with;
use function str_starts_with;
use Webmozart\Assert\Assert;

/**
 * @internal
 */
final class SourceSymbolSelectorParser
{
    // PHP identifier naming rules: https://www.php.net/manual/en/language.oop5.basic.php
    private const string CLASS_NAME = '[A-Za-z_\x80-\xff][A-Za-z\d_\x80-\xff]*(?:\\\\[A-Za-z_\x80-\xff][A-Za-z\d_\x80-\xff]*)*';

    // PHP function naming rules also apply to methods: https://www.php.net/manual/en/functions.user-defined.php
    private const string METHOD_NAME = '[A-Za-z_\x80-\xff][A-Za-z\d_\x80-\xff]*';

    private const string LINE_PATTERN = '/^[1-9]\d*$/D';

    private const string SELECTOR_PATTERN = <<<'REGEX'
        {^
            (?<className>\\?%s\\?)             # Class name or namespace prefix
            (?:::(?<coordinate>%s|[1-9]\d*))?  # Optional method name or source line
            (?:::(?<line>[1-9]\d*))?           # Optional source line after a method name
        $}xD
        REGEX;

    /**
     * Parses the first source-selector grammar without consulting the autoloader.
     */
    public function parse(string $value): ?SourceSymbolSelector
    {
        $matches = self::matchSelector($value);

        if ($matches === null) {
            return null;
        }

        [
            'className' => $className,
            'coordinate' => $coordinate,
            'line' => $line,
        ] = $matches;

        if (
            $coordinate !== ''
            && preg_match(self::LINE_PATTERN, $coordinate) === 1
        ) {
            if ($line !== '') {
                throw InvalidSourceSymbolSelector::create($value);
            }

            return new SourceSymbolSelector(
                $className,
                null,
                self::parseLine($coordinate, $value),
            );
        }

        return new SourceSymbolSelector(
            $className,
            $coordinate === '' ? null : $coordinate,
            self::tryParseLine($line, $value),
        );
    }

    /**
     * @return array{className: string, coordinate: string, line: string}|null
     */
    private static function matchSelector(string $value): ?array
    {
        $pattern = sprintf(
            self::SELECTOR_PATTERN,
            self::CLASS_NAME,
            self::METHOD_NAME,
        );
        $matches = [];

        if (preg_match($pattern, $value, $matches) !== 1) {
            if (self::looksLikeExplicitSymbolSelector($value)) {
                throw InvalidSourceSymbolSelector::create($value);
            }

            return null;
        }

        Assert::isArray(
            $matches,
            'A successful selector match must provide captured groups.',
        );
        Assert::keyExists(
            $matches,
            'className',
            'A successful selector match must capture the class name.',
        );

        return [
            'className' => $matches['className'],
            'coordinate' => $matches['coordinate'] ?? '',
            'line' => $matches['line'] ?? '',
        ];
    }

    private static function looksLikeExplicitSymbolSelector(string $value): bool
    {
        return str_starts_with($value, '\\')
            || str_ends_with($value, '\\')
            || str_contains($value, '::');
    }

    /**
     * @return positive-int|null
     */
    private static function tryParseLine(
        string $line,
        string $value,
    ): ?int {
        return $line === ''
            ? null
            : self::parseLine(
                $line,
                $value,
            );
    }

    /**
     * @return positive-int
     */
    private static function parseLine(
        string $line,
        string $value,
    ): int {
        $parsedLine = (int) $line;
        $lineOverflowsInteger = (string) $parsedLine !== $line;

        if ($lineOverflowsInteger) {
            throw InvalidSourceSymbolSelector::lineOutOfRange($value);
        }

        Assert::positiveInteger(
            $parsedLine,
            'The line matched by the selector pattern must be a positive integer.',
        );

        return $parsedLine;
    }
}
