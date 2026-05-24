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
use OpenTelemetry\SDK\Metrics\MetricReader\ExportingReader;
use PHPUnit\Framework\Attributes\CoversClass;
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

    private MeterProvider $meterProvider;

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
            ],
        );
        $this->meterProvider->forceFlush();

        $this->exporter->assertSameHistogramValue(
            'infection.run.duration',
            self::SPAN_DURATION_SECONDS,
        );
        $this->exporter->assertSameCounterValue(
            'infection.run.count',
            1,
        );
        $this->exporter->assertSameHistogramValue(
            'infection.mutation.evaluation.duration',
            self::SPAN_DURATION_SECONDS,
            ['infection.mutation.status' => 'escaped'],
        );
        $this->exporter->assertSameHistogramValue(
            'infection.mutation.runtime',
            self::MUTATION_RUNTIME_SECONDS,
            ['infection.mutation.msi.category' => 'not_covered'],
        );
        $this->exporter->assertSameCounterValue(
            'infection.mutation.count',
            1,
            ['infection.mutation.status' => 'escaped'],
        );
        $this->exporter->assertSameHistogramValue(
            'infection.mutation.evaluated.count',
            1,
        );
        $this->exporter->assertSameHistogramValue(
            'infection.msi',
            0.0,
        );

        $this->exporter->assertNoDataPointHasAttribute('infection.mutation.id');
        $this->exporter->assertNoDataPointHasAttribute('code.file.path');
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
