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

namespace Infection\Tests\TestingUtility\Iterable;

use ArrayIterator;
use function iterator_to_array;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(TrackableIterator::class)]
final class TrackableIteratorTest extends TestCase
{
    /**
     * @param array<string, string> $values
     */
    #[DataProvider('valuesProvider')]
    public function test_it_decorates_the_given_iterator(array $values): void
    {
        $iterator = new TrackableIterator(
            new ArrayIterator($values),
        );

        $actual = iterator_to_array($iterator);

        $this->assertSame($values, $actual);
    }

    public static function valuesProvider(): iterable
    {
        yield [
            [],
        ];

        yield [
            [
                'a' => 'A',
                'b' => 'B',
            ],
        ];
    }

    public function test_it_tracks_the_yielded_values_of_the_iterator(): void
    {
        $iterator = new TrackableIterator(
            new YieldOnceIterator(
                new ArrayIterator([
                    'a' => 'A',
                    'b' => 'B',
                    'c' => 'D',
                ]),
            ),
        );

        $this->assertIteratorStateIs(
            $iterator,
            expectedHasYieldedAnyValue: false,
            expectedIndex: 0,
            expectedLastYieldedKey: TrackableIterator::EMPTY_KEY,
            expectedLastYieldedValue: TrackableIterator::EMPTY_VALUE,
        );

        $key = $iterator->key();

        $this->assertSame('a', $key);   // Sanity check
        $this->assertIteratorStateIs(
            $iterator,
            expectedHasYieldedAnyValue: true,
            expectedIndex: 0,
            expectedLastYieldedKey: 'a',
            expectedLastYieldedValue: TrackableIterator::EMPTY_VALUE,
        );

        $value = $iterator->current();

        $this->assertSame('A', $value);   // Sanity check
        $this->assertIteratorStateIs(
            $iterator,
            expectedHasYieldedAnyValue: true,
            expectedIndex: 0,
            expectedLastYieldedKey: 'a',
            expectedLastYieldedValue: 'A',
        );

        // Next changes the to be yielded ones, but as they are not yielded yet!
        $iterator->next();

        $this->assertIteratorStateIs(
            $iterator,
            expectedHasYieldedAnyValue: true,
            expectedIndex: 1,
            expectedLastYieldedKey: 'a',
            expectedLastYieldedValue: 'A',
        );

        $key = $iterator->key();

        $this->assertSame('b', $key);   // Sanity check
        $this->assertIteratorStateIs(
            $iterator,
            expectedHasYieldedAnyValue: true,
            expectedIndex: 1,
            expectedLastYieldedKey: 'b',
            expectedLastYieldedValue: 'A',
        );

        $value = $iterator->current();

        $this->assertSame('B', $value);   // Sanity check
        $this->assertIteratorStateIs(
            $iterator,
            expectedHasYieldedAnyValue: true,
            expectedIndex: 1,
            expectedLastYieldedKey: 'b',
            expectedLastYieldedValue: 'B',
        );

        // Rewind changes the to be yielded ones, but as they are not yielded yet!
        $iterator->rewind();

        $this->assertIteratorStateIs(
            $iterator,
            expectedHasYieldedAnyValue: true,
            expectedIndex: 0,
            expectedLastYieldedKey: 'b',
            expectedLastYieldedValue: 'B',
        );

        $key = $iterator->key();

        $this->assertSame('a', $key);   // Sanity check
        $this->assertIteratorStateIs(
            $iterator,
            expectedHasYieldedAnyValue: true,
            expectedIndex: 0,
            expectedLastYieldedKey: 'a',
            expectedLastYieldedValue: 'B',
        );

        $value = $iterator->current();

        $this->assertSame('A', $value);   // Sanity check
        $this->assertIteratorStateIs(
            $iterator,
            expectedHasYieldedAnyValue: true,
            expectedIndex: 0,
            expectedLastYieldedKey: 'a',
            expectedLastYieldedValue: 'A',
        );

        $iterator->next();

        $this->assertIteratorStateIs(
            $iterator,
            expectedHasYieldedAnyValue: true,
            expectedIndex: 1,
            expectedLastYieldedKey: 'a',
            expectedLastYieldedValue: 'A',
        );

        $key = $iterator->key();

        $this->assertSame('b', $key);   // Sanity check
        $this->assertIteratorStateIs(
            $iterator,
            expectedHasYieldedAnyValue: true,
            expectedIndex: 1,
            expectedLastYieldedKey: 'b',
            expectedLastYieldedValue: 'A',
        );

        $value = $iterator->current();

        $this->assertSame('B', $value);   // Sanity check
        $this->assertIteratorStateIs(
            $iterator,
            expectedHasYieldedAnyValue: true,
            expectedIndex: 1,
            expectedLastYieldedKey: 'b',
            expectedLastYieldedValue: 'B',
        );
    }

    /**
     * @param TrackableIterator<array-key, mixed> $iterator
     */
    private function assertIteratorStateIs(
        TrackableIterator $iterator,
        bool $expectedHasYieldedAnyValue,
        int $expectedIndex,
        mixed $expectedLastYieldedKey,
        mixed $expectedLastYieldedValue,
    ): void {
        $this->assertSame($expectedHasYieldedAnyValue, $iterator->hasYieldedAnyValue());
        $this->assertSame($expectedIndex, $iterator->getIndex());
        $this->assertSame($expectedLastYieldedKey, $iterator->getLastYieldedKey());
        $this->assertSame($expectedLastYieldedValue, $iterator->getLastYieldedValue());
    }
}
