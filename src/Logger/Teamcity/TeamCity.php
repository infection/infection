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

namespace Infection\Logger\Teamcity;

use function array_keys;
use function array_map;
use function implode;
use function is_int;
use function preg_replace;
use function Safe\getmypid;
use function Safe\ini_get;
use function sprintf;
use function str_contains;
use function str_replace;
use function stripos;
use Throwable;

/**
 * @see https://www.jetbrains.com/help/teamcity/2025.07/service-messages.html
 *
 * @internal
 */
final class TeamCity
{
    // `|` must be escaped FIRST to avoid double-escaping.
    private const CHARACTERS_TO_ESCAPE = ['|', "'", "\n", "\r", '[', ']'];

    private const ESCAPED_CHARACTERS = ['||', "|'", '|n', '|r', '|[', '|]'];

    private const UNICODE_CHARACTER_REGEX = '/\\\\u(?<hexadecimalDigits>[0-9A-Fa-f]{4})/';

    private ?int $flowId = null;

    public function __construct()
    {
        $this->setFlowId();
    }

    public function testSuiteStarted(string $name): string
    {
        return $this->writeMessage(
            'testSuiteStarted',
            ['name' => $name],
        );
    }

    public function testSuiteFinished(string $name): string
    {
        return $this->writeMessage(
            'testSuiteFinished',
            ['name' => $name],
        );
    }

    public function testCount(int $count): string
    {
        return $this->writeMessage(
            'testCount',
            ['count' => $count],
        );
    }

    public function testStarted(string $name): string
    {
        return $this->writeMessage(
            'testStarted',
            ['name' => $name],
        );
    }

    public function testFinished(string $name, int $durationMs = 0): string
    {
        return $this->writeMessage(
            'testFinished',
            [
                'name' => $name,
                'duration' => $durationMs,
            ],
        );
    }

    public function testFailed(string $name, string $message, string $details = ''): string
    {
        return $this->writeMessage(
            'testFailed',
            [
                'name' => $name,
                'message' => $message,
                'details' => $details,
            ],
        );
    }

    public function testIgnored(string $name, string $message): string
    {
        return $this->writeMessage(
            'testIgnored',
            [
                'name' => $name,
                'message' => $message,
            ],
        );
    }

    /**
     * @see https://www.jetbrains.com/help/teamcity/2025.07/service-messages.html#Service+Messages+Formats
     *
     * @param string|array<non-empty-string|int, string|int|float> $valueOrAttributes
     */
    public function write(
        MessageName $messageName,
        string|array $valueOrAttributes,
    ): string {
        return sprintf(
            '##teamcity[%s]',
            implode(
                ' ',
                [
                    $messageName->value,
                    ...self::escape((array) $valueOrAttributes),
                ],
            ),
        );
    }

    private function setFlowId(): void
    {
        try {
            $disabledFunctions = ini_get('disable_functions');

            if (stripos($disabledFunctions, 'getmypid') === false) {
                $this->flowId = getmypid();
            }
        } catch (Throwable) {
            // Ignore: flowId is optional
        }
    }

    /**
     * @param array<string, int|string> $parameters
     */
    private function writeMessage(string $eventName, array $parameters = []): string
    {
        $message = sprintf('##teamcity[%s', $eventName);

        if ($this->flowId !== null) {
            $parameters['flowId'] = $this->flowId;
        }

        foreach ($parameters as $key => $value) {
            $message .= sprintf(
                " %s='%s'",
                $key,
                $this->legacyEscape((string) $value),
            );
        }

        return $message . "]\n";
    }

    private function legacyEscape(string $string): string
    {
        return str_replace(
            ['|', "'", "\n", "\r", ']', '['],
            ['||', "|'", '|n', '|r', '|]', '|['],
            $string,
        );
    }

    /**
     * @param array<non-empty-string|int, string|int|float> $values
     *
     * @return non-empty-string[]
     */
    private static function escape(array $values): array
    {
        return array_map(
            static function (string|int $key) use ($values): string {
                $value = $values[$key];

                return is_int($key)
                    ? self::escapeValue($value)
                    : sprintf(
                        '%s=%s',
                        $key,
                        self::escapeValue($value),
                    );
            },
            array_keys($values),
        );
    }

    /**
     * @return non-empty-string
     */
    private static function escapeValue(string|int|float $value): string
    {
        $escapedValue = sprintf(
            '\'%s\'',
            str_replace(
                self::CHARACTERS_TO_ESCAPE,
                self::ESCAPED_CHARACTERS,
                (string) $value,
            ),
        );

        if (str_contains($value, '\u')) {
            $escapedValue = preg_replace(
                self::UNICODE_CHARACTER_REGEX,
                '|0x$1',
                $escapedValue,
            );
        }

        return $escapedValue;
    }
}
