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

namespace Infection\Tests\Source\Collector\LazyCacheSourceCollector;

use function count;
use Infection\Source\Collector\FixedSourceCollector;
use Infection\Source\Collector\LazyCacheSourceCollector;
use Infection\Source\Collector\SourceCollector;
use Infection\Tests\Fixtures\Finder\MockSplFileInfo;
use Iterator;
use function iterator_to_array;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\SplFileInfo;

#[CoversClass(LazyCacheSourceCollector::class)]
final class LazyCacheSourceCollectorTest extends TestCase
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
        $collector = new LazyCacheSourceCollector($decoratedCollector);

        $collected = $collector->collect();
        $this->assertInstanceOf(Iterator::class, $collected);

        $actualFiles = IteratorConsumer::consume($collected);

        $this->assertSame($expectedIsFiltered, $collector->isFiltered());
        $this->assertEquals($expectedFiles, $actualFiles);
    }

    public static function sourceFilesProvider(): iterable
    {
        yield [
            new FixedSourceCollector(
                false,
                [],
            ),
            false,
            [],
        ];

        yield [
            new FixedSourceCollector(
                true,
                [],
            ),
            true,
            [],
        ];

        yield [
            new FixedSourceCollector(
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

    public function test_it_collects_all_items_at_once_on_first_collect(): void
    {
        $files = [
            new MockSplFileInfo('src/Service1.php'),
            new MockSplFileInfo('src/Service2.php'),
        ];

        $decoratedCollector = new StreamingSourceCollectorSpy($files);

        $collector = new LazyCacheSourceCollector($decoratedCollector);

        // Sanity check
        $this->assertSame(0, $decoratedCollector->yieldCount);

        $collectedFiles = $collector->collect();

        // All items are collected immediately on first collect() call
        $this->assertSame(count($files), $decoratedCollector->yieldCount);

        // Consuming the iterator doesn't trigger additional yields
        $index = 0;

        foreach ($collectedFiles as $_file) {
            ++$index;
        }

        // Sanity check
        // @phpstan-ignore argument.unresolvableType
        $this->assertSame(count($files), $index);
        // No additional yields occurred
        // @phpstan-ignore argument.unresolvableType
        $this->assertSame(count($files), $decoratedCollector->yieldCount);
    }

    public function test_it_collects_the_files_only_once(): void
    {
        $files = [
            new MockSplFileInfo('src/Service1.php'),
            new MockSplFileInfo('src/Service2.php'),
        ];

        $decoratedCollector = new StreamingSourceCollectorSpy($files);

        $collector = new LazyCacheSourceCollector($decoratedCollector);

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

        $collector = new LazyCacheSourceCollector($decoratedCollector);

        $source1 = $collector->collect();
        // All items are collected immediately on first collect() call
        $this->assertSame(count($files), $decoratedCollector->yieldCount);

        // We get the 2nd source after the first collect() call
        $source2 = $collector->collect();
        // No additional yields, still using cached data
        $this->assertSame(count($files), $decoratedCollector->yieldCount);

        // Both sources return the same cached data
        $collectedFiles1 = iterator_to_array($source1);
        $collectedFiles2 = iterator_to_array($source2);

        $this->assertSame($files, $collectedFiles1);
        $this->assertSame($files, $collectedFiles2);

        // Still no additional yields - everything is cached
        $this->assertSame(count($files), $decoratedCollector->yieldCount);
    }
}
