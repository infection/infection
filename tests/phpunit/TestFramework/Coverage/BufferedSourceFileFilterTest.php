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

use function array_values;
use Infection\TestFramework\Coverage\BufferedSourceFileFilter;
use Infection\TestFramework\Tracing\Trace\Trace;
use Infection\Tests\Fixtures\Finder\MockSplFileInfo;
use function iterator_to_array;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(BufferedSourceFileFilter::class)]
final class BufferedSourceFileFilterTest extends TestCase
{
    public function test_it_filters_and_collects_unseen(): void
    {
        $expectedUncoveredFiles = [
            'bar.php' => $this->createFileInfoMock('bar.php'),
        ];

        $sourceFiles = [
            $this->createFileInfoMock('foo.php'),
            $expectedUncoveredFiles['bar.php'],
            $this->createFileInfoMock('baz.php'),
        ];

        $traces = [
            $trace1 = $this->createTraceMock('foo.php'),
            // no trace for bar.php
            $trace2 = $this->createTraceMock('baz.php'),
            $this->createTraceMock('not-part-of-source-files.php'),
        ];

        $expectedTraces = [
            $trace1,
            $trace2,
        ];

        $bufferedFilter = BufferedSourceFileFilter::create($sourceFiles);

        $actualTraces = iterator_to_array($bufferedFilter->filter($traces), preserve_keys: false);
        $actualUncoveredFiles = $bufferedFilter->getUnseenInCoverageReportFiles();

        $this->assertSame($expectedTraces, $actualTraces);

        $this->assertSame(
            array_values($expectedUncoveredFiles),
            array_values($actualUncoveredFiles),
        );
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

    private function createFileInfoMock(string $filename): MockSplFileInfo
    {
        return new MockSplFileInfo([
            'realPath' => $filename,
        ]);
    }
}
