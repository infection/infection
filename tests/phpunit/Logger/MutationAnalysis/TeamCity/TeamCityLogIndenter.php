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

namespace Infection\Tests\Logger\MutationAnalysis\TeamCity;

use function explode;
use function implode;
use Infection\CannotBeInstantiated;
use function max;
use function Safe\preg_match;
use function str_ends_with;
use function str_repeat;

/**
 * This service is a utility to make the TeamCity logs more readable by indenting
 * them based on the opening/closing blocks.
 *
 * Note that this is purely for testing purposes for better readability: teamcity
 * logs do not need to be indented.
 */
final class TeamCityLogIndenter
{
    use CannotBeInstantiated;

    private const OPENING_SUFFIXES = ['Started', 'Opened', 'testSuiteStarted', 'testStarted', 'flowStarted'];

    private const CLOSING_SUFFIXES = ['Finished', 'Closed', 'testSuiteFinished', 'testFinished', 'flowFinished'];

    private const MESSAGE_NAME_REGEX = '/^##teamcity\[(?<messageName>\p{L}+)/';

    private const INDENT = '    ';

    public static function indent(string $logs): string
    {
        return self::indentLines(explode("\n", $logs));
    }

    /**
     * @param string[] $lines
     */
    private static function indentLines(
        array $lines,
    ): string {
        $indentedLines = [];
        $indent = 0;

        foreach ($lines as $line) {
            $messageName = self::getMessageName($line);

            if ($messageName !== null && self::isClosingMessage($messageName)) {
                $indent = max(0, $indent - 1);
            }

            $indentedLines[] = self::indentLine($line, $indent);

            if ($messageName !== null && self::isOpeningMessage($messageName)) {
                ++$indent;
            }
        }

        return implode("\n", $indentedLines);
    }

    private static function indentLine(string $line, int $indent): string
    {
        $indent = $line === ''
            ? ''
            : str_repeat(self::INDENT, $indent);

        return $indent . $line;
    }

    private static function getMessageName(string $line): ?string
    {
        if (preg_match(self::MESSAGE_NAME_REGEX, $line, $matches) === 1) {
            // @phpstan-ignore offsetAccess.notFound
            return $matches['messageName'];
        }

        return null;
    }

    private static function isOpeningMessage(string $messageName): bool
    {
        foreach (self::OPENING_SUFFIXES as $suffix) {
            if (str_ends_with($messageName, $suffix)) {
                return true;
            }
        }

        return false;
    }

    private static function isClosingMessage(string $messageName): bool
    {
        foreach (self::CLOSING_SUFFIXES as $suffix) {
            if (str_ends_with($messageName, $suffix)) {
                return true;
            }
        }

        return false;
    }
}
