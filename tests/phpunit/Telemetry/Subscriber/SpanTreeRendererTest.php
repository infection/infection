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

namespace Infection\Tests\Telemetry\Subscriber;

use Infection\Telemetry\OpenTelemetryMetrics;
use Infection\Telemetry\OpenTelemetryTracer;
use Infection\Tests\Telemetry\SDK\Clock\IncrementalClock;
use Infection\Tests\Telemetry\SDK\Trace\SpanExporter\TestExporter;
use OpenTelemetry\SDK\Metrics\NoopMeterProvider;
use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor;
use OpenTelemetry\SDK\Trace\TracerProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SpanTreeRenderer::class)]
final class SpanTreeRendererTest extends TestCase
{
    private TestExporter $exporter;

    private TracerProvider $tracerProvider;

    private OpenTelemetryTracer $tracer;

    protected function setUp(): void
    {
        $this->exporter = new TestExporter();
        $this->tracerProvider = new TracerProvider(
            new SimpleSpanProcessor($this->exporter),
        );
        $this->tracer = new OpenTelemetryTracer(
            $this->tracerProvider->getTracer('infection'),
            $this->tracerProvider,
            new IncrementalClock(10, 10),
            self::createNoopMetrics(),
        );
    }

    protected function tearDown(): void
    {
        $this->tracerProvider->shutdown();
    }

    public function test_it_renders_spans_as_a_tree_ordered_by_start_time(): void
    {
        $root = $this->tracer->startRootSpan('infection.run');
        $artefactCollection = $this->tracer->startChildSpan(
            $root,
            'infection.artefact_collection',
        );
        $initialTests = $this->tracer->startChildSpan(
            $artefactCollection,
            'infection.initial_tests',
        );
        $initialStaticAnalysis = $this->tracer->startChildSpan(
            $artefactCollection,
            'infection.initial_static_analysis',
        );
        $mutationAnalysis = $this->tracer->startChildSpan(
            $root,
            'infection.mutation_analysis',
        );
        $astProcessing = $this->tracer->startChildSpan(
            $mutationAnalysis,
            'infection.ast_processing',
        );
        $astParsing = $this->tracer->startChildSpan(
            $astProcessing,
            'infection.ast_processing.file.parsing',
        );

        $this->tracer->end($astParsing);
        $this->tracer->end($astProcessing);
        $this->tracer->end($mutationAnalysis);
        $this->tracer->end($initialStaticAnalysis);
        $this->tracer->end($initialTests);
        $this->tracer->end($artefactCollection);
        $this->tracer->end($root);

        $expected = <<<'TXT'
            infection.run [10, 140]
              infection.artefact_collection [20, 130]
                infection.initial_tests [30, 120]
                infection.initial_static_analysis [40, 110]
              infection.mutation_analysis [50, 100]
                infection.ast_processing [60, 90]
                  infection.ast_processing.file.parsing [70, 80]
            TXT;

        $actual = SpanTreeRenderer::render($this->exporter->getSpans());

        $this->assertSame($expected, $actual);
    }

    private static function createNoopMetrics(): OpenTelemetryMetrics
    {
        $meterProvider = new NoopMeterProvider();

        return new OpenTelemetryMetrics(
            $meterProvider->getMeter('infection'),
            $meterProvider,
        );
    }
}
