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

namespace Infection\Mutation;

use function array_diff_key;
use function array_flip;
use function array_intersect_key;
use function array_keys;
use function array_map;
use function count;
use function implode;
use function sprintf;
use UnexpectedValueException;

/**
 * @internal
 */
enum MutationAttributeKeys: string
{
    case START_LINE = 'startLine';
    case END_LINE = 'endLine';
    case START_TOKEN_POSITION = 'startTokenPos';
    case END_TOKEN_POSITION = 'endTokenPos';
    case START_FILE_POSITION = 'startFilePos';
    case END_FILE_POSITION = 'endFilePos';

    /**
     * @param array<string|int|float> $attributes
     *
     * @return array<value-of<self>, string|int|float>
     */
    public static function pluck(array $attributes): array
    {
        $keysAsIndex = self::getKeysAsIndex();

        $values = array_intersect_key($attributes, $keysAsIndex);

        self::assertAllAttributesExist($values, $keysAsIndex);

        return $values;
    }

    /**
     * @return array<value-of<self>, mixed>
     */
    private static function getKeysAsIndex(): array
    {
        return array_flip(
            array_map(
                static fn (self $case) => $case->value,
                self::cases(),
            ),
        );
    }

    /**
     * @param array<value-of<self>, mixed> $values
     * @param array<value-of<self>, mixed> $keysAsIndex
     */
    private static function assertAllAttributesExist(array $values, array $keysAsIndex): void
    {
        if (count($values) === count($keysAsIndex)) {
            return;
        }

        throw new UnexpectedValueException(
            sprintf(
                'Expected all the mutation attributes to be found. Missing the following attribute(s): "%s".',
                implode(
                    '", "',
                    array_keys(
                        array_diff_key(
                            $keysAsIndex,
                            $values,
                        ),
                    ),
                ),
            ),
        );
    }
}
