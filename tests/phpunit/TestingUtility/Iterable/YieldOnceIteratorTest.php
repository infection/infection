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
use Infection\Tests\TestingUtility\PHPUnit\ExpectsThrowables;
use function iterator_to_array;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(YieldOnceIterator::class)]
final class YieldOnceIteratorTest extends TestCase
{
    use ExpectsThrowables;

    /**
     * @param array<string, string> $values
     */
    #[DataProvider('valuesProvider')]
    public function test_it_decorates_the_given_iterator(array $values): void
    {
        $iterator = new YieldOnceIterator(
            new ArrayIterator($values),
        );

        $actual = iterator_to_array($iterator);

        $this->assertSame($values, $actual);
    }

    /**
     * @param array<string, string> $values
     */
    #[DataProvider('valuesProvider')]
    public function test_it_does_not_allow_the_same_key_to_be_fetched_more_than_once(array $values): void
    {
        $iterator = new YieldOnceIterator(
            new ArrayIterator($values),
        );

        $iterator->key();
        $this->expectToThrow($iterator->key(...));

        $iterator->next();
        $iterator->key();
        $this->expectToThrow($iterator->key(...));

        $iterator->rewind();
        $iterator->key();
        $this->expectToThrow($iterator->key(...));
    }

    /**
     * @param array<string, string> $values
     */
    #[DataProvider('valuesProvider')]
    public function test_it_does_not_allow_the_same_value_to_be_fetched_more_than_once(array $values): void
    {
        $iterator = new YieldOnceIterator(
            new ArrayIterator($values),
        );

        $iterator->current();
        $this->expectToThrow($iterator->current(...));

        $iterator->next();
        $iterator->current();
        $this->expectToThrow($iterator->current(...));

        $iterator->rewind();
        $iterator->current();
        $this->expectToThrow($iterator->current(...));
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
}
