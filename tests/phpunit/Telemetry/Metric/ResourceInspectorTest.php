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

namespace Infection\Tests\Telemetry\Metric;

use Infection\Telemetry\Metric\GarbageCollection\GarbageCollectorInspector;
use Infection\Telemetry\Metric\Memory\MemoryInspector;
use Infection\Telemetry\Metric\Memory\MemoryUsage;
use Infection\Telemetry\Metric\ResourceInspector;
use Infection\Telemetry\Metric\Snapshot;
use Infection\Telemetry\Metric\Time\HRTime;
use Infection\Telemetry\Metric\Time\Stopwatch;
use Infection\Tests\Telemetry\Metric\GarbageCollection\GarbageCollectorStatusBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ResourceInspector::class)]
final class ResourceInspectorTest extends TestCase
{
    public function test_it_can_do_a_snapshot_of_the_current_resources(): void
    {
        $hrtime = HRTime::fromSecondsAndNanoseconds(10, 100);

        $stopwatchMock = $this->createMock(Stopwatch::class);
        $stopwatchMock
            ->expects($this->once())
            ->method('current')
            ->willReturn($hrtime);

        $memoryUsage = MemoryUsage::fromBytes(1024);
        $peakMemoryUsage = MemoryUsage::fromBytes(2048);

        $memoryInspectorMock = $this->createMock(MemoryInspector::class);
        $memoryInspectorMock
            ->expects($this->once())
            ->method('readMemoryUsage')
            ->willReturn($memoryUsage);
        $memoryInspectorMock
            ->expects($this->once())
            ->method('readPeakMemoryUsage')
            ->willReturn($peakMemoryUsage);

        $garbageCollectorStatus = GarbageCollectorStatusBuilder::withTestData()->build();

        $garbageCollectorInspectorMock = $this->createMock(GarbageCollectorInspector::class);
        $garbageCollectorInspectorMock
            ->expects($this->once())
            ->method('readStatus')
            ->willReturn($garbageCollectorStatus);

        $expected = new Snapshot(
            $hrtime,
            $memoryUsage,
            $peakMemoryUsage,
            $garbageCollectorStatus,
        );

        $inspector = new ResourceInspector(
            $stopwatchMock,
            $memoryInspectorMock,
            $garbageCollectorInspectorMock,
        );

        $actual = $inspector->snapshot();

        $this->assertEquals($expected, $actual);
    }
}
