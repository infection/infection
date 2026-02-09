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

use Infection\Source\Collector\CachedSourceCollector;
use Infection\Source\Collector\SourceCollector;
use Infection\Tests\TestingUtility\FileSystem\MockSplFileInfo;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(CachedSourceCollector::class)]
final class CachedSourceCollectorTest extends TestCase
{
    private SourceCollector&MockObject $decoratedCollectorMock;

    private CachedSourceCollector $collector;

    protected function setUp(): void
    {
        $this->decoratedCollectorMock = $this->createMock(SourceCollector::class);

        $this->collector = new CachedSourceCollector(
            $this->decoratedCollectorMock,
        );
    }

    public function test_it_caches_the_collected_files(): void
    {
        $expected = [
            new MockSplFileInfo('src/File1.php'),
            new MockSplFileInfo('src/File2.php'),
        ];

        $this->decoratedCollectorMock
            ->expects($this->once())
            ->method('collect')
            ->willReturn($expected);

        $actual1 = $this->collector->collect();
        $actual2 = $this->collector->collect();

        $this->assertSame($expected, $actual1);
        $this->assertSame($actual1, $actual2);
    }
}
