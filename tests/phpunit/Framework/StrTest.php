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

namespace Infection\Tests\Framework;

use Infection\Framework\Str;
use const PHP_EOL;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use function str_repeat;
use function str_replace;

#[CoversClass(Str::class)]
final class StrTest extends TestCase
{
    #[DataProvider('toSystemLineEndingsProvider')]
    public function test_it_replaces_any_line_ending_by_the_system_line_ending(
        string $value,
        string $expected,
    ): void {
        $actual = Str::toSystemLineEndings($value);

        $this->assertSame($expected, $actual);
    }

    public static function toSystemLineEndingsProvider(): iterable
    {
        yield 'Unix/Linux (LF)' => [
            "\n",
            PHP_EOL,
        ];

        yield 'Windows (CRLF)' => [
            "\r\n",
            PHP_EOL,
        ];

        yield 'Classic MacOS (CRLF)' => [
            "\r",
            PHP_EOL,
        ];
    }

    #[DataProvider('toUnixLineEndingsProvider')]
    public function test_it_replaces_any_line_ending_by_the_unix_line_ending(
        string $value,
        string $expected,
    ): void {
        $actual = Str::toUnixLineEndings($value);

        $this->assertSame($expected, $actual);
    }

    public static function toUnixLineEndingsProvider(): iterable
    {
        yield 'Unix/Linux (LF)' => [
            "\n",
            "\n",
        ];

        yield 'Windows (CRLF)' => [
            "\r\n",
            "\n",
        ];

        yield 'Classic MacOS (CRLF)' => [
            "\r",
            "\n",
        ];
    }

    #[DataProvider('trimLinesProvider')]
    public function test_it_trims_the_lines_and_replace_the_line_endings_by_the_unix_line_ending(
        string $input,
        string $expected,
    ): void {
        $actual = Str::rTrimLines($input);

        $this->assertSame($expected, $actual);
    }

    #[DataProvider('trimLinesProvider')]
    public function test_it_rtrim_lines_trims_blank_lines_and_replaces_the_line_endings_by_the_unix_line_ending(
        string $value,
        string $expectedTrimmedLines,
        ?string $expectedCleanedLines = null,
    ): void {
        $expected = $expectedCleanedLines ?? $expectedTrimmedLines;

        $actual = Str::cleanForDisplay($value);

        $this->assertSame($expected, $actual);
    }

