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

namespace Infection\Tests\TestFramework\Coverage;

use Infection\FileSystem\SourceFileFilter;
use Infection\TestFramework\Coverage\FilteredEnrichedTraceProvider;
use Infection\TestFramework\Coverage\JUnit\JUnitTestExecutionInfoAdder;
use Infection\TestFramework\Coverage\Trace;
use Infection\TestFramework\Coverage\TraceProvider;
use PHPUnit\Framework\TestCase;
use function Pipeline\take;
use Symfony\Component\Finder\SplFileInfo;
use Traversable;

final class FilteredEnrichedTraceProviderTest extends TestCase
{
    public function test_it_provides_traces(): void
    {
        $canary = [1, 2, 3];

        $traces = $this->createTraces(
            $canary,
            [$this->createMock(SplFileInfo::class)],
            true
        );

        $this->assertSame($canary, $traces);
        $this->assertCount(3, $canary);
    }

    public function test_it_takes_its_traces_from_the_decorated_trace_provider_and_not_the_provided_source_files(): void
    {
        $inputFileNames = [
            'src/Foo.php',
            'src/Bar.php',
        ];

        $expectedFileNames = [
            'src/Foo.php',
            'src/Bar.php',
            'src/Baz.php',
            'src/Test/Foo.php',
        ];

        $traces = $this->createTraces(
            take($inputFileNames)->map(function (string $filename) {
                return $this->createTraceMock($filename);
            }),
            take($expectedFileNames)->map(function (string $filename) {
                return $this->createFileInfoMock($filename);
            }),
            false
        );

        $actualFileNames = take($traces)
            ->map(static function (Trace $trace) {
                return $trace->getSourceFileInfo()->getRealPath();
            })
            ->toArray()
        ;

        $this->assertSame($expectedFileNames, $actualFileNames);
    }

    public function test_it_appends_the_missing_source_files_as_uncovered_traces(): void
    {
        $traces = $this->createTraces(
            [],
            [$this->createFileInfoMock('src/Foo.php')],
            false
        );

        if ($traces instanceof Traversable) {
            $traces = iterator_to_array($traces);
        }

        /** @var Trace $uncoveredTrace */
        $uncoveredTrace = $traces[0];

        $this->assertFalse($uncoveredTrace->hasTests());
    }

    public function test_it_does_not_append_missing_sources_files_as_uncovered_traces_if_only_covered_is_enabled(): void
    {
        $expectedFileNames = [
            'src/Foo.php',
            'src/Bar.php',
        ];

        $inputFileNames = [
            'src/Foo.php',
            'src/Bar.php',
            'src/Baz.php',
            'src/Test/Foo.php',
        ];

        $providedFiles = $this->createTraces(
            take($expectedFileNames)->map(function (string $filename) {
                return $this->createTraceMock($filename);
            }),
            take($inputFileNames)->map(function (string $filename) {
                return $this->createFileInfoMock($filename);
            }),
            true
        );

        $traces = take($providedFiles)
            ->map(static function (Trace $trace) {
                return $trace->getSourceFileInfo()->getRealPath();
            })
            ->toArray()
        ;

        $this->assertSame($expectedFileNames, $traces);
    }

    private function createTraces(
        iterable $canary,
        iterable $sourceFiles,
        bool $onlyCovered
    ): iterable {
        $traceProviderMock = $this->createMock(TraceProvider::class);
        $traceProviderMock
            ->expects($this->once())
            ->method('provideTraces')
            ->willReturn($canary)
        ;

        $filter = $this->createMock(SourceFileFilter::class);
        $filter
            ->expects($this->exactly(2))
            ->method('filter')
            ->withConsecutive(
                [$sourceFiles],
                [$canary]
            )
            ->willReturnOnConsecutiveCalls(
                $sourceFiles,
                $canary
            )
        ;

        $testFileDataAdder = $this->createMock(JUnitTestExecutionInfoAdder::class);
        $testFileDataAdder
            ->expects($this->once())
            ->method('addTestExecutionInfo')
            ->with($canary)
            ->willReturn($canary)
        ;

        $provider = new FilteredEnrichedTraceProvider(
            $traceProviderMock,
            $testFileDataAdder,
            $filter,
            $sourceFiles,
            $onlyCovered
        );

        return $provider->provideTraces();
    }

    private function createTraceMock(string $filename): Trace
    {
        $fileInfoMock = $this->createFileInfoMock($filename);

        $traceMock = $this->createMock(Trace::class);
        $traceMock
            ->method('getSourceFileInfo')
            ->willReturn($fileInfoMock)
        ;

        return $traceMock;
    }

    private function createFileInfoMock(string $filename): SplFileInfo
    {
        $fileInfoMock = $this->createMock(SplFileInfo::class);
        $fileInfoMock
            ->method('getRealPath')
            ->willReturn($filename)
        ;

        return $fileInfoMock;
    }
}
