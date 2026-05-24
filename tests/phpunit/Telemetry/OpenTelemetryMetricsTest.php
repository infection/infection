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

use Infection\Telemetry\Attribute\RunSpanAttributesProvider;
use Infection\Telemetry\OpenTelemetryMetrics;
use Infection\Telemetry\SpanHandle;
use Infection\Tests\Telemetry\SDK\Metrics\MetricExporter\TestExporter;
use OpenTelemetry\API\Trace\Span;
use OpenTelemetry\SDK\Metrics\MeterProvider;
use OpenTelemetry\SDK\Metrics\MeterProviderInterface;
use OpenTelemetry\SDK\Metrics\MetricReader\ExportingReader;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @phpstan-import-type Attributes from RunSpanAttributesProvider
 */
#[CoversClass(OpenTelemetryMetrics::class)]
final class OpenTelemetryMetricsTest extends TestCase
{
    private const int SPAN_START_NANOS = 1_000_000_000;

    private const int SPAN_END_NANOS = 3_000_000_000;

    private const float SPAN_DURATION_SECONDS = 2.0;

    private const float MUTATION_RUNTIME_SECONDS = 0.42;

    private TestExporter $exporter;

    private MeterProviderInterface $meterProvider;

    private OpenTelemetryMetrics $metrics;

    protected function setUp(): void
    {
        $this->exporter = new TestExporter();
        $this->meterProvider = MeterProvider::builder()
            ->addReader(new ExportingReader($this->exporter))
            ->build();

        $this->metrics = new OpenTelemetryMetrics(
            $this->meterProvider->getMeter('infection'),
            $this->meterProvider,
        );
    }

    public function test_it_records_metrics_from_ended_spans(): void
    {
        $this->metrics->startRun(
            [
                'infection.project.name' => 'acme/project',
                'infection.version' => '1.2.3',
                'infection.thread.count' => 4,
            ],
        );

        $this->metrics->recordSpanEnded(
            self::createSpan('infection.mutation_evaluation.mutation'),
            self::SPAN_END_NANOS,
            [
                'infection.mutation.status' => 'escaped',
                'infection.mutation.runtime' => self::MUTATION_RUNTIME_SECONDS,
                'infection.mutation.msi.category' => 'not_covered',
                'infection.mutation.id' => 'should-not-be-a-metric-attribute',
                'code.file.path' => 'src/Foo.php',
            ],
        );
        $this->metrics->recordSpanEnded(
            self::createSpan('infection.run'),
            self::SPAN_END_NANOS,
            [
                'infection.mutation.evaluated.count' => 1,
                'infection.msi.value' => 0.0,
                'infection.mutation.coverage_rate.value' => 100.0,
                'infection.covered_msi.value' => 0.0,
            ],
        );
        $this->meterProvider->forceFlush();

        $this->exporter->assertSameHistogramValue(
            'infection.run.duration',
            self::SPAN_DURATION_SECONDS,
            ['infection.project.name' => 'acme/project'],
        );
        $this->exporter->assertSameCounterValue(
            'infection.run.count',
            1,
            ['infection.project.name' => 'acme/project'],
        );
        $this->exporter->assertSameHistogramValue(
            'infection.mutation.evaluation.duration',
            self::SPAN_DURATION_SECONDS,
            [
                'infection.project.name' => 'acme/project',
                'infection.mutation.status' => 'escaped',
            ],
        );
        $this->exporter->assertSameHistogramValue(
            'infection.mutation.runtime',
            self::MUTATION_RUNTIME_SECONDS,
            [
                'infection.project.name' => 'acme/project',
                'infection.mutation.msi.category' => 'not_covered',
            ],
        );
        $this->exporter->assertSameCounterValue(
            'infection.mutation.count',
            1,
            [
                'infection.project.name' => 'acme/project',
                'infection.mutation.status' => 'escaped',
            ],
        );
        $this->exporter->assertSameHistogramValue(
            'infection.mutation.evaluated.count',
            1,
        );
        $this->exporter->assertSameHistogramValue(
            'infection.msi',
            0.0,
        );
        $this->exporter->assertSameHistogramValue(
            'infection.mutation.coverage_rate',
            100.0,
        );
        $this->exporter->assertSameHistogramValue(
            'infection.covered_msi',
            0.0,
        );

        $this->exporter->assertNoDataPointHasAttribute('infection.mutation.id');
        $this->exporter->assertNoDataPointHasAttribute('code.file.path');
    }

