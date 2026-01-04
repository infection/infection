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

use function array_column;
use Closure;
use Infection\Source\Collector\CoveredSourceCollector;
use Infection\Source\Collector\SourceCollector;
use Infection\TestFramework\Tracing\Throwable\NoTraceFound;
use Infection\TestFramework\Tracing\Trace\Trace;
use Infection\TestFramework\Tracing\Tracer;
use Infection\Tests\Fixtures\Finder\MockSplFileInfo;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use SplFileInfo;
use SplObjectStorage;

#[CoversClass(CoveredSourceCollector::class)]
final class CoveredSourceCollectorTest extends TestCase
{
    private SourceCollector&MockObject $decoratedCollectorMock;

    private Tracer&MockObject $tracerMock;

    private CoveredSourceCollector $collector;

    protected function setUp(): void
    {
        $this->decoratedCollectorMock = $this->createMock(SourceCollector::class);
        $this->tracerMock = $this->createMock(Tracer::class);

        $this->collector = new CoveredSourceCollector(
            $this->decoratedCollectorMock,
            $this->tracerMock,
        );
    }

    /**
     * @param array<array{SplFileInfo, bool|null}> $filesTuple
     * @param SplFileInfo[] $expected
     */
    #[DataProvider('fileProvider')]
    public function test_it_filters_out_files_without_tests(
        array $filesTuple,
        array $expected,
    ): void {
        $files = array_column($filesTuple, 0);

        $this->decoratedCollectorMock
            ->expects($this->once())
            ->method('collect')
            ->willReturn($files);

        $traces = $this->createTraceMocks($filesTuple);

        $this->tracerMock
            ->method('trace')
            ->willReturnCallback(self::createTraceCallback($traces));

        $actual = $this->collector->collect();

        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    public static function fileProvider(): iterable
    {
        $file1 = new MockSplFileInfo('src/File1.php');
        $file2 = new MockSplFileInfo('src/File2.php');
        $file3 = new MockSplFileInfo('src/File3.php');
        $file4 = new MockSplFileInfo('src/Fil4.php');

        yield 'no sources' => [
            [],
            [],
        ];

        yield 'source without trace' => [
            [
                [$file1, null],
            ],
            [],
        ];

        yield 'source with trace without tests' => [
            [
                [$file1, false],
            ],
            [],
        ];

        yield 'source with trace with tests' => [
            [
                [$file1, true],
            ],
            [$file1],
        ];

        yield 'nominal' => [
            [
                [$file1, true],
                [$file2, false],
                [$file3, null],
                [$file4, true],
            ],
            [$file1, $file4],
        ];
    }

    /**
     * @param array<array{SplFileInfo, bool|null}> $filesTuple
     *
     * @return SplObjectStorage<SplFileInfo, Trace>
     */
    public function createTraceMocks(array $filesTuple): SplObjectStorage
    {
        /** @var SplObjectStorage<SplFileInfo, Trace> $traces */
        $traces = new SplObjectStorage();

        foreach ($filesTuple as [$file, $hasTests]) {
            if ($hasTests === null) {
                continue;
            }

            $traceMock = $this->createTraceMock($hasTests);

            $traces->offsetSet($file, $traceMock);
        }

        return $traces;
    }

    private function createTraceMock(bool $hasTests): Trace
    {
        $traceMock = $this->createMock(Trace::class);
        $traceMock
            ->expects($this->once())
            ->method('hasTests')
            ->willReturn($hasTests);

        return $traceMock;
    }

    /**
     * @param SplObjectStorage<SplFileInfo, Trace> $traces
     */
    private static function createTraceCallback(SplObjectStorage $traces): Closure
    {
        return static function (SplFileInfo $source) use ($traces): Trace {
            if (!$traces->offsetExists($source)) {
                throw new NoTraceFound();
            }

            return $traces->offsetGet($source);
        };
    }
}
