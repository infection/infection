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

namespace Infection\TestFramework\PhpUnit\CommandLine;

use Infection\CannotBeInstantiated;
use function method_exists;
use function Safe\preg_match;
use function sprintf;
use function str_replace;
use function stripcslashes;
use function strlen;
use function substr;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\StringInput;
use function trim;

/**
 * @internal
 */
final readonly class TestFrameworkExtraArgs
{
    use CannotBeInstantiated;

    private const string REGEX_UNQUOTED_STRING = '([^\s\\\\]+?)';

    private const string REGEX_QUOTED_STRING = '(?:"([^"\\\\]*(?:\\\\.[^"\\\\]*)*)"|\'([^\'\\\\]*(?:\\\\.[^\'\\\\]*)*)\')';

    private const int PARSE_ERROR_PREVIEW_LENGTH = 10;

    /**
     * @return list<string>
     */
    public static function parseRawTokens(?string $value): array
    {
        $value = trim($value ?? '');

        if ($value === '') {
            return [];
        }

        try {
            $tokens = self::getRawTokens($value);
        } catch (InvalidArgumentException $exception) {
            throw new InvalidArgumentException(
                'Could not parse `testFrameworkExtraArgs` / `--test-framework-extra-args`.',
                $exception->getCode(),
                previous: $exception,
            );
        }

        return $tokens;
    }

    /**
     * @return list<string>
     */
    private static function getRawTokens(string $value): array
    {
        // TODO: this tokenize fallback can be dropped once we drop support for
        //   Symfony 6.4.
        // @phpstan-ignore function.alreadyNarrowedType
        return method_exists(StringInput::class, 'getRawTokens')
            ? (new StringInput($value))->getRawTokens()
            : self::tokenize($value);
    }

    /**
     * @return list<string>
     */
    private static function tokenize(string $input): array
    {
        $tokens = [];
        $length = strlen($input);
        $cursor = 0;
        $token = null;

        while ($cursor < $length) {
            if ($input[$cursor] === '\\') {
                $token .= $input[++$cursor] ?? '';
                ++$cursor;

                continue;
            }

            if (preg_match('/\s+/A', $input, $match, 0, $cursor) === 1) {
                if ($token !== null) {
                    $tokens[] = $token;
                    $token = null;
                }
            } elseif (preg_match('/([^="\'\s]+?)(=?)(' . self::REGEX_QUOTED_STRING . '+)/A', $input, $match, 0, $cursor) === 1) {
                $token .= $match[1] . $match[2] . stripcslashes(
                    str_replace(
                        ['"\'', '\'"', '\'\'', '""'],
                        '',
                        substr($match[3], 1, -1),
                    ),
                );
            } elseif (preg_match('/' . self::REGEX_QUOTED_STRING . '/A', $input, $match, 0, $cursor) === 1) {
                $token .= stripcslashes(
                    substr($match[0], 1, -1),
                );
            } elseif (preg_match('/' . self::REGEX_UNQUOTED_STRING . '/A', $input, $match, 0, $cursor) === 1) {
                $token .= $match[1];
            } else {
                throw new InvalidArgumentException(
                    sprintf(
                        'Unable to parse input near "... %s ...".',
                        substr($input, $cursor, self::PARSE_ERROR_PREVIEW_LENGTH),
                    ),
                );
            }

            $cursor += strlen($match[0]);
        }

        if ($token !== null) {
            $tokens[] = $token;
        }

        return $tokens;
    }
}
