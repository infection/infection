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

namespace Infection\Tests\Telemetry\Tracing;

use Infection\Telemetry\Metric\Snapshot;
use Infection\Telemetry\Tracing\RootScope;
use Infection\Telemetry\Tracing\Scope;
use Infection\Telemetry\Tracing\Span;
use Infection\Telemetry\Tracing\SpanId;
use Infection\Tests\Telemetry\Metric\SnapshotBuilder;

final class SpanBuilder
{
    /**
     * @param list<Span> $children
     */
    private function __construct(
        private SpanId $id,
        private Snapshot $start,
        private Snapshot $end,
        private array $children,
    ) {
    }

    public static function from(Span $span): self
    {
        return new self(
            $span->id,
            $span->start,
            $span->end,
            $span->children,
        );
    }

    public static function withRootTestData(): self
    {
        return new self(
            id: SpanId::create(
                RootScope::ARTEFACT_COLLECTION,
                'testId(abc)',
            ),
            start: SnapshotBuilder::withTestData()->build(),
            end: SnapshotBuilder::withTestData()->build(),
            children: [
                self::withChildTestData()->build(),
                self::withChildTestData()->build(),
            ],
        );
    }

    public static function withChildTestData(): self
    {
        return new self(
            id: SpanId::create(
                Scope::AST_GENERATION,
                'testId(abc)',
            ),
            start: SnapshotBuilder::withTestData()->build(),
            end: SnapshotBuilder::withTestData()->build(),
            children: [],
        );
    }

    public function withId(SpanId $id): self
    {
        $clone = clone $this;
        $clone->id = $id;

        return $clone;
    }

    public function withStart(Snapshot $start): self
    {
        $clone = clone $this;
        $clone->start = $start;

        return $clone;
    }

    public function withEnd(Snapshot $end): self
    {
        $clone = clone $this;
        $clone->end = $end;

        return $clone;
    }

    public function withChildren(Span ...$children): self
    {
        $clone = clone $this;
        $clone->children = $children;

        return $clone;
    }

    public function build(): Span
    {
        return new Span(
            $this->id,
            $this->id->scopeId,
            $this->id->scope,
            $this->start,
            $this->end,
            $this->children,
        );
    }
}
