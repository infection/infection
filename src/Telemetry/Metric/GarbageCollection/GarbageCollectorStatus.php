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

namespace Infection\Telemetry\Metric\GarbageCollection;

/**
 * Represents the state provided by `gc_status()`.
 *
 * @see https://www.php.net/manual/en/function.gc-status.php
 *
 * @internal
 */
final readonly class GarbageCollectorStatus
{
    public function __construct(
        // Number of times the garbage collector has run.
        public int $runs,
        // The number of objects collected.
        public int $collected,
        // The number of roots in the buffer which will trigger garbage collection.
        public int $threshold,
        // The current number of roots in the buffer.
        public int $roots,
        // TODO: make it non-nullable when we make Infection require PHP 8.3+.
        //  meanwhile null=info not available.
        // Total application time, in seconds. Including collector_time.
        public ?float $applicationTime,
        // Time spent collecting cycles, in seconds. Includes destructor_time and free_time.
        public ?float $collectorTime,
        // Time spent executing destructors during a cycle collection, in seconds. Subset of collectorTime.
        public ?float $destructorTime,
        // Time spent freeing values during a cycle collection, in seconds. Subset of collectorTime.
        public ?float $freeTime,
        public ?bool $running,
        // Whether the garbage collector is protected and root additions are forbidden.
        public ?bool $protected,
        // Whether the root buffer size exceeded internal limits (GC_MAX_BUF_SIZE)
        public ?bool $full,
        // Current garbage collector buffer size.
        public ?int $bufferSize,
    ) {
    }
}
