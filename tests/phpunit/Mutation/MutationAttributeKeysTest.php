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

namespace Infection\Tests\Mutation;

use function array_diff_key;
use function array_merge;
use Exception;
use Infection\Mutation\MutationAttributeKeys;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

#[CoversClass(MutationAttributeKeys::class)]
final class MutationAttributeKeysTest extends TestCase
{
    /**
     * @param array<string|int|float> $attributes
     * @param array<string, string|int|float>|Exception $expected
     */
    #[DataProvider('attributesProvider')]
    public function test_it_plucks_all_attributes(
        array $attributes,
        array|Exception $expected,
    ): void {
        if ($expected instanceof Exception) {
            $this->expectExceptionObject($expected);
        }

        $actual = MutationAttributeKeys::pluck($attributes);

        $this->assertSame($expected, $actual);
    }

    public static function attributesProvider(): iterable
    {
        $nominalAttributes = [
            'startLine' => 3,
            'endLine' => 5,
            'startTokenPos' => 21,
            'endTokenPos' => 31,
            'startFilePos' => 43,
            'endFilePos' => 53,
        ];

        $expected = [
            MutationAttributeKeys::START_LINE->value => 3,
            MutationAttributeKeys::END_LINE->value => 5,
            MutationAttributeKeys::START_TOKEN_POSITION->value => 21,
            MutationAttributeKeys::END_TOKEN_POSITION->value => 31,
            MutationAttributeKeys::START_FILE_POSITION->value => 43,
            MutationAttributeKeys::END_FILE_POSITION->value => 53,
        ];

        yield 'nominal' => [
            $nominalAttributes,
            $expected,
        ];

        yield 'with extra keys' => [
            array_merge(
                $nominalAttributes,
                ['a' => 'A', 'b' => 'B'],
            ),
            $expected,
        ];

        yield 'with missing keys' => [
            array_diff_key(
                $nominalAttributes,
                [
                    'startLine' => null,
                    'endFilePos' => null,
                ],
            ),
            new UnexpectedValueException(
                'Expected all the mutation attributes to be found. Missing the following attribute(s): "startLine", "endFilePos".',
            ),
        ];
    }
}
