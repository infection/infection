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

use Infection\Telemetry\Metric\GarbageCollection\GarbageCollectorStatus;
use Infection\Telemetry\Metric\Memory\MemoryUsage;
use Infection\Telemetry\Metric\Snapshot;
use Infection\Telemetry\Metric\Time\HRTime;
use Infection\Tests\Telemetry\Metric\GarbageCollection\GarbageCollectorStatusBuilder;

final class SnapshotBuilder
{
    private function __construct(
        private HRTime $time,
        private MemoryUsage $memoryUsage,
        private MemoryUsage $peakMemoryUsage,
        private GarbageCollectorStatus $garbageCollectorStatus,
    ) {
    }

    public static function from(Snapshot $snapshot): self
    {
        return new self(
            $snapshot->time,
            $snapshot->memoryUsage,
            $snapshot->peakMemoryUsage,
            $snapshot->garbageCollectorStatus,
        );
    }

    public static function withTestData(): self
    {
        return new self(
            time: HRTime::fromSecondsAndNanoseconds(0, 0),
            memoryUsage: MemoryUsage::fromBytes(1_048_576),
            peakMemoryUsage: MemoryUsage::fromBytes(1_048_576),
            garbageCollectorStatus: GarbageCollectorStatusBuilder::withTestData()->build(),
        );
    }

    public function withTime(HRTime $time): self
    {
        $clone = clone $this;
        $clone->time = $time;

        return $clone;
    }

    public function withMemoryUsage(MemoryUsage $memoryUsage): self
    {
        $clone = clone $this;
        $clone->memoryUsage = $memoryUsage;

        return $clone;
    }

    public function withPeakMemoryUsage(MemoryUsage $peakMemoryUsage): self
    {
        $clone = clone $this;
        $clone->peakMemoryUsage = $peakMemoryUsage;

        return $clone;
    }

    public function withGarbageCollectorStatus(GarbageCollectorStatus $garbageCollectorStatus): self
    {
        $clone = clone $this;
        $clone->garbageCollectorStatus = $garbageCollectorStatus;

        return $clone;
    }

    public function build(): Snapshot
    {
        return new Snapshot(
            $this->time,
            $this->memoryUsage,
            $this->peakMemoryUsage,
            $this->garbageCollectorStatus,
        );
    }
}
