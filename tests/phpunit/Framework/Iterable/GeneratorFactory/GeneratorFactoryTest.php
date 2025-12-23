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

namespace Infection\Tests\Framework\Iterable\GeneratorFactory;

use ArrayIterator;
use Generator;
use Infection\Framework\Iterable\GeneratorFactory;
use Infection\Tests\TestingUtility\Iterable\NonRewindableIterator;
use function iterator_to_array;
use IteratorAggregate;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Traversable;

#[CoversClass(GeneratorFactory::class)]
final class GeneratorFactoryTest extends TestCase
{
    /**
     * @param iterable<mixed> $iterable
     * @param mixed[] $expected
     */
    #[DataProvider('iterableProvider')]
    public function test_it_can_generate_a_generator_from_an_iterable(
        iterable $iterable,
        array $expected,
    ): void {
        $actual = GeneratorFactory::fromIterable($iterable);

        $this->assertInstanceOf(Generator::class, $actual);
        $this->assertSame($expected, iterator_to_array($actual));
    }

    public static function iterableProvider(): iterable
    {
        $keyValueExample = [
            'key1' => 'value1',
            'key2' => 'value2',
        ];

        yield 'empty array' => [
            [],
            [],
        ];

        yield 'array with custom keys' => [
            $keyValueExample,
            $keyValueExample,
        ];

        yield 'ArrayIterator' => [
            new ArrayIterator($keyValueExample),
            $keyValueExample,
        ];

        yield 'ArrayIterator with custom keys' => [
            new ArrayIterator($keyValueExample),
            $keyValueExample,
        ];

        yield 'non-rewindable Iterator' => [
            new NonRewindableIterator(
                new ArrayIterator($keyValueExample),
            ),
            $keyValueExample,
        ];

        yield 'Generator' => [
            (static function () use ($keyValueExample): Generator {
                yield from $keyValueExample;
            })(),
            $keyValueExample,
        ];

        yield 'IteratorAggregate' => [
            new NonRewindableIterator(
                new ArrayIterator($keyValueExample),
            ),
            $keyValueExample,
        ];

        yield 'non-rewindable IteratorAggregate' => [
            self::createIteratorAggregate(
                new NonRewindableIterator(
                    new ArrayIterator($keyValueExample),
                ),
            ),
            $keyValueExample,
        ];

        yield 'recursive non-rewindable IteratorAggregate' => [
            self::createIteratorAggregate(
                self::createIteratorAggregate(
                    new NonRewindableIterator(
                        new ArrayIterator($keyValueExample),
                    ),
                ),
            ),
            $keyValueExample,
        ];
    }

    /**
     * @param Traversable<string, string> $traversable
     */
    private static function createIteratorAggregate(Traversable $traversable): IteratorAggregate
    {
        return new SimpleIteratorAggregate($traversable);
    }
}
