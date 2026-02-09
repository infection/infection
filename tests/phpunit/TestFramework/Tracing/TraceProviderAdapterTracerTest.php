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

namespace Infection\Tests\TestFramework\Tracing;

use ArrayIterator;
use Infection\TestFramework\Tracing\Throwable\NoTraceFound;
use Infection\TestFramework\Tracing\Trace\Trace;
use Infection\TestFramework\Tracing\TraceProvider;
use Infection\TestFramework\Tracing\TraceProviderAdapterTracer;
use Infection\Tests\TestingUtility\FileSystem\MockSplFileInfo;
use Infection\Tests\TestingUtility\Iterable\NonRewindableIterator;
use Infection\Tests\TestingUtility\Iterable\TrackableIterator;
use Infection\Tests\TestingUtility\Iterable\YieldOnceIterator;
use Infection\Tests\TestingUtility\PHPUnit\ExpectsThrowables;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use SplFileInfo;

#[CoversClass(TraceProviderAdapterTracer::class)]
final class TraceProviderAdapterTracerTest extends TestCase
{
    use ExpectsThrowables;

    private TraceProvider&MockObject $traceProviderMock;

    private TraceProviderAdapterTracer $tracer;

    protected function setUp(): void
    {
        $this->traceProviderMock = $this->createMock(TraceProvider::class);

        $this->tracer = new TraceProviderAdapterTracer($this->traceProviderMock);
    }

    public function test_it_can_trace_files(): void
    {
        $fileInfo1 = self::createDummySplFileInfo('src/Service1.php');
        $fileInfo2 = self::createDummySplFileInfo('src/Service2.php');
        $unknownFileInfo = self::createDummySplFileInfo('unknown');

        $traces = [
            $trace1 = $this->createTraceMock($fileInfo1),
            $trace2 = $this->createTraceMock($fileInfo2),
            $this->createTraceMock('unused trace'),
        ];

        $this->traceProviderMock
            ->method('provideTraces')
            ->willReturn($traces);

        $this->assertSame($trace1, $this->tracer->trace($fileInfo1));
        $this->assertSame($trace2, $this->tracer->trace($fileInfo2));

        $this->expectExceptionObject(
            new NoTraceFound(
                'Could not find any trace for file "unknown".',
            ),
        );

        $this->tracer->trace($unknownFileInfo);
    }

    public function test_it_traverses_and_pauses_the_trace_generator_as_needed_and_caches_the_results(): void
    {
        $fileInfo1 = self::createDummySplFileInfo('src/Service1.php');
        $fileInfo2 = self::createDummySplFileInfo('src/Service2.php');
        $fileInfo3 = self::createDummySplFileInfo('src/Service3.php');
        $unknownFileInfo = self::createDummySplFileInfo('unknown');

        // Note: we could make this test unbearably complicated... Or we leverage simple
        // composable utilities like here to ensure the behaviour is the one we expect.
        // By using those utilities, we do not need to do any additional check to
        // ensure that the generator used by the Tracer is not rewind, that the values
        // are cached, etc.
        $tracesIterator = new TrackableIterator(
            new YieldOnceIterator(
                new NonRewindableIterator(
                    new ArrayIterator([
                        $trace1 = $this->createTraceMock($fileInfo1),
                        $trace2 = $this->createTraceMock($fileInfo2),
                        $trace3 = $this->createTraceMock($fileInfo3),
                        $this->createTraceMock('unused trace'),
                    ]),
                ),
            ),
        );

        $this->traceProviderMock
            ->expects($this->once())
            ->method('provideTraces')
            ->willReturn($tracesIterator);

        // Sanity check
        $this->assertSame(0, $tracesIterator->getIndex());
        $this->assertFalse($tracesIterator->hasYieldedAnyValue());

        $this->assertSame($trace1, $this->tracer->trace($fileInfo1));
        $this->assertTrue($tracesIterator->hasYieldedAnyValue());

        $this->assertSame($trace2, $this->tracer->trace($fileInfo2));

        $this->assertSame(2, $tracesIterator->getIndex());

        // We exhaust the remainder of the iterator by looking for a non-existent trace
        $this->expectToThrow(fn () => $this->tracer->trace($unknownFileInfo));
        $this->assertSame(4, $tracesIterator->getIndex());

        $this->expectToThrow(fn () => $this->tracer->trace($unknownFileInfo));

        // We still can fetch traces despite the generator being exhausted
        $this->assertSame($trace3, $this->tracer->trace($fileInfo3));
    }

    // Note that despite being Windows-specific, the same problem could
    // maybe surface with symlinks (this was not checked).
    public function test_it_finds_traces_on_windows(): void
    {
        // Comes from the Symfony Finder.
        // See https://github.com/infection/infection/pull/2789#issuecomment-3710366303
        $sourceFileInfo = new MockSplFileInfo(
            'C:/path/to/project/src\Admin\Admin.php',
            'C:\path\to\project\src\Admin\Admin.php',
        );

        // Comes from SourceFileInfoProvider
        $coverageFileInfo = new MockSplFileInfo(
            'C:\path\to\project\src\Admin\Admin.php',
            'C:\path\to\project\src\Admin\Admin.php',
        );

        $tracesIterator = new ArrayIterator([
            $trace = $this->createTraceMock($coverageFileInfo),
            $this->createTraceMock($coverageFileInfo),
        ]);

        $this->traceProviderMock
            ->expects($this->once())
            ->method('provideTraces')
            ->willReturn($tracesIterator);

        $this->assertSame($trace, $this->tracer->trace($sourceFileInfo));
    }

    public function test_it_handles_empty_trace_provider(): void
    {
        $fileInfo = self::createDummySplFileInfo('src/Service.php');

        $this->traceProviderMock
            ->method('provideTraces')
            ->willReturn([]);

        $this->expectExceptionObject(
            new NoTraceFound(
                'Could not find any trace.',
            ),
        );

        $this->tracer->trace($fileInfo);
    }

    /**
     * @param non-empty-string $name
     */
    private static function createDummySplFileInfo(string $name): MockSplFileInfo
    {
        return new MockSplFileInfo($name, $name);
    }

    /**
     * @param SplFileInfo|non-empty-string $fileInfoOrName
     */
    private function createTraceMock(SplFileInfo|string $fileInfoOrName): Trace
    {
        $fileInfo = $fileInfoOrName instanceof SplFileInfo
            ? $fileInfoOrName
            : self::createDummySplFileInfo($fileInfoOrName);

        $traceStub = $this->createStub(Trace::class);
        $traceStub
            ->method('getSourceFileInfo')
            ->willReturn($fileInfo);
        $traceStub
            ->method('getRealPath')
            ->willReturn($fileInfo->getRealPath());

        return $traceStub;
    }
}
