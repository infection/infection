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

namespace Infection\Tests\Telemetry\SDK\Trace\Tracer;

use Infection\Telemetry\OpenTelemetryTracer;
use Infection\Tests\Telemetry\SDK\Clock\IncrementalClock;
use Infection\Tests\Telemetry\SDK\Trace\Span\GuardedSpan;
use Infection\Tests\Telemetry\SDK\Trace\Span\GuardedSpanBuilder;
use OpenTelemetry\SDK\Trace\SpanExporter\InMemoryExporter;
use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor;
use OpenTelemetry\SDK\Trace\TracerProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Webmozart\Assert\InvalidArgumentException;

#[CoversClass(GuardedTracer::class)]
#[CoversClass(GuardedSpanBuilder::class)]
#[CoversClass(GuardedSpan::class)]
final class GuardedTracerTest extends TestCase
{
    private TracerProvider $tracerProvider;

    private GuardedTracer $guardedTracer;

    private OpenTelemetryTracer $telemetry;

    protected function setUp(): void
    {
        $this->tracerProvider = new TracerProvider(
            new SimpleSpanProcessor(new InMemoryExporter()),
        );
        $this->guardedTracer = new GuardedTracer(
            $this->tracerProvider->getTracer('infection'),
        );
        $this->telemetry = new OpenTelemetryTracer(
            $this->guardedTracer,
            $this->tracerProvider,
            new IncrementalClock(10, 10),
        );
    }

    protected function tearDown(): void
    {
        $this->tracerProvider->shutdown();
    }

    public function test_it_allows_spans_to_be_closed_after_their_children(): void
    {
        $root = $this->telemetry->startRootSpan('infection.run');
        $child = $this->telemetry->startChildSpan($root, 'infection.child');

        $this->telemetry->end($child);
        $this->telemetry->end($root);

        $this->guardedTracer->assertHasNoOpenSpans();
        $this->addToAssertionCount(1);
    }

    public function test_it_rejects_ending_a_parent_span_while_a_child_span_is_open(): void
    {
        $root = $this->telemetry->startRootSpan('infection.run');
        $this->telemetry->startChildSpan($root, 'infection.child');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            "Cannot end span \"infection.run\" while the following child spans are still open:\n- infection.child",
        );

        $this->telemetry->end($root);
    }

    public function test_it_reports_open_spans(): void
    {
        $this->telemetry->startRootSpan('infection.run');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            "Expected all spans to be closed, but the following spans are still open:\n- infection.run",
        );

        $this->guardedTracer->assertHasNoOpenSpans();
    }

    public function test_it_rejects_ending_the_same_span_twice(): void
    {
        $root = $this->telemetry->startRootSpan('infection.run');

        $this->telemetry->end($root);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Span "infection.run" was ended more than once.');

        $this->telemetry->end($root);
    }

    public function test_it_rejects_starting_a_child_span_from_a_closed_parent(): void
    {
        $root = $this->telemetry->startRootSpan('infection.run');

        $this->telemetry->end($root);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Cannot start span "infection.child" as child of already closed span "infection.run".',
        );

        $this->telemetry->startChildSpan($root, 'infection.child');
    }
}
