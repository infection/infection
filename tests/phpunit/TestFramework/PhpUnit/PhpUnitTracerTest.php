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

namespace Infection\Tests\TestFramework\PhpUnit;

use Infection\TestFramework\Coverage\Trace;
use Infection\TestFramework\Coverage\TraceProvider;
use Infection\TestFramework\PhpUnit\PhpUnitTracer;
use Infection\Tests\Fixtures\Finder\MockSplFileInfo;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\SplFileInfo;
use Webmozart\Assert\InvalidArgumentException;

#[CoversClass(PhpUnitTracer::class)]
final class PhpUnitTracerTest extends TestCase
{
    private TraceProvider&MockObject $traceProviderMock;

    private PhpUnitTracer $tracer;

    protected function setUp(): void
    {
        $this->traceProviderMock = $this->createMock(TraceProvider::class);

        $this->tracer = new PhpUnitTracer($this->traceProviderMock);
    }

    public function test_it_returns_true_when_trace_exists(): void
    {
        $fileInfo = new MockSplFileInfo([
            'name' => 'Foo.php',
            'realPath' => '/path/to/Foo.php',
        ]);

        $expectedTrace = $this->createTraceMock($fileInfo);

        $this->traceProviderMock
            ->method('provideTraces')
            ->willReturn([$expectedTrace]);

        $this->assertTrue($this->tracer->hasTrace($fileInfo));
    }

    public function test_it_returns_false_when_trace_does_not_exist(): void
    {
        $searchFileInfo = new MockSplFileInfo([
            'name' => 'Bar.php',
            'realPath' => '/path/to/Bar.php',
        ]);

        $otherFileInfo = new MockSplFileInfo([
            'name' => 'Foo.php',
            'realPath' => '/path/to/Foo.php',
        ]);

        $otherTrace = $this->createTraceMock($otherFileInfo);

        $this->traceProviderMock
            ->method('provideTraces')
            ->willReturn([$otherTrace]);

        $this->assertFalse($this->tracer->hasTrace($searchFileInfo));
    }

    public function test_it_returns_trace_when_it_exists(): void
    {
        $fileInfo = new MockSplFileInfo([
            'name' => 'Foo.php',
            'realPath' => '/path/to/Foo.php',
        ]);

        $expectedTrace = $this->createTraceMock($fileInfo);

        $this->traceProviderMock
            ->method('provideTraces')
            ->willReturn([$expectedTrace]);

        $actualTrace = $this->tracer->trace($fileInfo);

        $this->assertSame($expectedTrace, $actualTrace);
    }

    public function test_it_throws_when_trace_does_not_exist(): void
    {
        $searchFileInfo = new MockSplFileInfo([
            'name' => 'Bar.php',
            'realPath' => '/path/to/Bar.php',
        ]);

        $otherFileInfo = new MockSplFileInfo([
            'name' => 'Foo.php',
            'realPath' => '/path/to/Foo.php',
        ]);

        $otherTrace = $this->createTraceMock($otherFileInfo);

        $this->traceProviderMock
            ->method('provideTraces')
            ->willReturn([$otherTrace]);

        $this->expectException(InvalidArgumentException::class);

        $this->tracer->trace($searchFileInfo);
    }

    public function test_it_caches_trace_results(): void
    {
        $fileInfo = new MockSplFileInfo([
            'name' => 'Foo.php',
            'realPath' => '/path/to/Foo.php',
        ]);

        $expectedTrace = $this->createTraceMock($fileInfo);

        $this->traceProviderMock
            ->expects($this->once())
            ->method('provideTraces')
            ->willReturn([$expectedTrace]);

        // First call
        $firstTrace = $this->tracer->trace($fileInfo);
        // Second call should use cache and not call provideTraces again
        $secondTrace = $this->tracer->trace($fileInfo);

        $this->assertSame($expectedTrace, $firstTrace);
        $this->assertSame($expectedTrace, $secondTrace);
        $this->assertSame($firstTrace, $secondTrace);
    }

