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

use function array_values;
use function count;
use Infection\Telemetry\Attribute\RunSpanAttributesProvider;
use OpenTelemetry\SDK\Metrics\AggregationTemporalitySelectorInterface;
use OpenTelemetry\SDK\Metrics\Data\Histogram;
use OpenTelemetry\SDK\Metrics\Data\HistogramDataPoint;
use OpenTelemetry\SDK\Metrics\Data\Metric;
use OpenTelemetry\SDK\Metrics\Data\NumberDataPoint;
use OpenTelemetry\SDK\Metrics\Data\Sum;
use OpenTelemetry\SDK\Metrics\Data\Temporality;
use OpenTelemetry\SDK\Metrics\MetricExporter\InMemoryExporter;
use OpenTelemetry\SDK\Metrics\MetricExporterInterface;
use OpenTelemetry\SDK\Metrics\MetricMetadataInterface;
use OpenTelemetry\SDK\Metrics\PushMetricExporterInterface;
use PHPUnit\Framework\Assert;
use function sprintf;

/**
 * @phpstan-import-type Attributes from RunSpanAttributesProvider
 *
 * @internal
 */
final readonly class TestExporter implements AggregationTemporalitySelectorInterface, MetricExporterInterface, PushMetricExporterInterface
{
    private InMemoryExporter $exporter;

    public function __construct()
    {
        $this->exporter = new InMemoryExporter();
    }

    public function temporality(MetricMetadataInterface $metric): string|Temporality|null
    {
        return $this->exporter->temporality($metric);
    }

    public function export(iterable $batch): bool
    {
        return $this->exporter->export($batch);
    }

    public function shutdown(): bool
    {
        return $this->exporter->shutdown();
    }

    public function forceFlush(): bool
    {
        return $this->exporter->forceFlush();
    }

    /**
     * @return list<Metric>
     */
    public function collect(bool $reset = false): array
    {
        return array_values($this->exporter->collect($reset));
    }

    /**
     * @param Attributes $expectedAttributes
     */
    public function assertSameHistogramValue(
        string $name,
        float|int $expectedValue,
        array $expectedAttributes = [],
    ): void {
        $dataPoint = $this->getDataPoint($name, $expectedAttributes);

        Assert::assertInstanceOf(HistogramDataPoint::class, $dataPoint);
        Assert::assertSame($expectedValue, $dataPoint->sum);
    }

    /**
     * @param Attributes $expectedAttributes
     */
    public function assertSameCounterValue(
        string $name,
        float|int $expectedValue,
        array $expectedAttributes = [],
    ): void {
        $dataPoint = $this->getDataPoint($name, $expectedAttributes);

        Assert::assertInstanceOf(NumberDataPoint::class, $dataPoint);
        Assert::assertSame($expectedValue, $dataPoint->value);
    }

    /**
     * @param non-empty-string $attribute
     */
    public function assertNoDataPointHasAttribute(string $attribute): void
    {
        foreach ($this->collect() as $metric) {
            foreach (self::getDataPoints($metric) as $dataPoint) {
                Assert::assertArrayNotHasKey(
                    $attribute,
                    $dataPoint->attributes->toArray(),
                );
            }
        }
    }

    /**
     * Finds the unique exported data point for the given metric name and expected
     * attribute subset, failing when none or several match.
     *
     * @param Attributes $expectedAttributes
     */
    private function getDataPoint(
        string $name,
        array $expectedAttributes,
    ): HistogramDataPoint|NumberDataPoint {
        $matchingDataPoints = $this->findDataPoints($name, $expectedAttributes);

        Assert::assertCount(
            1,
            $matchingDataPoints,
            sprintf(
                'Expected exactly one metric data point named "%s" with matching attributes, got %d.',
                $name,
                count($matchingDataPoints),
            ),
        );

        return $matchingDataPoints[0];
    }

    /**
     * Finds the exported data point for the given metric name and expected attribute subset.
     *
     * @param Attributes $expectedAttributes
     *
     * @return list<HistogramDataPoint|NumberDataPoint>
     */
    private function findDataPoints(
        string $name,
        array $expectedAttributes,
    ): array {
        $matchingDataPoints = [];

        foreach ($this->collect() as $metric) {
            if ($metric->name !== $name) {
                continue;
            }

            foreach (self::getDataPoints($metric) as $dataPoint) {
                if (!self::hasAttributes($dataPoint, $expectedAttributes)) {
                    continue;
                }

                $matchingDataPoints[] = $dataPoint;
            }
        }

        return $matchingDataPoints;
    }

    /**
     * Checks whether a data point contains all expected attributes, while
     * allowing extra attributes to be present.
     *
     * @param Attributes $expectedAttributes
     */
    private static function hasAttributes(
        HistogramDataPoint|NumberDataPoint $dataPoint,
        array $expectedAttributes,
    ): bool {
        $attributes = $dataPoint->attributes->toArray();

        foreach ($expectedAttributes as $key => $expectedValue) {
            $value = $attributes[$key] ?? null;

            if ($value !== $expectedValue) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return iterable<HistogramDataPoint|NumberDataPoint>
     */
    private static function getDataPoints(Metric $metric): iterable
    {
        $data = $metric->data;

        if ($data instanceof Histogram) {
            return $data->dataPoints;
        }

        Assert::assertInstanceOf(Sum::class, $data);

        return $data->dataPoints;
    }
}
