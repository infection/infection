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

namespace Infection\Tests\Configuration\SourceSymbol;

use Infection\Configuration\SourceSymbol\InvalidSourceSymbolSelector;
use Infection\Configuration\SourceSymbol\SourceSymbolSelector;
use Infection\Configuration\SourceSymbol\SourceSymbolSelectorParser;
use const PHP_INT_MAX;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(SourceSymbolSelectorParser::class)]
#[CoversClass(SourceSymbolSelector::class)]
final class SourceSymbolSelectorParserTest extends TestCase
{
    #[DataProvider('selectorProvider')]
    public function test_it_parses_source_selectors(
        string $input,
        SourceSymbolSelector|InvalidSourceSymbolSelector|null $expected,
    ): void {
        $parser = new SourceSymbolSelectorParser();

        if ($expected instanceof InvalidSourceSymbolSelector) {
            $this->expectExceptionObject($expected);
        }

        $actual = $parser->parse($input);

        $this->assertEquals(
            $expected,
            $actual,
            'The parsed selector must preserve the requested class, method, and line.',
        );
    }

    public static function selectorProvider(): iterable
    {
        yield 'ordinary FQCN' => [
            'App\Service\Mailer',
            new SourceSymbolSelector(
                'App\Service\Mailer',
                null,
                null,
            ),
        ];

        yield 'leading namespace separator' => [
            '\App\Service\Mailer',
            new SourceSymbolSelector(
                '\App\Service\Mailer',
                null,
                null,
            ),
        ];

        yield 'explicit global namespace' => [
            '\Mailer',
            new SourceSymbolSelector(
                '\Mailer',
                null,
                null,
            ),
        ];

        yield 'explicit global namespace and line' => [
            '\Mailer::32',
            new SourceSymbolSelector(
                '\Mailer',
                null,
                32,
            ),
        ];

        yield 'explicit global namespace and method line' => [
            '\Mailer::send::32',
            new SourceSymbolSelector(
                '\Mailer',
                'send',
                32,
            ),
        ];

        yield 'method' => [
            'App\Service\Mailer::__invoke',
            new SourceSymbolSelector(
                'App\Service\Mailer',
                '__invoke',
                null,
            ),
        ];

        yield 'short class and method' => [
            'Mailer::send',
            new SourceSymbolSelector(
                'Mailer',
                'send',
                null,
            ),
        ];

        yield 'short class and absolute line' => [
            'Mailer::32',
            new SourceSymbolSelector(
                'Mailer',
                null,
                32,
            ),
        ];

        yield 'absolute line' => [
            'App\Service\Mailer::32',
            new SourceSymbolSelector(
                'App\Service\Mailer',
                null,
                32,
            ),
        ];

        yield 'method and absolute line' => [
            'App\Service\Mailer::send::32',
            new SourceSymbolSelector(
                'App\Service\Mailer',
                'send',
                32,
            ),
        ];

        yield 'maximum absolute line' => [
            'Mailer::' . PHP_INT_MAX,
            new SourceSymbolSelector(
                'Mailer',
                null,
                PHP_INT_MAX,
            ),
        ];

        yield 'method and maximum absolute line' => [
            'Mailer::send::' . PHP_INT_MAX,
            new SourceSymbolSelector(
                'Mailer',
                'send',
                PHP_INT_MAX,
            ),
        ];

        yield 'overflowing absolute line' => [
            'Mailer::' . PHP_INT_MAX . '0',
            InvalidSourceSymbolSelector::lineOutOfRange('Mailer::' . PHP_INT_MAX . '0'),
        ];

        yield 'method and overflowing absolute line' => [
            'Mailer::send::' . PHP_INT_MAX . '0',
            InvalidSourceSymbolSelector::lineOutOfRange('Mailer::send::' . PHP_INT_MAX . '0'),
        ];

        yield 'bare short class' => [
            'Mailer',
            new SourceSymbolSelector(
                'Mailer',
                null,
                null,
            ),
        ];

        yield 'trailing class marker' => [
            'Mailer\\',
            new SourceSymbolSelector(
                'Mailer',
                null,
                null,
            ),
        ];

        yield 'global class with trailing marker' => [
            '\Mailer\\',
            new SourceSymbolSelector(
                '\Mailer',
                null,
                null,
            ),
        ];

        yield 'invalid class with trailing marker' => [
            '1Mailer\\',
            InvalidSourceSymbolSelector::create('1Mailer\\'),
        ];

        yield 'repeated trailing class marker' => [
            'Mailer\\\\',
            InvalidSourceSymbolSelector::create('Mailer\\\\'),
        ];

        yield 'bare file filter' => [
            'Mailer.php',
            null,
        ];

        yield 'Windows path' => [
            'C:\project\src\Mailer.php',
            null,
        ];

        yield 'ambiguous single-colon line separator' => [
            'App\Mailer::send:32',
            InvalidSourceSymbolSelector::create('App\Mailer::send:32'),
        ];

        yield 'two line coordinates' => [
            'App\Mailer::31::32',
            InvalidSourceSymbolSelector::create('App\Mailer::31::32'),
        ];

        yield 'underscores and trailing digits in identifiers' => [
            '_App2\_Mailer3::_send4',
            new SourceSymbolSelector(
                '_App2\_Mailer3',
                '_send4',
                null,
            ),
        ];

        yield 'non-ASCII identifiers' => [
            'École\Élève::étudier',
            new SourceSymbolSelector(
                'École\Élève',
                'étudier',
                null,
            ),
        ];

        yield 'minimum absolute line' => [
            'Mailer::1',
            new SourceSymbolSelector(
                'Mailer',
                null,
                1,
            ),
        ];

        yield 'method and minimum absolute line' => [
            'Mailer::send::1',
            new SourceSymbolSelector(
                'Mailer',
                'send',
                1,
            ),
        ];

        yield 'empty input' => [
            '',
            null,
        ];

        yield 'bare digit-starting name' => [
            '1Mailer',
            null,
        ];

        yield 'bare class with leading space' => [
            ' Mailer',
            null,
        ];

        yield 'bare class with trailing space' => [
            'Mailer ',
            null,
        ];

        yield 'bare class with trailing newline' => [
            "Mailer\n",
            null,
        ];

        yield 'qualified digit-starting name' => [
            '\1Mailer',
            InvalidSourceSymbolSelector::create('\1Mailer'),
        ];

        yield 'digit-starting namespace segment' => [
            '\App\1Mailer',
            InvalidSourceSymbolSelector::create('\App\1Mailer'),
        ];

        yield 'doubled leading namespace separator' => [
            '\\\\Mailer',
            InvalidSourceSymbolSelector::create('\\\\Mailer'),
        ];

        yield 'doubled internal namespace separator' => [
            '\\App\\\\Mailer',
            InvalidSourceSymbolSelector::create('\\App\\\\Mailer'),
        ];

        yield 'qualified class with trailing marker' => [
            '\\App\\Mailer\\',
            new SourceSymbolSelector(
                '\\App\\Mailer',
                null,
                null,
            ),
        ];

        yield 'namespace separator without a class' => [
            '\\',
            InvalidSourceSymbolSelector::create('\\'),
        ];

        yield 'empty coordinate' => [
            'Mailer::',
            InvalidSourceSymbolSelector::create('Mailer::'),
        ];

        yield 'empty method line' => [
            'Mailer::send::',
            InvalidSourceSymbolSelector::create('Mailer::send::'),
        ];

        yield 'empty method before line' => [
            'Mailer::::1',
            InvalidSourceSymbolSelector::create('Mailer::::1'),
        ];

        yield 'extra coordinate' => [
            'Mailer::send::1::2',
            InvalidSourceSymbolSelector::create('Mailer::send::1::2'),
        ];

        yield 'zero line' => [
            'Mailer::0',
            InvalidSourceSymbolSelector::create('Mailer::0'),
        ];

        yield 'zero method line' => [
            'Mailer::send::0',
            InvalidSourceSymbolSelector::create('Mailer::send::0'),
        ];

        yield 'negative line' => [
            'Mailer::-1',
            InvalidSourceSymbolSelector::create('Mailer::-1'),
        ];

        yield 'negative method line' => [
            'Mailer::send::-1',
            InvalidSourceSymbolSelector::create('Mailer::send::-1'),
        ];

        yield 'line with leading zero' => [
            'Mailer::01',
            InvalidSourceSymbolSelector::create('Mailer::01'),
        ];

        yield 'method line with leading zero' => [
            'Mailer::send::01',
            InvalidSourceSymbolSelector::create('Mailer::send::01'),
        ];

        yield 'digit-starting method name' => [
            'Mailer::1send',
            InvalidSourceSymbolSelector::create('Mailer::1send'),
        ];

        yield 'namespaced method name' => [
            'Mailer::App\send',
            InvalidSourceSymbolSelector::create('Mailer::App\send'),
        ];

        yield 'selector with leading space' => [
            ' Mailer::send',
            InvalidSourceSymbolSelector::create(' Mailer::send'),
        ];

        yield 'selector with trailing space' => [
            'Mailer::send ',
            InvalidSourceSymbolSelector::create('Mailer::send '),
        ];

        yield 'selector with internal space' => [
            'Mailer:: send',
            InvalidSourceSymbolSelector::create('Mailer:: send'),
        ];

        yield 'selector with trailing newline' => [
            "Mailer::send\n",
            InvalidSourceSymbolSelector::create("Mailer::send\n"),
        ];

        yield 'line with trailing newline' => [
            "Mailer::1\n",
            InvalidSourceSymbolSelector::create("Mailer::1\n"),
        ];

        yield 'method line with trailing newline' => [
            "Mailer::send::1\n",
            InvalidSourceSymbolSelector::create("Mailer::send::1\n"),
        ];
    }
}