    public function test_it_caches_missing_trace_after_full_traversal(): void
    {
        $searchFileInfo = new MockSplFileInfo([
            'name' => 'Bar.php',
            'realPath' => '/path/to/Bar.php',
        ]);

        $otherFileInfo = new MockSplFileInfo([
            'name' => 'Foo.php',
            'realPath' => '/path/to/Foo.php',
        ]);

        $otherTrace = $this->createTraceMock($otherFileInfo);

        $this->traceProviderMock
            ->expects($this->once())
            ->method('provideTraces')
            ->willReturn([$otherTrace]);

        // First call - will traverse entire generator
        $firstResult = $this->tracer->hasTrace($searchFileInfo);
        // Second call - should use cached null result
        $secondResult = $this->tracer->hasTrace($searchFileInfo);

        $this->assertFalse($firstResult);
        $this->assertFalse($secondResult);
    }

    public function test_it_stops_traversal_when_trace_is_found(): void
    {
        $file1 = new MockSplFileInfo([
            'name' => 'File1.php',
            'realPath' => '/path/to/File1.php',
        ]);
        $file2 = new MockSplFileInfo([
            'name' => 'File2.php',
            'realPath' => '/path/to/File2.php',
        ]);
        $file3 = new MockSplFileInfo([
            'name' => 'File3.php',
            'realPath' => '/path/to/File3.php',
        ]);

        $trace1 = $this->createTraceMock($file1);
        $trace2 = $this->createTraceMock($file2);
        $trace3 = $this->createTraceMock($file3);

        $this->traceProviderMock
            ->method('provideTraces')
            ->willReturn((static function () use ($trace1, $trace2, $trace3) {
                yield $trace1;

                yield $trace2;

                yield $trace3;
            })());

        // Find the second file - should stop after finding it
        $foundTrace = $this->tracer->trace($file2);
        $this->assertSame($trace2, $foundTrace);

        // Now lookup file1 - should be cached from the partial traversal
        $this->assertTrue($this->tracer->hasTrace($file1));

        // Now lookup file3 - requires continuing the traversal
        $this->assertTrue($this->tracer->hasTrace($file3));
    }

    public function test_it_handles_multiple_files_correctly(): void
    {
        $file1 = new MockSplFileInfo([
            'name' => 'File1.php',
            'realPath' => '/path/to/File1.php',
        ]);
        $file2 = new MockSplFileInfo([
            'name' => 'File2.php',
            'realPath' => '/path/to/File2.php',
        ]);

        $trace1 = $this->createTraceMock($file1);
        $trace2 = $this->createTraceMock($file2);

        $this->traceProviderMock
            ->method('provideTraces')
            ->willReturn([$trace1, $trace2]);

        $this->assertTrue($this->tracer->hasTrace($file1));
        $this->assertTrue($this->tracer->hasTrace($file2));

        $actualTrace1 = $this->tracer->trace($file1);
        $actualTrace2 = $this->tracer->trace($file2);

        $this->assertSame($trace1, $actualTrace1);
        $this->assertSame($trace2, $actualTrace2);
    }

    public function test_it_indexes_traces_by_pathname(): void
    {
        $fileInfo1 = new MockSplFileInfo([
            'name' => '/path/to/Foo.php',
            'realPath' => '/path/to/Foo.php',
        ]);

        $fileInfo2 = new MockSplFileInfo([
            'name' => '/path/to/Foo.php',
            'realPath' => '/path/to/Foo.php',
        ]);

        $expectedTrace = $this->createTraceMock($fileInfo1);

        $this->traceProviderMock
            ->expects($this->once())
            ->method('provideTraces')
            ->willReturn([$expectedTrace]);

        // Lookup with first file info
        $trace1 = $this->tracer->trace($fileInfo1);

        // Lookup with second file info that has same pathname
        $trace2 = $this->tracer->trace($fileInfo2);

        $this->assertSame($expectedTrace, $trace1);
        $this->assertSame($expectedTrace, $trace2);
    }

