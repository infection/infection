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

use function count;
use Infection\Telemetry\OpenTelemetryMetrics;
use Infection\Telemetry\OpenTelemetryTracer;
use Infection\Telemetry\SpanHandle;
use Infection\Tests\Telemetry\SDK\Clock\IncrementalClock;
use Infection\Tests\Telemetry\SDK\Metrics\MetricExporter\TestExporter as MetricsTestExporter;
use Infection\Tests\Telemetry\SDK\Trace\SpanExporter\TestExporter;
use OpenTelemetry\API\Common\Time\Clock;
use OpenTelemetry\API\Trace\NoopTracer;
use OpenTelemetry\API\Trace\SpanContextValidator;
use OpenTelemetry\SDK\Metrics\MeterProvider;
use OpenTelemetry\SDK\Metrics\MetricReader\ExportingReader;
use OpenTelemetry\SDK\Trace\SpanDataInterface;
use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor;
use OpenTelemetry\SDK\Trace\TracerProvider;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use function sprintf;

#[Group('integration')]
#[CoversClass(OpenTelemetryTracer::class)]
#[CoversClass(SpanHandle::class)]
final class OpenTelemetryTracerTest extends TestCase
{
    private const int CLOCK_START_NANOS = 1_000_000_000;

    private const int CLOCK_TICK_NANOS = 1_000_000_000;

    private const float RUN_DURATION = 9.0;

    private const int GENERATED_MUTATIONS_COUNT = 13;

    private const float MUTATION_RUNTIME = 0.42;

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
            Clock::getDefault(),
            NoopOpenTelemetryMetricsFactory::create(),
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

