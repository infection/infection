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

namespace Infection\Tests\TestFramework\PhpUnit\CommandLine;

use Infection\TestFramework\PhpUnit\CommandLine\TestFrameworkExtraArgs;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

#[CoversClass(TestFrameworkExtraArgs::class)]
final class TestFrameworkExtraArgsTest extends TestCase
{
    /**
     * @param list<string> $expectedTokens
     */
    #[DataProvider('rawTokensProvider')]
    public function test_it_parses_raw_tokens(
        string $rawArgs,
        array $expectedTokens,
    ): void {
        $extraArgs = TestFrameworkExtraArgs::parseRawTokens($rawArgs);

        $this->assertSame($expectedTokens, $extraArgs);
    }

    /**
     * @param list<string> $expectedTokens
     */
    #[DataProvider('rawTokensProvider')]
    public function test_it_parses_raw_tokens_with_the_fallback_tokenizer(
        string $rawArgs,
        array $expectedTokens,
    ): void {
        $this->assertSame($expectedTokens, self::parseRawTokensWithFallbackTokenizer($rawArgs));
    }

    /**
     * @return iterable<string, array{string, list<string>}>
     */
    public static function rawTokensProvider(): iterable
    {
        yield 'quoted value' => [
            ' tests/FooTest.php --filter="a test" --colors=always ',
            [
                'tests/FooTest.php',
                '--filter=a test',
                '--colors=always',
            ],
        ];

        yield 'unclosed quote' => [
            '--filter="unfinished',
            ['--filter="unfinished'],
        ];

        yield 'short option with quoted value' => [
            '-a"foo bar"',
            ['-afoo bar'],
        ];

        yield 'concatenated quoted values' => [
            '--long-option="foo bar""another"',
            ['--long-option=foo baranother'],
        ];

        yield 'mixed quote styles' => [
            '--long-option=\'foo bar\'"another"',
            ['--long-option=foo baranother'],
        ];

        yield 'escaped quotes' => [
            "--arg=\\\"'Jenny'\''s'\\\"",
            ['--arg="Jenny\'s"'],
        ];

        yield 'whitespace inside quoted string' => [
            "'a\rb\nc\td'",
            ["a\rb\nc\td"],
        ];

        yield 'whitespace between quoted strings' => [
            "'a'\r'b'\n'c'\t'd'",
            ['a', 'b', 'c', 'd'],
        ];
    }

    /**
     * @return list<string>
     */
    private static function parseRawTokensWithFallbackTokenizer(string $rawArgs): array
    {
        $tokenize = new ReflectionMethod(TestFrameworkExtraArgs::class, 'tokenize');

        return $tokenize->invoke(null, $rawArgs);
    }
}