    public function test_it_handles_empty_trace_provider(): void
    {
        $fileInfo = new MockSplFileInfo([
            'name' => 'Foo.php',
            'realPath' => '/path/to/Foo.php',
        ]);

        $this->traceProviderMock
            ->method('provideTraces')
            ->willReturn([]);

        $this->assertFalse($this->tracer->hasTrace($fileInfo));
    }

    public function test_it_marks_traversal_as_complete_after_exhausting_generator(): void
    {
        $searchFile1 = new MockSplFileInfo([
            'name' => 'NotFound1.php',
            'realPath' => '/path/to/NotFound1.php',
        ]);

        $searchFile2 = new MockSplFileInfo([
            'name' => 'NotFound2.php',
            'realPath' => '/path/to/NotFound2.php',
        ]);

        $existingFile = new MockSplFileInfo([
            'name' => 'Existing.php',
            'realPath' => '/path/to/Existing.php',
        ]);

        $existingTrace = $this->createTraceMock($existingFile);

        $provideTracesCallCount = 0;
        $iterationCount = 0;

        $this->traceProviderMock
            ->method('provideTraces')
            ->willReturnCallback(static function () use ($existingTrace, &$provideTracesCallCount, &$iterationCount) {
                ++$provideTracesCallCount;

                ++$iterationCount;

                yield $existingTrace;
            });

        // First lookup - will exhaust the generator
        $this->assertFalse($this->tracer->hasTrace($searchFile1));

        // At this point, we should have iterated once
        $this->assertSame(1, $iterationCount);

        // Second lookup - should not iterate through generator again
        // because traversed flag should be set
        $this->assertFalse($this->tracer->hasTrace($searchFile2));

        // Verify we didn't iterate again
        $this->assertSame(1, $iterationCount);

        // Third lookup for yet another non-existent file
        // Should also use cached knowledge that we're fully traversed
        $searchFile3 = new MockSplFileInfo([
            'name' => 'NotFound3.php',
            'realPath' => '/path/to/NotFound3.php',
        ]);
        $this->assertFalse($this->tracer->hasTrace($searchFile3));

        // Still should not have iterated again
        $this->assertSame(1, $iterationCount);
        $this->assertSame(1, $provideTracesCallCount);
    }

    public function test_it_stops_iteration_when_generator_is_exhausted_mid_lookup(): void
    {
        $file1 = new MockSplFileInfo([
            'name' => 'File1.php',
            'realPath' => '/path/to/File1.php',
        ]);
        $file2 = new MockSplFileInfo([
            'name' => 'File2.php',
            'realPath' => '/path/to/File2.php',
        ]);

        $trace1 = $this->createTraceMock($file1);
        $trace2 = $this->createTraceMock($file2);

        $yieldCount = 0;

        $this->traceProviderMock
            ->method('provideTraces')
            ->willReturnCallback(static function () use ($trace1, $trace2, &$yieldCount) {
                ++$yieldCount;

                yield $trace1;

                ++$yieldCount;

                yield $trace2;
                // Generator is exhausted after this point
            });

        // Lookup a file that doesn't exist - will traverse entire generator
        $nonExistentFile = new MockSplFileInfo([
            'name' => 'NonExistent.php',
            'realPath' => '/path/to/NonExistent.php',
        ]);

        $this->assertFalse($this->tracer->hasTrace($nonExistentFile));

        // Both traces should have been yielded
        $this->assertSame(2, $yieldCount);

        // Lookup file1 - should be cached, no additional yields
        $this->assertTrue($this->tracer->hasTrace($file1));
        $this->assertSame(2, $yieldCount);
    }

    private function createTraceMock(SplFileInfo $fileInfo): Trace
    {
        $trace = $this->createMock(Trace::class);
        $trace->method('getSourceFileInfo')->willReturn($fileInfo);

        return $trace;
    }
}
