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

use Infection\Framework\OperatingSystem;
use Infection\Framework\Str;
use const PHP_EOL;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use function str_replace;

#[CoversClass(Str::class)]
final class StrTest extends TestCase
{
    #[DataProvider('toSystemLineReturnProvider')]
    public function test_it_can_normalize_a_string_line_return_to_the_system_line_return(
        string $value,
        string $expected,
    ): void {
        $actual = Str::toSystemLineReturn($value);

        $this->assertSame($expected, $actual);
    }

    public static function toSystemLineReturnProvider(): iterable
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

    #[DataProvider('toLinuxLineReturnProvider')]
    public function test_it_can_normalize_a_string_line_return_to_the_linux_line_return(
        string $value,
        string $expected,
    ): void {
        $actual = Str::toLinuxLineReturn($value);

        $this->assertSame($expected, $actual);
    }

    public static function toLinuxLineReturnProvider(): iterable
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

    #[DataProvider('stringValuesProvider')]
    public function test_it_removes_trailing_spaces_and_replaces_line_returns_to_the_unix_line_return(
        string $input,
        string $expected,
    ): void {
        $actual = Str::normalize($input);

        $this->assertSame($expected, $actual);
    }

    public static function stringValuesProvider(): iterable
    {
        yield 'empty' => ['', ''];

        yield 'spaces' => [' ', ''];

        yield 'multi-line spaces' => [
            <<<'TXT'


                TXT
            ,
            <<<'TXT'


                TXT,
        ];

        yield 'text' => ['foo', 'foo'];

        yield 'text with spaces' => [' foo ', ' foo'];

        yield 'multi-line text with spaces' => [
            <<<'TXT'

                 foo
                 bar

                TXT
            ,
            <<<'TXT'

                 foo
                 bar

                TXT,
        ];

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

        yield 'multiline Unix/Linux (LF) with spaces' => [
            "\n"
            . " line1 \n"
            . " line2 \n"
            . "\n",
            <<<'TXT'

                 line1
                 line2


                TXT,
        ];

        yield 'multiline Windows (CRLF) with spaces' => [
            "\r\n"
            . " line1 \r\n"
            . " line2 \r\n"
            . "\r\n",
            <<<'TXT'

                 line1
                 line2


                TXT,
        ];

        yield 'multiline Classic MacOS (CRLF)with spaces' => [
            "\r"
            . " line1 \r"
            . " line2 \r"
            . "\r",
            <<<'TXT'

                 line1
                 line2


                TXT,
        ];
    }

    #[DataProvider('trimLineReturnProvider')]
    public function test_it_can_trim_string_of_line_returns_without_replacing_the_line_return_used(
        string $value,
        string $expected,
    ): void {
        if (OperatingSystem::isWindows()) {
            $value = str_replace("\n", "\r\n", $value);
            $expected = str_replace("\n", "\r\n", $expected);
        }

        $actual = Str::trimLineReturns($value);

        $this->assertSame($expected, $actual);
    }

    public static function trimLineReturnProvider(): iterable
    {
        yield 'empty' => [
            '',
            '',
        ];

        yield 'string with untrimmed spaces' => [
            '  ',
            '',
        ];

        yield 'string without line return' => [
            'Hello!',
            'Hello!',
        ];

        yield 'string with leading line returns' => [
            <<<'TXT'


                Hello!
                TXT
            ,
            'Hello!',
        ];

        yield 'string with trailing line returns' => [
            <<<'TXT'
                Hello!


                TXT
            ,
            'Hello!',
        ];

        yield 'string with leading & trailing line returns' => [
            <<<'TXT'


                Hello!


                TXT
            ,
            'Hello!',
        ];

        yield 'string with leading, trailing & in-between line returns' => [
            <<<'TXT'


                Hello...

                ...World!


                TXT
            ,
            <<<'TXT'
                Hello...

                ...World!
                TXT,
        ];

        yield 'string with leading, trailing & in-between line returns & dirty empty strings' => [
            <<<'TXT'


                  Hello...

                 ...World!


                TXT
            ,
            <<<'TXT'
                  Hello...

                 ...World!
                TXT,
        ];

        yield 'string with content followed by single whitespace line' => [
            "Hello\n  ",
            'Hello',
        ];

        yield 'string with only newlines' => [
            "\n\n\n",
            '',
        ];
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
