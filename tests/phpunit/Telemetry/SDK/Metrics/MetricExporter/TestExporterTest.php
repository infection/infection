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

namespace Infection\Tests\Telemetry\SDK\Metrics\MetricExporter;

use Infection\Telemetry\Attribute\RunSpanAttributesProvider;
use Infection\Tests\TestingUtility\PHPUnit\ExpectsThrowables;
use OpenTelemetry\SDK\Common\Attribute\Attributes as OTelAttributes;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScope;
use OpenTelemetry\SDK\Metrics\Data\DataInterface;
use OpenTelemetry\SDK\Metrics\Data\Histogram;
use OpenTelemetry\SDK\Metrics\Data\HistogramDataPoint;
use OpenTelemetry\SDK\Metrics\Data\Metric;
use OpenTelemetry\SDK\Metrics\Data\NumberDataPoint;
use OpenTelemetry\SDK\Metrics\Data\Sum;
use OpenTelemetry\SDK\Metrics\Data\Temporality;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @phpstan-import-type Attributes from RunSpanAttributesProvider
 */
#[CoversClass(TestExporter::class)]
final class TestExporterTest extends TestCase
{
    use ExpectsThrowables;

    private const int TIMESTAMP = 1;

    private const float HISTOGRAM_VALUE = 2.0;

    private const int COUNTER_VALUE = 3;

    public function test_it_accepts_matching_histogram_and_counter_values(): void
    {
        $exporter = new TestExporter();

        $exporter->export([
            self::createHistogramMetric(
                'infection.run.duration',
                self::HISTOGRAM_VALUE,
                [
                    'infection.version' => '1.2.3',
                    'infection.thread.count' => 4,
                ],
            ),
            self::createCounterMetric(
                'infection.run.count',
                self::COUNTER_VALUE,
                [
                    'infection.version' => '1.2.3',
                    'infection.thread.count' => 4,
                ],
            ),
        ]);

        $exporter->assertSameHistogramValue(
            'infection.run.duration',
            self::HISTOGRAM_VALUE,
            ['infection.version' => '1.2.3'],
        );
        $exporter->assertSameCounterValue(
            'infection.run.count',
            self::COUNTER_VALUE,
            ['infection.thread.count' => 4],
        );
    }

    public function test_it_accepts_metrics_without_forbidden_attributes(): void
    {
        $exporter = new TestExporter();

        $exporter->export([
            self::createHistogramMetric(
                'infection.run.duration',
                self::HISTOGRAM_VALUE,
                ['infection.version' => '1.2.3'],
            ),
        ]);

        $exporter->assertNoDataPointHasAttribute('infection.mutation.id');
    }

    public function test_it_rejects_missing_matching_data_points(): void
    {
        $exporter = new TestExporter();

        $exporter->export([
            self::createCounterMetric(
                'infection.mutation.count',
                self::COUNTER_VALUE,
                ['infection.mutation.status' => 'killed'],
            ),
        ]);

        // @phpstan-ignore classConstant.internalClass
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Expected exactly one metric data point named "infection.mutation.count" with matching attributes, got 0.');

        $exporter->assertSameCounterValue(
            'infection.mutation.count',
            self::COUNTER_VALUE,
            ['infection.mutation.status' => 'escaped'],
        );
    }

    public function test_it_rejects_ambiguous_matching_data_points(): void
    {
        $exporter = new TestExporter();

        $exporter->export([
            self::createCounterMetric(
                'infection.mutation.count',
                self::COUNTER_VALUE,
                [
                    'infection.mutation.status' => 'escaped',
                    'infection.version' => '1.2.3',
                ],
            ),
            self::createCounterMetric(
                'infection.mutation.count',
                self::COUNTER_VALUE,
                [
                    'infection.mutation.status' => 'escaped',
                    'infection.version' => '1.2.4',
                ],
            ),
        ]);

        // @phpstan-ignore classConstant.internalClass
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Expected exactly one metric data point named "infection.mutation.count" with matching attributes, got 2.');

        $exporter->assertSameCounterValue(
            'infection.mutation.count',
            self::COUNTER_VALUE,
            ['infection.mutation.status' => 'escaped'],
        );
    }

    public function test_it_rejects_data_points_with_forbidden_attributes(): void
    {
        $exporter = new TestExporter();

        $exporter->export([
            self::createHistogramMetric(
                'infection.mutation.runtime',
                self::HISTOGRAM_VALUE,
                ['infection.mutation.id' => 'mutation-hash'],
            ),
        ]);

        // @phpstan-ignore classConstant.internalClass
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Failed asserting that an array does not have the key \'infection.mutation.id\'.');

        $exporter->assertNoDataPointHasAttribute('infection.mutation.id');
    }

    /**
     * @param non-empty-string $name
     * @param Attributes $attributes
     */
    private static function createHistogramMetric(
        string $name,
        float|int $value,
        array $attributes = [],
    ): Metric {
        return self::createMetric(
            $name,
            new Histogram(
                [
                    new HistogramDataPoint(
                        1,
                        $value,
                        $value,
                        $value,
                        [1],
                        [],
                        OTelAttributes::create($attributes),
                        self::TIMESTAMP,
                        self::TIMESTAMP,
                    ),
                ],
                Temporality::CUMULATIVE,
            ),
        );
    }

    /**
     * @param non-empty-string $name
     * @param Attributes $attributes
     */
    private static function createCounterMetric(
        string $name,
        float|int $value,
        array $attributes = [],
    ): Metric {
        return self::createMetric(
            $name,
            new Sum(
                [
                    new NumberDataPoint(
                        $value,
                        OTelAttributes::create($attributes),
                        self::TIMESTAMP,
                        self::TIMESTAMP,
                    ),
                ],
                Temporality::CUMULATIVE,
                true,
            ),
        );
    }

    /**
     * @param non-empty-string $name
     */
    private static function createMetric(string $name, DataInterface $data): Metric
    {
        return new Metric(
            new InstrumentationScope(
                'infection',
                null,
                null,
                OTelAttributes::create([]),
            ),
            ResourceInfo::create(OTelAttributes::create([])),
            $name,
            null,
            null,
            $data,
        );
    }
}