    public static function trimLinesProvider(): iterable
    {
        yield 'empty' => [
            '',
            '',
        ];

        yield 'blank string' => [
            '  ',
            '',
        ];

        yield 'string without any line endings' => [
            'Hello!',
            'Hello!',
        ];

        yield 'string without line endings with spaces' => [
            ' Hello world! ',
            ' Hello world!',
        ];

        yield 'string with only Unix/Linux (LF) line endings' => [
            str_repeat("\n", 3),
            str_repeat("\n", 3),
            '',
        ];

        yield 'string with only Windows (CRLF) line endings' => [
            str_repeat("\r\n", 3),
            str_repeat("\n", 3),
            '',
        ];

        yield 'string with only Classic MacOS (CRLF) line endings' => [
            str_repeat("\r", 3),
            str_repeat("\n", 3),
            '',
        ];

        yield 'string with leading Unix/Linux (LF) line endings' => [
            str_repeat("\n", 3) . 'Hello!',
            str_repeat("\n", 3) . 'Hello!',
            'Hello!',
        ];

        yield 'string with leading Windows (CRLF) line endings' => [
            str_repeat("\r\n", 3) . 'Hello!',
            str_repeat("\n", 3) . 'Hello!',
            'Hello!',
        ];

        yield 'string with leading Classic MacOS (CRLF) line endings' => [
            str_repeat("\r", 3) . 'Hello!',
            str_repeat("\n", 3) . 'Hello!',
            'Hello!',
        ];

        yield 'string with trailing Unix/Linux (LF) line endings' => [
            'Hello!' . str_repeat("\n", 3),
            'Hello!' . str_repeat("\n", 3),
            'Hello!',
        ];

        yield 'string with trailing Windows (CRLF) line endings' => [
            'Hello!' . str_repeat("\r\n", 3),
            'Hello!' . str_repeat("\n", 3),
            'Hello!',
        ];

        yield 'string with trailing Classic MacOS (CRLF) line endings' => [
            'Hello!' . str_repeat("\r", 3),
            'Hello!' . str_repeat("\n", 3),
            'Hello!',
        ];

        yield 'string with leading & trailing Unix/Linux (LF) line endings' => [
            str_repeat("\n", 3) . 'Hello!' . str_repeat("\n", 3),
            str_repeat("\n", 3) . 'Hello!' . str_repeat("\n", 3),
            'Hello!',
        ];

        yield 'string with leading & trailing Windows (CRLF) line endings' => [
            str_repeat("\r\n", 3) . 'Hello!' . str_repeat("\r\n", 3),
            str_repeat("\n", 3) . 'Hello!' . str_repeat("\n", 3),
            'Hello!',
        ];

        yield 'string with leading & trailing Classic MacOS (CRLF) line endings' => [
            str_repeat("\r", 3) . 'Hello!' . str_repeat("\r", 3),
            str_repeat("\n", 3) . 'Hello!' . str_repeat("\n", 3),
            'Hello!',
        ];

        yield from (static function () {
            $s = ' ';   // Adding those variables for visibility
            $value = <<<TXT

                $s
                {$s}Hello...$s

                $s
                $s...World!$s
                $s

                TXT;

            $expectedTrimmedLines = <<<TXT


                {$s}Hello...


                $s...World!


                TXT;

            $expectedCleanedLines = <<<TXT
                {$s}Hello...


                $s...World!
                TXT;

            yield 'string with leading, trailing & in-between line endings and spaces and blank lines – Unix/Linux (LF) line endings' => [
                $value,
                $expectedTrimmedLines,
                $expectedCleanedLines,
            ];

            yield 'string with leading, trailing & in-between line endings and spaces and blank lines – Windows (CRLF) line endings' => [
                str_replace("\n", "\r\n", $value),
                $expectedTrimmedLines,
                $expectedCleanedLines,
            ];

            yield 'string with leading, trailing & in-between line endings and spaces and blank lines – Classic MacOS (CRLF) line endings' => [
                str_replace("\n", "\r", $value),
                $expectedTrimmedLines,
                $expectedCleanedLines,
            ];
        })();
    }

    #[DataProvider('utf8StringConversionProvider')]
    public function test_it_converts_strings_to_utf8_encoding(string $input, string $expected): void
    {
        $result = Str::convertToUtf8($input);

        $this->assertSame($expected, $result);
    }

    public static function utf8StringConversionProvider(): iterable
    {
        yield 'simple ASCII string' => ['Hello World', 'Hello World'];

        yield 'UTF-8 with accents' => ['Héllo Wörld', 'Héllo Wörld'];

        yield 'UTF-8 with Chinese characters' => ['你好', '你好'];

        yield 'UTF-8 with emojis' => ['Hello 🎉', 'Hello 🎉'];

        yield 'empty string' => ['', ''];

        yield 'multi-line string' => ["Line1\nLine2\nLine3", "Line1\nLine2\nLine3"];

        yield 'mixed special characters' => ['Café ñ ü ö 世界', 'Café ñ ü ö 世界'];

        yield 'invalid byte sequence 1' => ["Hello\xC0\xC1World", 'Hello??World'];

        yield 'invalid byte sequence 2' => ["Test\xF5\xF6\xF7\xF8", 'Test????'];

        yield 'truncated multi-byte' => ["Hello\xC2World", 'Hello?World'];

        yield 'overlong encoding' => ["Test\xC0\x80", 'Test??'];
    }
}
