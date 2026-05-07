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

use function extension_loaded;
use OpenTelemetry\API\Behavior\LogsMessagesTrait;
use OpenTelemetry\Contrib\Otlp\SpanExporter;
use OpenTelemetry\SDK\Sdk;
use OpenTelemetry\SDK\Trace\ExporterFactory;
use OpenTelemetry\SDK\Trace\NoopTracerProvider;
use OpenTelemetry\SDK\Trace\SamplerFactory;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;
use OpenTelemetry\SDK\Trace\SpanProcessorFactory;
use OpenTelemetry\SDK\Trace\TracerProvider;
use OpenTelemetry\SDK\Trace\TracerProviderFactory;
use OpenTelemetry\SDK\Trace\TracerProviderInterface;
use RuntimeException;

/**
 * Unlike the upstream factory, it lets the configuration errors cause a failure
 * instead of degrading to no-op telemetry.
 *
 * @see TracerProviderFactory
 * @internal
 */
final readonly class FailingTracerProviderFactory
{
    use LogsMessagesTrait;

    public function __construct(
        private ExporterFactory $exporterFactory = new ExporterFactory(),
        private SamplerFactory $samplerFactory = new SamplerFactory(),
        private SpanProcessorFactory $spanProcessorFactory = new SpanProcessorFactory(),
    ) {
    }

    /**
     * @throws RuntimeException
     */
    public function create(): TracerProviderInterface
    {
        if (Sdk::isDisabled()) {
            return new NoopTracerProvider();
        }

        // No exception is caught (unlike TracerProviderFactory) when creating
        // those services.
        $exporter = $this->createExporter();
        $sampler = $this->samplerFactory->create();
        $spanProcessor = $this->spanProcessorFactory->create($exporter);

        return new TracerProvider(
            $spanProcessor,
            $sampler,
        );
    }

    private function createExporter(): ?SpanExporterInterface
    {
        $exporter = $this->exporterFactory->create();

        if (
            $exporter instanceof SpanExporter
            && !extension_loaded('protobuf')
        ) {
            self::logWarning('protobuf is being used as a transport without the extension. See https://opentelemetry.io/docs/languages/php/#optional-php-extensions');
        }

        return $exporter;
    }
}
