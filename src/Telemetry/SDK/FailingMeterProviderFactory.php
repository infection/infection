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

namespace Infection\Telemetry\SDK;

use function count;
use function extension_loaded;
use function is_string;
use OpenTelemetry\API\Behavior\LogsMessagesTrait;
use OpenTelemetry\Contrib\Otlp\MetricExporter;
use OpenTelemetry\SDK\Common\Configuration\Configuration;
use OpenTelemetry\SDK\Common\Configuration\Variables;
use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarFilter\AllExemplarFilter;
use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarFilter\NoneExemplarFilter;
use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarFilter\WithSampledTraceExemplarFilter;
use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarFilterInterface;
use OpenTelemetry\SDK\Metrics\MeterProvider;
use OpenTelemetry\SDK\Metrics\MeterProviderFactory;
use OpenTelemetry\SDK\Metrics\MeterProviderInterface;
use OpenTelemetry\SDK\Metrics\MetricReader\ExportingReader;
use OpenTelemetry\SDK\Metrics\NoopMeterProvider;
use OpenTelemetry\SDK\Registry;
use OpenTelemetry\SDK\Resource\ResourceInfoFactory;
use OpenTelemetry\SDK\Sdk;
use RuntimeException;

/**
 * Unlike the upstream factory, it lets configuration errors fail the run
 * instead of degrading to no-op metrics.
 *
 * @see MeterProviderFactory
 * @internal
 */
final readonly class FailingMeterProviderFactory
{
    use LogsMessagesTrait;

    /**
     * @throws RuntimeException
     */
    public function create(): MeterProviderInterface
    {
        if (Sdk::isDisabled()) {
            return new NoopMeterProvider();
        }

        $exporters = Configuration::getList(Variables::OTEL_METRICS_EXPORTER);

        if (
            count($exporters) !== 1
            || !is_string($exporters[0])
            || $exporters[0] === ''
        ) {
            throw new RuntimeException('The configured OpenTelemetry metrics exporter name must be a non-empty string.');
        }

        $factory = Registry::metricExporterFactory($exporters[0]);
        $exporter = $factory->create();

        if (
            $exporter instanceof MetricExporter
            && !extension_loaded('protobuf')
        ) {
            self::logWarning('protobuf is being used as a transport without the extension. See https://opentelemetry.io/docs/languages/php/#optional-php-extensions');
        }

        return MeterProvider::builder()
            ->setResource(ResourceInfoFactory::defaultResource())
            ->addReader(new ExportingReader($exporter))
            ->setExemplarFilter($this->createExemplarFilter(Configuration::getEnum(Variables::OTEL_METRICS_EXEMPLAR_FILTER)))
            ->build();
    }

    private function createExemplarFilter(string $name): ExemplarFilterInterface
    {
        return match ($name) {
            'with_sampled_trace' => new WithSampledTraceExemplarFilter(),
            'all' => new AllExemplarFilter(),
            'none' => new NoneExemplarFilter(),
            default => throw new RuntimeException('Unknown exemplar filter: ' . $name),
        };
    }
}
