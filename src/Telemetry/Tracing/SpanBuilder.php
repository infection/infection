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

namespace Infection\Telemetry\Tracing;

use function array_map;
use Infection\Telemetry\Metric\Snapshot;
use Infection\Telemetry\Tracing\Throwable\AlreadyEndedSpan;
use Infection\Telemetry\Tracing\Throwable\UnendedSpan;

/**
 * A span is a single unit of work. This builder is used to open a span. Once
 * the span is ended, it can build its Span object.
 *
 * This class is meant as an internal tool of the Tracer.
 *
 * @see Span
 * @see Tracer
 * @see https://opentelemetry.io/docs/specs/otel/overview/#spans
 *
 * @internal
 */
final class SpanBuilder
{
    private Snapshot $end;

    /**
     * @var list<SpanBuilder>
     */
    private array $children = [];

    public function __construct(
        public readonly SpanId $id,
        private readonly Snapshot $start,
    ) {
    }

    /** @internal Should only be used by the Tracer */
    public function end(Snapshot $end): void
    {
        $this->assertSpanWasNotEnded();
        $this->assertAllChildrenSpansWereEnded();

        $this->end = $end;
    }

    public function addChild(self $span): void
    {
        $this->children[] = $span;
    }

    public function build(): Span
    {
        $this->assertSpanWasEnded();

        return new Span(
            $this->id,
            $this->id->scopeId,
            $this->id->scope,
            $this->start,
            $this->end,
            array_map(
                static fn (SpanBuilder $child) => $child->build(),
                $this->children,
            ),
        );
    }

    /**
     * @throws UnendedSpan
     */
    private function assertSpanWasEnded(): void
    {
        if (!isset($this->end)) {
            throw UnendedSpan::create($this->id);
        }
    }

    /**
     * @throws AlreadyEndedSpan
     */
    private function assertSpanWasNotEnded(): void
    {
        if (isset($this->end)) {
            throw AlreadyEndedSpan::create($this->id);
        }
    }

    /**
     * @throws UnendedSpan
     */
    private function assertAllChildrenSpansWereEnded(): void
    {
        foreach ($this->children as $child) {
            $child->assertSpanWasEnded();
        }
    }
}
