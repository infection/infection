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

use ArrayIterator;
use Infection\FileSystem\SourceFileFilter;
use Infection\TestFramework\Coverage\CoveredFileNameFilter;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Infection\TestFramework\Coverage\CoveredFileNameFilter
 */
final class CoveredFileNameFilterTest extends TestCase
{
    public function test_it_filters_array(): void
    {
        $expected = [3, 2, 1];

        $filter = $this->createMock(SourceFileFilter::class);
        $filter
            ->expects($this->once())
            ->method('filter')
            ->with([1, 2, 3])
            ->willReturn($expected)
        ;

        $fileNameFilter = new CoveredFileNameFilter($filter);

        /** @var array $actual */
        $actual = $fileNameFilter->filter([1, 2, 3]);

        $this->assertSame($expected, $actual);
    }

    public function test_it_filters_iterable(): void
    {
        $iterator = new ArrayIterator();

        $filter = $this->createMock(SourceFileFilter::class);
        $filter
            ->expects($this->once())
            ->method('filter')
            ->with($iterator)
            ->willReturn($iterator)
        ;

        $fileNameFilter = new CoveredFileNameFilter($filter);

        $actual = $fileNameFilter->filter($iterator);

        $this->assertSame($iterator, $actual);
    }
}
