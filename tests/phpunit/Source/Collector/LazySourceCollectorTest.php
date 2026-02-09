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

namespace Infection\Tests\Source\Collector;

use Infection\Source\Collector\FakeSourceCollector;
use Infection\Source\Collector\FixedSourceCollector;
use Infection\Source\Collector\LazySourceCollector;
use Infection\Source\Collector\SourceCollector;
use Infection\Tests\TestingUtility\FileSystem\MockSplFileInfo;
use LogicException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SplFileInfo;

#[CoversClass(LazySourceCollector::class)]
final class LazySourceCollectorTest extends TestCase
{
    public function test_it_does_not_consume_the_factory_unless_necessary(): void
    {
        $exception = new LogicException();

        $collector = new LazySourceCollector(
            static fn () => throw $exception,
        );

        $this->expectExceptionObject($exception);

        // Only now it consumes the factory.
        $collector->collect();
    }

    public function test_it_exposes_its_decorated_collector(): void
    {
        $decoratedCollector = new FakeSourceCollector();

        $collector = new LazySourceCollector(
            static fn () => $decoratedCollector,
        );

        $this->assertSame($decoratedCollector, $collector->getCollector());
    }

    /**
     * @param SplFileInfo[] $expectedFiles
     */
    #[DataProvider('decoratedCollectorProvider')]
    public function test_it_decorates_the_given_collector(
        SourceCollector $decoratedCollector,
        array $expectedFiles,
    ): void {
        $collector = new LazySourceCollector($decoratedCollector);

        $this->assertSame($expectedFiles, $collector->collect());
    }

    public static function decoratedCollectorProvider(): iterable
    {
        yield [
            new FixedSourceCollector([]),
            [],
        ];

        yield (static function (): array {
            $file1 = new MockSplFileInfo('src/Service1.php');
            $file2 = new MockSplFileInfo('src/Service2.php');

            return [
                new FixedSourceCollector(
                    [
                        'key1' => $file1,
                        'key2' => $file2,
                    ],
                ),
                [
                    'key1' => $file1,
                    'key2' => $file2,
                ],
            ];
        })();
    }
}
