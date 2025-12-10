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

use function count;
use Infection\Source\Collector\FixedCollector;
use Infection\Source\Collector\LazySourceCollector;
use Infection\Source\Collector\SourceCollector;
use Infection\Tests\Fixtures\Finder\MockSplFileInfo;
use function iterator_to_array;
use LogicException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\SplFileInfo;

#[CoversClass(LazySourceCollector::class)]
final class LazySourceCollectorTest extends TestCase
{
    /**
     * @param list<SplFileInfo> $expectedFiles
     */
    #[DataProvider('sourceFilesProvider')]
    public function test_it_decorates_its_inner_collector(
        SourceCollector $decoratedCollector,
        bool $expectedIsFiltered,
        array $expectedFiles,
    ): void {
        $collector = new LazySourceCollector($decoratedCollector);

        $actualFiles = IteratorConsumer::consume($collector->collect());

        $this->assertSame($expectedIsFiltered, $collector->isFiltered());
        $this->assertEquals($expectedFiles, $actualFiles);
    }

    public static function sourceFilesProvider(): iterable
    {
        yield [
            new FixedCollector(
                false,
                [],
            ),
            false,
            [],
        ];

        yield [
            new FixedCollector(
                true,
                [],
            ),
            true,
            [],
        ];

        yield [
            new FixedCollector(
                true,
                [
                    'key1' => new MockSplFileInfo('src/Service1.php'),
                    'key2' => new MockSplFileInfo('src/Service2.php'),
                ],
            ),
            true,
            [
                new MockSplFileInfo('src/Service1.php'),
                new MockSplFileInfo('src/Service2.php'),
            ],
        ];
    }

    public function test_it_streams_the_collected_files_and_collects_them_only_once(): void
    {
        $files = [
            new MockSplFileInfo('src/Service1.php'),
            new MockSplFileInfo('src/Service2.php'),
        ];

        $decoratedCollector = new StreamingSourceCollectorSpy($files);

        $collector = new LazySourceCollector($decoratedCollector);

        // Sanity check
        $this->assertSame(0, $decoratedCollector->yieldCount);

        $collectedFiles = $collector->collect();

        // We didn't start consuming the iterable yet
        $this->assertSame(0, $decoratedCollector->yieldCount);

        $index = 0;

        foreach ($collectedFiles as $_file) {
            ++$index;

            $this->assertSame($index, $decoratedCollector->yieldCount);
        }

        // Sanity check
        $this->assertSame(count($files), $index);
    }

    public function test_it_collects_the_files_only_once(): void
    {
        $files = [
            new MockSplFileInfo('src/Service1.php'),
            new MockSplFileInfo('src/Service2.php'),
        ];

        $decoratedCollector = new StreamingSourceCollectorSpy($files);

        $collector = new LazySourceCollector($decoratedCollector);

        $collectedFiles1 = iterator_to_array($collector->collect());
        $collectedFiles2 = iterator_to_array($collector->collect());

        $this->assertSame($files, $collectedFiles1);
        $this->assertSame($files, $collectedFiles2);

        $this->assertSame(count($files), $decoratedCollector->yieldCount);
    }

    public function test_it_collects_the_files_only_once_even_if_consumed_twice_from_different_sources(): void
    {
        $files = [
            new MockSplFileInfo('src/Service1.php'),
            new MockSplFileInfo('src/Service2.php'),
        ];

        $decoratedCollector = new StreamingSourceCollectorSpy($files);

        $collector = new LazySourceCollector($decoratedCollector);

        $source1 = $collector->collect();
        // We get the 2nd source before we traversed anything!
        $source2 = $collector->collect();

        $firstItemSource1 = self::loopFirstItem($source1);
        $firstItemSource2 = self::loopFirstItem($source2);

        $this->assertSame($files[0], $firstItemSource1);
        $this->assertSame($firstItemSource1, $firstItemSource2);

        $this->assertSame(1, $decoratedCollector->yieldCount);
    }

    /**
     * @template TKey
     * @template-covariant TValue
     *
     * @param iterable<TKey, TValue> $iterable
     *
     * @return TValue
     */
    private static function loopFirstItem(iterable $iterable): mixed
    {
        foreach ($iterable as $item) {
            return $item;
        }

        throw new LogicException('Unreachable statement.');
    }
}
