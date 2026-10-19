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

use const PREG_UNMATCHED_AS_NULL;
use function Safe\preg_match;
use function str_contains;
use function str_ends_with;
use function str_starts_with;
use Webmozart\Assert\Assert;

/**
 * Parses source-symbol selectors without consulting the autoloader.
 *
 * @internal
 */
final class SourceSymbolSelectorParser
{
    // PHP identifier naming rules apply to namespaces, classes, and methods:
    // https://www.php.net/manual/en/language.variables.basics.php
    private const string SELECTOR_PATTERN = <<<'REGEX'
        {^
            (?(DEFINE)(?<identifier>[A-Za-z_\x80-\xff][A-Za-z\d_\x80-\xff]*))
            (?<className>\\?(?&identifier)(?:\\(?&identifier))*\\?)  # Class name or namespace prefix
            (?:::(?<methodName>(?&identifier)))?                      # Optional method name
            (?:::(?<line>[1-9]\d*))?                                  # Optional source line
        $}xD
        REGEX;

    public function parse(string $value): ?SourceSymbolSelector
    {
        $matches = [];

        if (preg_match(self::SELECTOR_PATTERN, $value, $matches, PREG_UNMATCHED_AS_NULL) !== 1) {
            return self::rejectExplicitSymbolSelector($value);
        }

        $className = $matches['className'] ?? null;
        Assert::string($className, 'A successful selector match must capture the class name.');

        return new SourceSymbolSelector(
            $className,
            $matches['methodName'] ?? null,
            self::parseLine($matches['line'] ?? null, $value),
        );
    }

    private static function rejectExplicitSymbolSelector(string $value): null
    {
        if (
            str_starts_with($value, '\\')
            || str_ends_with($value, '\\')
            || str_contains($value, '::')
        ) {
            throw InvalidSourceSymbolSelector::create($value);
        }

        return null;
    }

    /**
     * @return positive-int|null
     */
    private static function parseLine(?string $line, string $value): ?int
    {
        if ($line === null) {
            return null;
        }

        $parsedLine = (int) $line;

        if ((string) $parsedLine !== $line) {
            throw InvalidSourceSymbolSelector::lineOutOfRange($value);
        }

        Assert::positiveInteger(
            $parsedLine,
            'The line matched by the selector pattern must be a positive integer.',
        );

        return $parsedLine;
    }
}
