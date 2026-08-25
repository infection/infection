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

namespace Infection\Tests\Configuration\SourceSymbolSelectorParser;

use Infection\Configuration\SourceSymbolSelector;
use Infection\Configuration\SourceSymbolSelectorParser;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(SourceSymbolSelectorParser::class)]
#[CoversClass(SourceSymbolSelector::class)]
final class SourceSymbolSelectorParserTest extends TestCase
{
    #[DataProvider('selectorProvider')]
    public function test_it_parses_source_selectors(string $input, ?SourceSymbolSelector $expected): void
    {
        $this->assertEquals($expected, (new SourceSymbolSelectorParser())->parse($input));
    }

    public static function selectorProvider(): iterable
    {
        yield 'ordinary FQCN' => ['App\Service\Mailer', new SourceSymbolSelector('App\Service\Mailer', null, null)];

        yield 'leading namespace separator' => ['\App\Service\Mailer', new SourceSymbolSelector('App\Service\Mailer', null, null)];

        yield 'method' => ['App\Service\Mailer::__invoke', new SourceSymbolSelector('App\Service\Mailer', '__invoke', null)];

        yield 'absolute line' => ['App\Service\Mailer::32', new SourceSymbolSelector('App\Service\Mailer', null, 32)];

        yield 'method and absolute line' => ['App\Service\Mailer::send::32', new SourceSymbolSelector('App\Service\Mailer', 'send', 32)];

        yield 'bare file filter' => ['Mailer.php', null];

        yield 'Windows path' => ['C:\project\src\Mailer.php', null];
    }

    public function test_it_rejects_the_ambiguous_single_colon_line_separator(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid source selector "App\Mailer::send:32"');

        (new SourceSymbolSelectorParser())->parse('App\Mailer::send:32');
    }

    public function test_it_rejects_two_line_coordinates(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new SourceSymbolSelectorParser())->parse('App\Mailer::31::32');
    }
}
