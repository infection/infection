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

namespace Infection\Tests\Mutator;

use Infection\Mutator\MutatorParser;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

final class MutatorParserTest extends TestCase
{
    /**
     * @var MutatorParser
     */
    private $mutatorParser;

    protected function setUp(): void
    {
        $this->mutatorParser = new MutatorParser();
    }

    /**
     * @dataProvider mutatorInputProvider
     *
     * @param string[] $expectedMutators
     */
    public function test_it_can_parse_the_provided_input(
        string $mutatorInput,
        array $expectedMutators
    ): void {
        $parsedMutators = $this->mutatorParser->parse($mutatorInput);

        $this->assertSame($expectedMutators, $parsedMutators);
    }

    public function test_it_cannot_parse_unknown_mutator(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Expected "Unknown" to be a known mutator or profile. See "https://infection.github.io/guide/mutators.html" and "https://infection.github.io/guide/profiles.html" for the list of available mutations and profiles.');

        $this->mutatorParser->parse('Unknown');
    }

    public function mutatorInputProvider(): iterable
    {
        yield 'empty string' => ['', []];

        yield 'string with only spaces' => ['  ', []];

        yield 'mutator by name' => [
            'TrueValue',
            ['TrueValue'],
        ];

        yield 'profile' => [
            '@boolean',
            ['@boolean'],
        ];

        yield 'nominal' => [
            'TrueValue,FalseValue, @boolean',
            [
                'TrueValue',
                'FalseValue',
                '@boolean',
            ],
        ];

        yield 'spaces, empty values & co.' => [
            '  TrueValue  ,   ,  FalseValue   ,,,   @boolean  ',
            [
                'TrueValue',
                'FalseValue',
                '@boolean',
            ],
        ];
    }
}