        Assert::assertSame(SpanContextValidator::INVALID_SPAN, $rootSpan->getParentSpanId());
        Assert::assertSame($rootSpan->getSpanId(), $childSpan->getParentSpanId());
        Assert::assertTrue($rootSpan->hasEnded());
        Assert::assertTrue($childSpan->hasEnded());
        Assert::assertTrue($rootSpan->getAttributes()->get('infection.root.started'));
        Assert::assertSame(1, $childSpan->getAttributes()->get('infection.child.started'));
        Assert::assertSame('yes', $childSpan->getAttributes()->get('infection.child.finished'));
    }

    public function test_it_shuts_down_the_tracer_provider(): void
    {
        $this->tracer->shutdown();

        $span = $this->tracerProvider
            ->getTracer('infection')
            ->spanBuilder('infection.after_shutdown')
            ->startSpan();
        $span->end();

        Assert::assertSame([], $this->exporter->getSpans());
        Assert::assertFalse($this->tracerProvider->getTracer('infection')->isEnabled());
    }

    public function test_it_can_be_used_without_a_tracer_provider(): void
    {
        $tracer = new OpenTelemetryTracer(
            NoopTracer::getInstance(),
            null,
            Clock::getDefault(),
            NoopOpenTelemetryMetricsFactory::create(),
        );

        $tracer->shutdown();

        Assert::assertSame([], $this->exporter->getSpans());
    }

    public function test_it_records_metrics_from_ended_spans(): void
    {
        $metricsExporter = new MetricsTestExporter();
        $meterProvider = MeterProvider::builder()
            ->addReader(new ExportingReader($metricsExporter))
            ->build();
        $telemetry = new OpenTelemetryTracer(
            $this->tracerProvider->getTracer('infection'),
            $this->tracerProvider,
            new IncrementalClock(
                self::CLOCK_START_NANOS,
                self::CLOCK_TICK_NANOS,
            ),
            new OpenTelemetryMetrics(
                $meterProvider->getMeter('infection'),
                $meterProvider,
            ),
        );

        $run = $telemetry->startRootSpan(
            'infection.run',
            [
                'infection.project.name' => 'acme/project',
                'infection.version' => '1.2.3',
                'infection.distribution' => 'source',
                'infection.thread.count' => 4,
                'infection.run.source_filtered' => false,
                'infection.run.progress_enabled' => true,
                'infection.timeouts_as_escaped' => false,
                'infection.initial_tests.skipped' => false,
                'infection.initial_static_analysis.skipped' => true,
                'infection.test_framework.name' => 'phpunit',
            ],
        );

        $phase = $telemetry->startChildSpan($run, 'infection.mutation_generation');
        $telemetry->end(
            $phase,
            [
                'infection.source_file.count' => 8,
                'infection.mutated_file.count' => 5,
                'infection.mutation.generated.count' => self::GENERATED_MUTATIONS_COUNT,
            ],
        );

        $mutation = $telemetry->startChildSpan($run, 'infection.mutation_evaluation.mutation');
        $telemetry->end(
            $mutation,
            [
                'infection.mutation.status' => 'escaped',
                'infection.mutation.runtime' => self::MUTATION_RUNTIME,
                'infection.mutation.msi.category' => 'not_covered',
                'infection.mutation.id' => 'should-not-be-a-metric-attribute',
                'code.file.path' => 'src/Foo.php',
            ],
        );

        $process = $telemetry->startChildSpan(
            $run,
            'infection.mutation_evaluation.mutant_analysis.evaluation.process',
            [
                'infection.mutation.process.test_framework' => 'phpunit',
                'infection.mutation.process.thread' => 2,
            ],
        );
        $telemetry->end(
            $process,
            [
                'infection.mutation.process.timed_out' => true,
                'process.exit.code' => 1,
            ],
        );

        $reporter = $telemetry->startChildSpan(
            $run,
            'infection.reporting.reporter',
            ['infection.reporter.name' => 'show_metrics'],
        );
        $telemetry->end($reporter);

        $telemetry->end(
            $run,
            [
                'infection.mutation.evaluated.count' => 1,
                'infection.mutation.not_covered.count' => 1,
                'infection.msi.value' => 0.0,
                'infection.mutation.coverage_rate.value' => 100.0,
                'infection.covered_msi.value' => 0.0,
            ],
        );
        $meterProvider->forceFlush();

        $metricsExporter->assertSameHistogramValue(
            'infection.run.duration',
            self::RUN_DURATION,
        );
        $metricsExporter->assertSameCounterValue(
            'infection.run.count',
            1,
        );
        $metricsExporter->assertSameHistogramValue(
            'infection.phase.duration',
            1.0,
            ['infection.phase.name' => 'mutation_generation'],
        );
        $metricsExporter->assertSameHistogramValue(
            'infection.mutation.generated.count',
            self::GENERATED_MUTATIONS_COUNT,
        );
        $metricsExporter->assertSameHistogramValue(
            'infection.mutation.evaluation.duration',
            1.0,
            ['infection.mutation.status' => 'escaped'],
        );
        $metricsExporter->assertSameHistogramValue(
            'infection.mutation.runtime',
            self::MUTATION_RUNTIME,
            ['infection.mutation.msi.category' => 'not_covered'],
        );
        $metricsExporter->assertSameCounterValue(
            'infection.mutation.count',
            1,
            ['infection.mutation.status' => 'escaped'],
        );
        $metricsExporter->assertSameHistogramValue(
            'infection.mutant.process.duration',
            1.0,
            ['infection.mutation.process.thread' => 2],
        );
        $metricsExporter->assertSameCounterValue(
            'infection.mutant.process.count',
            1,
            ['infection.mutation.process.timed_out' => true],
        );
        $metricsExporter->assertSameHistogramValue(
            'infection.reporter.duration',
            1.0,
            ['infection.reporter.name' => 'show_metrics'],
        );
        $metricsExporter->assertSameHistogramValue(
            'infection.msi',
            0.0,
        );
        $metricsExporter->assertNoDataPointHasAttribute('infection.mutation.id');
        $metricsExporter->assertNoDataPointHasAttribute('code.file.path');
    }

    private function getSpanFromExporter(string $name): SpanDataInterface
    {
        $matchingSpans = $this->exporter->getSpansByName($name);

        Assert::assertCount(
            1,
            $matchingSpans,
            sprintf(
                'Expected exactly one span named "%s", got %d.',
                $name,
                count($matchingSpans),
            ),
        );

        return $matchingSpans[0];
    }
}