    /**
     * @param non-empty-string $spanName
     */
    #[DataProvider('phaseSpanProvider')]
    public function test_it_records_phase_metrics_for_supported_span_names(
        string $spanName,
        string $phase,
    ): void {
        $this->metrics->startRun(
            [
                'infection.project.name' => 'acme/project',
                'infection.version' => '1.2.3',
                'infection.distribution' => 'source',
            ],
        );

        $this->metrics->recordSpanEnded(
            self::createSpan($spanName),
            self::SPAN_END_NANOS,
            [],
        );
        $this->meterProvider->forceFlush();

        $this->exporter->assertSameHistogramValue(
            'infection.phase.duration',
            self::SPAN_DURATION_SECONDS,
            [
                'infection.project.name' => 'acme/project',
                'infection.phase.name' => $phase,
            ],
        );
    }

    public static function phaseSpanProvider(): iterable
    {
        yield 'source collection' => ['infection.source_collection', 'source_collection'];

        yield 'artefact collection' => ['infection.artefact_collection', 'artefact_collection'];

        yield 'initial tests' => ['infection.initial_tests', 'initial_tests'];

        yield 'initial static analysis' => ['infection.initial_static_analysis', 'initial_static_analysis'];

        yield 'mutation analysis' => ['infection.mutation_analysis', 'mutation_analysis'];

        yield 'mutation generation' => ['infection.mutation_generation', 'mutation_generation'];

        yield 'AST processing' => ['infection.ast_processing', 'ast_processing'];

        yield 'mutation evaluation' => ['infection.mutation_evaluation', 'mutation_evaluation'];

        yield 'reporting' => ['infection.reporting', 'reporting'];
    }

    /**
     * @param non-empty-string $spanName
     */
    #[DataProvider('durationHistogramSpanProvider')]
    public function test_it_records_duration_histograms_for_supported_span_names(
        string $spanName,
        string $metricName,
        int $expectedDuration,
    ): void {
        $this->metrics->recordSpanEnded(
            self::createSpan($spanName),
            self::SPAN_END_NANOS,
            [],
        );
        $this->meterProvider->forceFlush();

        $this->exporter->assertSameHistogramValue(
            $metricName,
            $expectedDuration,
        );
    }

    public static function durationHistogramSpanProvider(): iterable
    {
        yield 'AST file' => ['infection.ast_processing.file', 'infection.ast.file.duration', 2];

        yield 'AST file parsing' => ['infection.ast_processing.file.parsing', 'infection.ast.file.parsing.duration', 2];

        yield 'AST file enrichment' => ['infection.ast_processing.file.enrichment', 'infection.ast.file.enrichment.duration', 2];

        yield 'mutant analysis' => ['infection.mutation_evaluation.mutant_analysis', 'infection.mutant.analysis.duration', 2];

        yield 'mutant materialisation' => ['infection.mutation_evaluation.mutant_analysis.materialisation', 'infection.mutant.materialisation.duration', 2];
    }

    public function test_it_records_metrics_for_specialized_supported_span_names(): void
    {
        $this->metrics->startRun(
            [
                'infection.project.name' => 'acme/project',
                'infection.version' => '1.2.3',
                'infection.distribution' => 'source',
            ],
        );

        $this->metrics->recordSpanEnded(
            self::createSpan('infection.mutation_evaluation.mutant_analysis.evaluation'),
            self::SPAN_END_NANOS,
            ['infection.mutation.queue_wait.duration' => 0.5],
        );
        $this->metrics->recordSpanEnded(
            self::createSpan('infection.mutation_evaluation.mutant_analysis.evaluation'),
            self::SPAN_END_NANOS,
            ['infection.mutation.queue_wait.duration' => 0.5],
        );
        $this->metrics->recordSpanEnded(
            self::createSpan('infection.mutation_evaluation.mutant_analysis.evaluation.process'),
            self::SPAN_END_NANOS,
            [
                'infection.mutation.process.test_framework' => 'phpunit',
                'infection.mutation.process.thread' => 1,
            ],
        );
        $this->metrics->recordSpanEnded(
            self::createSpan('infection.reporting.reporter'),
            self::SPAN_END_NANOS,
            ['infection.reporter.name' => 'summary'],
        );
        $this->meterProvider->forceFlush();

        $this->exporter->assertSameHistogramValue(
            'infection.mutant.evaluation.duration',
            self::SPAN_DURATION_SECONDS * 2,
            ['infection.project.name' => 'acme/project'],
        );
        $this->exporter->assertSameHistogramValue(
            'infection.mutation.queue_wait.duration',
            1.0,
            ['infection.project.name' => 'acme/project'],
        );
        $this->exporter->assertSameHistogramValue(
            'infection.mutant.process.duration',
            self::SPAN_DURATION_SECONDS,
            [
                'infection.project.name' => 'acme/project',
                'infection.mutation.process.test_framework' => 'phpunit',
                'infection.mutation.process.thread' => 1,
            ],
        );
        $this->exporter->assertSameCounterValue(
            'infection.mutant.process.count',
            1,
            [
                'infection.project.name' => 'acme/project',
                'infection.mutation.process.test_framework' => 'phpunit',
                'infection.mutation.process.thread' => 1,
            ],
        );
        $this->exporter->assertSameHistogramValue(
            'infection.reporter.duration',
            self::SPAN_DURATION_SECONDS,
            [
                'infection.project.name' => 'acme/project',
                'infection.reporter.name' => 'summary',
            ],
        );
    }

