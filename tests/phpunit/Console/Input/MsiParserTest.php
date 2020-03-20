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

namespace Infection\Tests\Console\Input;

use Infection\Console\Input\MsiParser;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class MsiParserTest extends TestCase
{
    /**
     * @dataProvider validValueProvider
     */
    public function test_it_can_parse_valid_values(?string $value, ?float $expected): void
    {
        $actual = MsiParser::parse($value, 'min-msi');

        $this->assertSame($expected, $actual);
    }

    /**
     * @dataProvider invalidValueProvider
     */
    public function test_it_cannot_parse_invalid_values(
        string $value,
        string $expectedErrorMessage
    ): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedErrorMessage);

        MsiParser::parse($value, 'min-msi');
    }

    public static function validValueProvider(): iterable
    {
        yield 'no value' => [null, null];

        yield 'empty string' => ['', null];

        yield 'empty untrimmed string' => ['  ', null];

        yield 'integer' => ['18', 18.];

        yield 'untrimmed integer' => [' 18 ', 18.];

        yield 'float' => ['18.3', 18.3];

        yield 'untrimmed float' => ['18.3', 18.3];

        yield 'nominal' => ['18.38', 18.38];

        yield 'rounded down' => ['18.382', 18.38];

        yield 'rounded up' => ['18.388', 18.39];

        yield 'half rounded up' => ['18.385', 18.39];

        yield 'bellow min value before rounding' => ['-.001', 0.];

        yield 'above max value before rounding' => ['100.001', 100];
    }

    public static function invalidValueProvider(): iterable
    {
        yield 'non numerical string' => ['foo', 'Expected min-msi to be a float. Got "foo"'];

        yield 'pseudo-numerical string' => ['foo18', 'Expected min-msi to be a float. Got "foo18"'];

        yield 'negative value' => ['-1', 'Expected min-msi to be an element of [0;100]. Got -1'];

        yield 'above max value' => ['100.01', 'Expected min-msi to be an element of [0;100]. Got 100.01'];
    }
}
