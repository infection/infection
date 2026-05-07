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

namespace Infection\Tests\Telemetry;

use Infection\Telemetry\OpenTelemetryTracer;
use Infection\Telemetry\SpanHandle;
use OpenTelemetry\API\Trace\NoopTracer;
use OpenTelemetry\API\Trace\SpanContextValidator;
use OpenTelemetry\SDK\Trace\SpanDataInterface;
use OpenTelemetry\SDK\Trace\SpanExporter\InMemoryExporter;
use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor;
use OpenTelemetry\SDK\Trace\TracerProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use function sprintf;

#[Group('integration')]
#[CoversClass(OpenTelemetryTracer::class)]
#[CoversClass(SpanHandle::class)]
final class OpenTelemetryTracerTest extends TestCase
{
    private InMemoryExporter $exporter;

    private TracerProvider $tracerProvider;

    private OpenTelemetryTracer $tracer;

    protected function setUp(): void
    {
        $this->exporter = new InMemoryExporter();
        $this->tracerProvider = new TracerProvider(
            new SimpleSpanProcessor($this->exporter),
        );
        $this->tracer = new OpenTelemetryTracer(
            $this->tracerProvider->getTracer('infection'),
            $this->tracerProvider,
        );
    }

    protected function tearDown(): void
    {
        $this->tracerProvider->shutdown();
    }

    public function test_it_starts_and_ends_root_and_child_spans(): void
    {
        $root = $this->tracer->startRootSpan(
            'infection.root',
            ['infection.root.started' => true],
        );
        $child = $this->tracer->startChildSpan(
            $root,
            'infection.child',
            ['infection.child.started' => 1],
        );

        $this->tracer->end(
            $child,
            ['infection.child.finished' => 'yes'],
        );
        $this->tracer->end($root);

        $rootSpan = $this->getSpanFromExporter('infection.root');
        $childSpan = $this->getSpanFromExporter('infection.child');

        $this->assertSame(SpanContextValidator::INVALID_SPAN, $rootSpan->getParentSpanId());
        $this->assertSame($rootSpan->getSpanId(), $childSpan->getParentSpanId());
        $this->assertTrue($rootSpan->hasEnded());
        $this->assertTrue($childSpan->hasEnded());
        $this->assertTrue($rootSpan->getAttributes()->get('infection.root.started'));
        $this->assertSame(1, $childSpan->getAttributes()->get('infection.child.started'));
        $this->assertSame('yes', $childSpan->getAttributes()->get('infection.child.finished'));
    }

    public function test_it_shuts_down_the_tracer_provider(): void
    {
        $this->tracer->shutdown();

        $span = $this->tracerProvider
            ->getTracer('infection')
            ->spanBuilder('infection.after_shutdown')
            ->startSpan();
        $span->end();

        $this->assertSame([], $this->exporter->getSpans());
        $this->assertFalse($this->tracerProvider->getTracer('infection')->isEnabled());
    }

    public function test_it_can_be_used_without_a_tracer_provider(): void
    {
        $tracer = new OpenTelemetryTracer(
            NoopTracer::getInstance(),
            null,
        );

        $tracer->shutdown();

        $this->assertSame([], $this->exporter->getSpans());
    }

    private function getSpanFromExporter(string $name): SpanDataInterface
    {
        /** @var SpanDataInterface $span */
        foreach ($this->exporter->getSpans() as $span) {
            if ($span->getName() === $name) {
                return $span;
            }
        }

        $this->fail(
            sprintf(
                'Span "%s" was not exported.',
                $name,
            ),
        );
    }
}
