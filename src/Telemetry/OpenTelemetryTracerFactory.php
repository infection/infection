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

namespace Infection\Telemetry;

use const FILTER_NULL_ON_FAILURE;
use const FILTER_VALIDATE_BOOL;
use function filter_var;
use function getenv;
use function implode;
use function in_array;
use Infection\Telemetry\SDK\FailingTracerProviderFactory;
use InvalidArgumentException;
use OpenTelemetry\SDK\Common\Configuration\Variables;
use RuntimeException;
use function Safe\putenv;
use function sprintf;
use function strtolower;

/**
 * @internal
 */
final readonly class OpenTelemetryTracerFactory
{
    private const INFECTION_TELEMETRY = 'INFECTION_TELEMETRY_ENABLED';

    /**
     * See https://github.com/open-telemetry/opentelemetry-specification/blob/v1.7.0/specification/trace/api.md#get-a-tracer
     */
    private const string TRACER_NAME = 'infection';

    /**
     * @throws RuntimeException
     */
    public function create(): ?OpenTelemetryTracer
    {
        self::guardSupportedExporters();

        if (
            self::isSdkDisabled()
            || (!self::isInfectionTelemetryEnabled() && !self::isRequested())
        ) {
            return null;
        }

        self::setDefaultTracesExporter();

        // Note that in theory we could create the TracerProvider directly,
        // not needing to set the service name via an environment variable.
        // However, it's a lot of boilerplate, so not worth it, at least at
        // the time of writing.
        self::setDefaultServiceName();
        $tracerProvider = (new FailingTracerProviderFactory())->create();

        return new OpenTelemetryTracer(
            $tracerProvider->getTracer(self::TRACER_NAME),
            $tracerProvider,
        );
    }

    public function createRequired(): OpenTelemetryTracer
    {
        $tracer = $this->create();

        if ($tracer === null) {
            throw new InvalidArgumentException(sprintf(
                'OpenTelemetry tracer is not enabled. Set %s=true or %s=console to create it.',
                self::INFECTION_TELEMETRY,
                Variables::OTEL_TRACES_EXPORTER,
            ));
        }

        return $tracer;
    }

    private function isInfectionTelemetryEnabled(): bool
    {
        return self::isBoolVariableEnabled(self::INFECTION_TELEMETRY);
    }

    private function isSdkDisabled(): bool
    {
        return self::isBoolVariableEnabled(Variables::OTEL_SDK_DISABLED);
    }

    private function isRequested(): bool
    {
        $tracesExporter = getenv(Variables::OTEL_TRACES_EXPORTER);

        if ($tracesExporter !== false) {
            return strtolower($tracesExporter) !== 'none';
        }

        return getenv(Variables::OTEL_EXPORTER_OTLP_ENDPOINT) !== false
            || getenv(Variables::OTEL_EXPORTER_OTLP_TRACES_ENDPOINT) !== false;
    }

    private static function setDefaultServiceName(): void
    {
        if (getenv(Variables::OTEL_SERVICE_NAME) !== false) {
            return;
        }

        putenv(Variables::OTEL_SERVICE_NAME . '=infection');
        $_SERVER[Variables::OTEL_SERVICE_NAME] = 'infection';
        $_ENV[Variables::OTEL_SERVICE_NAME] = 'infection';
    }

    private static function setDefaultTracesExporter(): void
    {
        if (getenv(Variables::OTEL_TRACES_EXPORTER) !== false) {
            return;
        }

        putenv(Variables::OTEL_TRACES_EXPORTER . '=console');
        $_SERVER[Variables::OTEL_TRACES_EXPORTER] = 'console';
        $_ENV[Variables::OTEL_TRACES_EXPORTER] = 'console';
    }

    private static function guardSupportedExporters(): void
    {
        self::guardExporter(Variables::OTEL_TRACES_EXPORTER, ['otlp', 'console', 'none']);
        self::guardExporter(Variables::OTEL_METRICS_EXPORTER, ['none']);
        self::guardExporter(Variables::OTEL_LOGS_EXPORTER, ['none']);
        self::guardAutoload();
    }

    /**
     * @param list<non-empty-string> $allowedValues
     */
    private static function guardExporter(string $name, array $allowedValues): void
    {
        $value = getenv($name);

        if ($value === false) {
            return;
        }

        $normalizedValue = strtolower($value);

        if (in_array($normalizedValue, $allowedValues, true)) {
            return;
        }

        throw new InvalidArgumentException(
            sprintf(
                'Unsupported OpenTelemetry exporter configured via %s="%s". Supported values: %s.',
                $name,
                $value,
                implode(', ', $allowedValues),
            ),
        );
    }

    /**
     * @see https://opentelemetry.io/docs/languages/php/sdk/#autoloading
     */
    private static function guardAutoload(): void
    {
        $value = self::getEnabledBoolVariable(Variables::OTEL_PHP_AUTOLOAD_ENABLED);

        if ($value === null) {
            return;
        }

        throw new InvalidArgumentException(
            sprintf(
                'Unsupported OpenTelemetry autoload configured via %s="%s". Supported values: false.',
                Variables::OTEL_PHP_AUTOLOAD_ENABLED,
                $value,
            ),
        );
    }

    private static function isBoolVariableEnabled(string $name): bool
    {
        return self::getEnabledBoolVariable($name) !== null;
    }

    private static function getEnabledBoolVariable(string $name): ?string
    {
        $value = getenv($name);

        return $value !== false
            && filter_var($value, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) === true
                ? $value
                : null;
    }
}
