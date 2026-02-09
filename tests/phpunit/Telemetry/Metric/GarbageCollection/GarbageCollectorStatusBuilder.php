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

namespace Infection\Tests\Telemetry\Metric\GarbageCollection;

use Infection\Telemetry\Metric\GarbageCollection\GarbageCollectorStatus;

final class GarbageCollectorStatusBuilder
{
    private function __construct(
        private int $runs,
        private int $collected,
        private int $threshold,
        private int $roots,
        private ?float $applicationTime,
        private ?float $collectorTime,
        private ?float $destructorTime,
        private ?float $freeTime,
        private ?bool $running,
        private ?bool $protected,
        private ?bool $full,
        private ?int $bufferSize,
    ) {
    }

    public static function from(GarbageCollectorStatus $status): self
    {
        return new self(
            $status->runs,
            $status->collected,
            $status->threshold,
            $status->roots,
            $status->applicationTime,
            $status->collectorTime,
            $status->destructorTime,
            $status->freeTime,
            $status->running,
            $status->protected,
            $status->full,
            $status->bufferSize,
        );
    }

    public static function withTestDataForPhp82OrLess(): self
    {
        return new self(
            runs: 0,
            collected: 0,
            threshold: 10_000,
            roots: 0,
            applicationTime: null,
            collectorTime: null,
            destructorTime: null,
            freeTime: null,
            running: null,
            protected: null,
            full: null,
            bufferSize: null,
        );
    }

    public static function withTestData(): self
    {
        return new self(
            runs: 5,
            collected: 100,
            threshold: 10000,
            roots: 50,
            applicationTime: 1.234567,
            collectorTime: 0.123456,
            destructorTime: 0.012345,
            freeTime: 0.011111,
            running: false,
            protected: false,
            full: false,
            bufferSize: 16384,
        );
    }

    public function withRuns(int $runs): self
    {
        $clone = clone $this;
        $clone->runs = $runs;

        return $clone;
    }

    public function withCollected(int $collected): self
    {
        $clone = clone $this;
        $clone->collected = $collected;

        return $clone;
    }

    public function withThreshold(int $threshold): self
    {
        $clone = clone $this;
        $clone->threshold = $threshold;

        return $clone;
    }

    public function withRoots(int $roots): self
    {
        $clone = clone $this;
        $clone->roots = $roots;

        return $clone;
    }

    public function withApplicationTime(?float $applicationTime): self
    {
        $clone = clone $this;
        $clone->applicationTime = $applicationTime;

        return $clone;
    }

    public function withCollectorTime(?float $collectorTime): self
    {
        $clone = clone $this;
        $clone->collectorTime = $collectorTime;

        return $clone;
    }

    public function withDestructorTime(?float $destructorTime): self
    {
        $clone = clone $this;
        $clone->destructorTime = $destructorTime;

        return $clone;
    }

    public function withFreeTime(?float $freeTime): self
    {
        $clone = clone $this;
        $clone->freeTime = $freeTime;

        return $clone;
    }

    public function withRunning(?bool $running): self
    {
        $clone = clone $this;
        $clone->running = $running;

        return $clone;
    }

    public function withProtected(?bool $protected): self
    {
        $clone = clone $this;
        $clone->protected = $protected;

        return $clone;
    }

    public function withFull(?bool $full): self
    {
        $clone = clone $this;
        $clone->full = $full;

        return $clone;
    }

    public function withBufferSize(?int $bufferSize): self
    {
        $clone = clone $this;
        $clone->bufferSize = $bufferSize;

        return $clone;
    }

    public function build(): GarbageCollectorStatus
    {
        return new GarbageCollectorStatus(
            $this->runs,
            $this->collected,
            $this->threshold,
            $this->roots,
            $this->applicationTime,
            $this->collectorTime,
            $this->destructorTime,
            $this->freeTime,
            $this->running,
            $this->protected,
            $this->full,
            $this->bufferSize,
        );
    }
}
