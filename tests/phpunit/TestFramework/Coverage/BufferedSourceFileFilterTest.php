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

use Infection\FileSystem\FileFilter;
use Infection\TestFramework\Coverage\BufferedSourceFileFilter;
use Infection\TestFramework\Coverage\Trace;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\SplFileInfo;

final class BufferedSourceFileFilterTest extends TestCase
{
    public function test_it_filters_and_collects_unseen(): void
    {
        $uncoveredFiles = [
            'bar.php' => $this->createFileInfoMock('bar.php'),
        ];

        $sourceFiles = [
            $this->createFileInfoMock('foo.php'),
            $uncoveredFiles['bar.php'],
            $this->createFileInfoMock('baz.php'),
        ];

        $traces = [
            $this->createTraceMock('foo.php'),
            $this->createTraceMock('baz.php'),
        ];

        $filter = $this->createMock(FileFilter::class);
        $filter
            ->expects($this->exactly(2))
            ->method('filter')
            ->withConsecutive(
                [$traces],
                [$uncoveredFiles]
            )
            ->willReturnOnConsecutiveCalls(
                $traces,
                $uncoveredFiles
            )
        ;

        $bufferedFilter = new BufferedSourceFileFilter($filter, $sourceFiles);

        $this->assertSame($traces, iterator_to_array($bufferedFilter->filter($traces), false));

        $this->assertSame(array_values($uncoveredFiles), array_values($bufferedFilter->getUnseenInCoverageReportFiles()));
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
