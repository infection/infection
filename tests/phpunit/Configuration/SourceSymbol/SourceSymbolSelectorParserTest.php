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

        $this->assertEquals($expected, $actual);
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
                'App\Service\Mailer',
                null,
                null,
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

        yield 'bare short class' => [
            'Mailer',
            new SourceSymbolSelector(
                'Mailer',
                null,
                null,
            ),
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
    }
}
