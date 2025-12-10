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

namespace Infection\Tests\Source\Collector\LazySourceCollector;

use DomainException;
use Infection\Tests\Fixtures\Finder\MockSplFileInfo;
use Iterator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\SplFileInfo;

#[CoversClass(NonRewindableStreamingSourceCollector::class)]
final class NonRewindableStreamingSourceCollectorTest extends TestCase
{
    #[DataProvider('filteredProvider')]
    public function test_it_cannot_call_filtered_twice(bool $filtered): void
    {
        $collector = new NonRewindableStreamingSourceCollector(
            $filtered,
            [new MockSplFileInfo('src/File')],
        );

        $collector->isFiltered();

        $this->expectException(DomainException::class);

        $collector->isFiltered();
    }

    public static function filteredProvider(): iterable
    {
        yield [true];

        yield [false];
    }

    /**
     * @param non-empty-array<SplFileInfo> $expected
     */
    #[DataProvider('collectProvider')]
    public function test_it_streams_the_data(array $expected): void
    {
        $collector = new NonRewindableStreamingSourceCollector(
            true,
            $expected,
        );

        $actual = self::consume($collector->collect());

        $this->assertSame($expected, $actual);
    }

    /**
     * @param non-empty-array<SplFileInfo> $files
     */
    #[DataProvider('collectProvider')]
    public function test_it_cannot_call_consume_collect_twice(array $files): void
    {
        $collector = new NonRewindableStreamingSourceCollector(
            true,
            $files,
        );

        self::consume($collector->collect());

        $this->expectException(DomainException::class);

        self::consume($collector->collect());
    }

    /**
     * @param non-empty-array<SplFileInfo> $files
     */
    #[DataProvider('collectProvider')]
    public function test_it_cannot_consume_the_same_iterable_twice(array $files): void
    {
        $collector = new NonRewindableStreamingSourceCollector(
            true,
            $files,
        );

        $files = $collector->collect();

        self::consume($files);

        $this->expectException(DomainException::class);

        self::consume($files);
    }

    public static function collectProvider(): iterable
    {
        yield [
            [
                new MockSplFileInfo('src/File1.php'),
                new MockSplFileInfo('src/File2.php'),
            ],
        ];
    }

    /**
     * @template TKey extends array-key
     * @template TValue
     *
     * @param iterable<TKey,TValue> $iterable
     *
     * @return array<TKey,TValue>
     */
    private static function consume(iterable $iterable): array
    {
        self::assertInstanceOf(Iterator::class, $iterable);

        return IteratorConsumer::consume($iterable);
    }
}
