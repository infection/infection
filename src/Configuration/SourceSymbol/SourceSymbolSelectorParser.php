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
use function str_contains;
use function str_starts_with;
use function substr;
use Webmozart\Assert\Assert;

/**
 * @internal
 */
final class SourceSymbolSelectorParser
{
    private const string CLASS_NAME = '[A-Za-z_\x80-\xff][A-Za-z0-9_\x80-\xff]*(?:\\\\[A-Za-z_\x80-\xff][A-Za-z0-9_\x80-\xff]*)*';

    private const string METHOD_NAME = '[A-Za-z_\x80-\xff][A-Za-z0-9_\x80-\xff]*';

    private const string SELECTOR_PATTERN = '/^(?<class>'
        . self::CLASS_NAME
        . ')(?:::(?<coordinate>'
        . self::METHOD_NAME
        . '|[1-9][0-9]*))?(?:::(?<line>[1-9][0-9]*))?$/D';

    /**
     * Parses the first source-selector grammar without consulting the autoloader.
     *
     * @throws InvalidSourceSymbolSelector
     */
    public function parse(string $value): ?SourceSymbolSelector
    {
        // Existing files win before this parser is called, so Windows paths such as
        // C:\project\src\Foo.php are not confused with source symbols.
        $candidate = str_starts_with($value, '\\')
            ? substr($value, 1)
            : $value;

        $matches = [];

        if (preg_match(self::SELECTOR_PATTERN, $candidate, $matches) !== 1) {
            if (str_starts_with($value, '\\') || str_contains($value, '::')) {
                throw InvalidSourceSymbolSelector::create($value);
            }

            return null;
        }

        Assert::isArray($matches);
        $coordinate = $matches['coordinate'] ?? '';
        $line = $matches['line'] ?? '';
        Assert::keyExists($matches, 'class');

        if ($coordinate !== '' && preg_match('/^[1-9][0-9]*$/D', $coordinate) === 1) {
            if ($line !== '') {
                throw InvalidSourceSymbolSelector::create($value);
            }

            return new SourceSymbolSelector(
                $matches['class'],
                null,
                self::parseLine($coordinate),
            );
        }

        return new SourceSymbolSelector(
            $matches['class'],
            $coordinate === '' ? null : $coordinate,
            $line === '' ? null : self::parseLine($line),
        );
    }

    /**
     * @return positive-int
     */
    private static function parseLine(string $line): int
    {
        $parsedLine = (int) $line;

        Assert::positiveInteger(
            $parsedLine,
            'The line matched by the selector pattern must be a positive integer.',
        );

        return $parsedLine;
    }
}