    public function test_it_ignores_unknown_span_names(): void
    {
        $this->metrics->recordSpanEnded(
            self::createSpan('infection.unknown'),
            self::SPAN_END_NANOS,
            [],
        );
        $this->meterProvider->forceFlush();

        $this->exporter->assertDataPointCount('infection.phase.duration', 0);
    }

    public function test_it_records_each_mutation_runtime_without_deduplicating_it(): void
    {
        $this->metrics->startRun(
            [
                'infection.project.name' => 'acme/project',
                'infection.version' => '1.2.3',
            ],
        );

        $this->metrics->recordSpanEnded(
            self::createSpan('infection.mutation_evaluation.mutation'),
            self::SPAN_END_NANOS,
            [
                'infection.mutation.status' => 'escaped',
                'infection.mutation.runtime' => self::MUTATION_RUNTIME_SECONDS,
                'infection.mutation.msi.category' => 'not_covered',
            ],
        );
        $this->metrics->recordSpanEnded(
            self::createSpan('infection.mutation_evaluation.mutation'),
            self::SPAN_END_NANOS,
            [
                'infection.mutation.status' => 'escaped',
                'infection.mutation.runtime' => self::MUTATION_RUNTIME_SECONDS,
                'infection.mutation.msi.category' => 'not_covered',
            ],
        );
        $this->meterProvider->forceFlush();

        $this->exporter->assertSameHistogramValue(
            'infection.mutation.runtime',
            self::MUTATION_RUNTIME_SECONDS * 2,
            [
                'infection.project.name' => 'acme/project',
                'infection.mutation.msi.category' => 'not_covered',
            ],
        );
    }

    public function test_it_deduplicates_summary_metrics_recorded_by_phase_and_run_spans(): void
    {
        $this->metrics->startRun(
            [
                'infection.project.name' => 'acme/project',
                'infection.version' => '1.2.3',
            ],
        );

        $this->metrics->recordSpanEnded(
            self::createSpan('infection.mutation_generation'),
            self::SPAN_END_NANOS,
            ['infection.mutation.generated.count' => 13],
        );
        $this->metrics->recordSpanEnded(
            self::createSpan('infection.run'),
            self::SPAN_END_NANOS,
            ['infection.mutation.generated.count' => 13],
        );
        $this->meterProvider->forceFlush();

        $this->exporter->assertSameHistogramValue(
            'infection.mutation.generated.count',
            13,
            ['infection.project.name' => 'acme/project'],
        );
    }

    #[DataProvider('phaseSummaryMetricProvider')]
    public function test_it_records_summary_metrics_from_phase_spans(
        string $metricName,
        int $value,
    ): void {
        $this->metrics->startRun(
            [
                'infection.project.name' => 'acme/project',
                'infection.version' => '1.2.3',
            ],
        );

        $this->metrics->recordSpanEnded(
            self::createSpan('infection.mutation_generation'),
            self::SPAN_END_NANOS,
            [
                'infection.source_file.count' => 8,
                'infection.mutated_file.count' => 5,
                'infection.mutation.generated.count' => 13,
            ],
        );
        $this->meterProvider->forceFlush();

        $this->exporter->assertSameHistogramValue(
            $metricName,
            $value,
            ['infection.project.name' => 'acme/project'],
        );
    }

    public static function phaseSummaryMetricProvider(): iterable
    {
        yield 'source files' => ['infection.source_file.count', 8];

        yield 'mutated files' => ['infection.mutated_file.count', 5];

        yield 'generated mutations' => ['infection.mutation.generated.count', 13];
    }

    /**
     * @param non-empty-string $name
     * @param Attributes $attributes
     */
    private static function createSpan(
        string $name,
        array $attributes = [],
    ): SpanHandle {
        return new SpanHandle(
            Span::getInvalid(),
            $name,
            self::SPAN_START_NANOS,
            $attributes,
        );
    }
}
