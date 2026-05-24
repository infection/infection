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

use function array_filter;
use function array_keys;
use Exception;
use function getenv;
use Infection\Telemetry\OpenTelemetryTracer;
use Infection\Telemetry\OpenTelemetryTracerFactory;
use Infection\Telemetry\SDK\FailingMeterProviderFactory;
use Infection\Telemetry\SDK\FailingTracerProviderFactory;
use Infection\Tests\EnvVariableManipulation\BacksUpEnvironmentVariables;
use InvalidArgumentException;
use OpenTelemetry\SDK\Common\Configuration\Variables;
use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarFilter\AllExemplarFilter;
use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarFilter\NoneExemplarFilter;
use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarFilter\WithSampledTraceExemplarFilter;
use OpenTelemetry\SDK\Metrics\NoopMeterProvider;
use PHPUnit\Framework\Attributes\BackupGlobals;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use RuntimeException;
use function Safe\putenv;
use function str_starts_with;
use UnexpectedValueException;

#[BackupGlobals(true)]
#[Group('integration')]
#[CoversClass(OpenTelemetryTracerFactory::class)]
#[CoversClass(FailingMeterProviderFactory::class)]
#[CoversClass(FailingTracerProviderFactory::class)]
final class OpenTelemetryTracerFactoryTest extends TestCase
{
    use BacksUpEnvironmentVariables;

    protected function setUp(): void
    {
        $this->backupEnvironmentVariables();
        self::clearTelemetryEnvironmentVariables();
    }

    protected function tearDown(): void
    {
        $this->restoreEnvironmentVariables();
    }

    /**
     * @param array<string, string> $environmentVariables
     */
    #[DataProvider('tracerProvider')]
    public function test_it_creates_a_tracer(
        array $environmentVariables,
        bool|Exception $expected,
    ): void {
        self::setEnvVariables($environmentVariables);
        $factory = new OpenTelemetryTracerFactory();

        if ($expected instanceof Exception) {
            $this->expectExceptionObject($expected);
        }

        $tracer = $factory->create();
        $tracer?->shutdown();

        if ($expected instanceof Exception) {
            return;
        }

        if ($expected) {
            $this->assertInstanceOf(OpenTelemetryTracer::class, $tracer);
        } else {
            $this->assertNull($tracer);
        }
    }

    public static function tracerProvider(): iterable
    {
        $expectTracer = true;
        $expectNoTracer = false;

        yield 'traces exporter not configured' => [
            [],
            $expectNoTracer,
        ];

        yield 'traces exporter requested without Infection telemetry' => [
            [
                Variables::OTEL_TRACES_EXPORTER => 'console',
            ],
            $expectNoTracer,
        ];

        yield 'OTLP endpoint requested without Infection telemetry' => [
            [
                Variables::OTEL_EXPORTER_OTLP_ENDPOINT => 'http://localhost:4318',
            ],
            $expectNoTracer,
        ];

        yield 'unsupported traces exporter without Infection telemetry' => [
            [
                Variables::OTEL_TRACES_EXPORTER => 'http',
            ],
            $expectNoTracer,
        ];

        yield 'traces exporter disabled with Infection telemetry' => [
            [
                Variables::OTEL_TRACES_EXPORTER => 'none',
                OpenTelemetryTracerFactory::INFECTION_TELEMETRY => 'true',
            ],
            $expectNoTracer,
        ];

        yield 'traces exporter disabled and metrics exporter enabled with Infection telemetry' => [
            [
                Variables::OTEL_TRACES_EXPORTER => 'none',
                Variables::OTEL_METRICS_EXPORTER => 'console',
                OpenTelemetryTracerFactory::INFECTION_TELEMETRY => 'true',
            ],
            $expectTracer,
        ];

        yield 'traces exporter disabled case-insensitively with Infection telemetry' => [
            [
                Variables::OTEL_TRACES_EXPORTER => 'NONE',
                OpenTelemetryTracerFactory::INFECTION_TELEMETRY => 'true',
            ],
            $expectNoTracer,
        ];

        yield 'console traces exporter with Infection telemetry' => [
            [
                Variables::OTEL_TRACES_EXPORTER => 'console',
                OpenTelemetryTracerFactory::INFECTION_TELEMETRY => 'true',
            ],
            $expectTracer,
        ];

        yield 'OTLP traces exporter with Infection telemetry' => [
            [
                Variables::OTEL_TRACES_EXPORTER => 'otlp',
                OpenTelemetryTracerFactory::INFECTION_TELEMETRY => 'true',
            ],
            $expectTracer,
        ];

        yield 'Infection telemetry enabled' => [
            [
                OpenTelemetryTracerFactory::INFECTION_TELEMETRY => 'true',
            ],
            $expectTracer,
        ];

        yield 'Infection telemetry explicitly not enabled' => [
            [
                OpenTelemetryTracerFactory::INFECTION_TELEMETRY => 'false',
            ],
            $expectNoTracer,
        ];

        yield 'OTLP exporter endpoint with Infection telemetry' => [
            [
                Variables::OTEL_EXPORTER_OTLP_ENDPOINT => 'http://localhost:4318',
                OpenTelemetryTracerFactory::INFECTION_TELEMETRY => 'true',
            ],
            $expectTracer,
        ];

        yield 'OTLP traces exporter endpoint with Infection telemetry' => [
            [
                Variables::OTEL_EXPORTER_OTLP_TRACES_ENDPOINT => 'http://localhost:4318/v1/traces',
                OpenTelemetryTracerFactory::INFECTION_TELEMETRY => 'true',
            ],
            $expectTracer,
        ];

        yield 'OTLP exporter endpoint with unsupported OTLP protocol and Infection telemetry' => [
            [
                Variables::OTEL_TRACES_EXPORTER => 'otlp',
                Variables::OTEL_EXPORTER_OTLP_ENDPOINT => 'http://localhost:4318',
                Variables::OTEL_EXPORTER_OTLP_PROTOCOL => 'foo',
                OpenTelemetryTracerFactory::INFECTION_TELEMETRY => 'true',
            ],
            new UnexpectedValueException('Unknown protocol: foo'),
        ];

        yield 'OTLP traces exporter endpoint with unsupported OTLP traces protocol and Infection telemetry' => [
            [
                Variables::OTEL_TRACES_EXPORTER => 'otlp',
                Variables::OTEL_EXPORTER_OTLP_TRACES_ENDPOINT => 'http://localhost:4318/v1/traces',
                Variables::OTEL_EXPORTER_OTLP_TRACES_PROTOCOL => 'foo',
                OpenTelemetryTracerFactory::INFECTION_TELEMETRY => 'true',
            ],
            new UnexpectedValueException('Unknown protocol: foo'),
        ];

        yield 'console traces exporter with Infection telemetry enabled' => [
            [
                Variables::OTEL_TRACES_EXPORTER => 'console',
                Variables::OTEL_PHP_AUTOLOAD_ENABLED => 'false',
                OpenTelemetryTracerFactory::INFECTION_TELEMETRY => 'true',
            ],
            $expectTracer,
        ];

        yield 'unsupported traces exporter with Infection telemetry' => [
            [
                Variables::OTEL_TRACES_EXPORTER => 'http',
                OpenTelemetryTracerFactory::INFECTION_TELEMETRY => 'true',
            ],
            new InvalidArgumentException(
                'Unsupported OpenTelemetry exporter configured via OTEL_TRACES_EXPORTER="http". Supported values: otlp, console, none.',
            ),
        ];

        yield 'console metrics exporter with Infection telemetry' => [
            [
                Variables::OTEL_METRICS_EXPORTER => 'console',
                OpenTelemetryTracerFactory::INFECTION_TELEMETRY => 'true',
            ],
            $expectTracer,
        ];

        yield 'OTLP metrics exporter with Infection telemetry' => [
            [
                Variables::OTEL_METRICS_EXPORTER => 'otlp',
                Variables::OTEL_EXPORTER_OTLP_METRICS_PROTOCOL => 'http/protobuf',
                OpenTelemetryTracerFactory::INFECTION_TELEMETRY => 'true',
            ],
            $expectTracer,
        ];

        yield 'unsupported metrics exporter with Infection telemetry' => [
            [
                Variables::OTEL_METRICS_EXPORTER => 'http',
                OpenTelemetryTracerFactory::INFECTION_TELEMETRY => 'true',
            ],
            new InvalidArgumentException(
                'Unsupported OpenTelemetry exporter configured via OTEL_METRICS_EXPORTER="http". Supported values: otlp, console, none.',
            ),
        ];

        yield 'unsupported logs exporter with Infection telemetry' => [
            [
                Variables::OTEL_LOGS_EXPORTER => 'console',
                OpenTelemetryTracerFactory::INFECTION_TELEMETRY => 'true',
            ],
            new InvalidArgumentException(
                'Unsupported OpenTelemetry exporter configured via OTEL_LOGS_EXPORTER="console". Supported values: none.',
            ),
        ];

        yield 'OpenTelemetry PHP autoload enabled with Infection telemetry' => [
            [
                Variables::OTEL_PHP_AUTOLOAD_ENABLED => 'true',
                OpenTelemetryTracerFactory::INFECTION_TELEMETRY => 'true',
            ],
            new InvalidArgumentException(
                'Unsupported OpenTelemetry autoload configured via OTEL_PHP_AUTOLOAD_ENABLED="true". Supported values: false.',
            ),
        ];

        yield 'SDK disabled' => [
            [
                Variables::OTEL_TRACES_EXPORTER => 'console',
                Variables::OTEL_SDK_DISABLED => 'true',
                OpenTelemetryTracerFactory::INFECTION_TELEMETRY => 'true',
            ],
            $expectNoTracer,
        ];
    }

    public function test_it_rejects_grpc_traces_protocol_when_the_grpc_extension_is_not_loaded(): void
    {
        self::setEnvVariables([
            Variables::OTEL_TRACES_EXPORTER => 'otlp',
            Variables::OTEL_EXPORTER_OTLP_TRACES_PROTOCOL => 'grpc',
            OpenTelemetryTracerFactory::INFECTION_TELEMETRY => 'true',
        ]);

        $this->expectExceptionObject(
            new InvalidArgumentException(
                'Unsupported OpenTelemetry OTLP gRPC protocol configured via OTEL_EXPORTER_OTLP_TRACES_PROTOCOL="grpc" because the grpc PHP extension is not loaded.',
            ),
        );

        (new OpenTelemetryTracerFactory(false))->create();
    }

    public function test_it_rejects_grpc_metrics_protocol_when_the_grpc_extension_is_not_loaded(): void
    {
        self::setEnvVariables([
            Variables::OTEL_TRACES_EXPORTER => 'none',
            Variables::OTEL_METRICS_EXPORTER => 'otlp',
            Variables::OTEL_EXPORTER_OTLP_METRICS_PROTOCOL => 'grpc',
            OpenTelemetryTracerFactory::INFECTION_TELEMETRY => 'true',
        ]);

        $this->expectExceptionObject(
            new InvalidArgumentException(
                'Unsupported OpenTelemetry OTLP gRPC protocol configured via OTEL_EXPORTER_OTLP_METRICS_PROTOCOL="grpc" because the grpc PHP extension is not loaded.',
            ),
        );

        (new OpenTelemetryTracerFactory(false))->create();
    }

    public function test_it_rejects_global_grpc_protocol_when_the_grpc_extension_is_not_loaded(): void
    {
        self::setEnvVariables([
            Variables::OTEL_TRACES_EXPORTER => 'otlp',
            Variables::OTEL_EXPORTER_OTLP_PROTOCOL => 'grpc',
            OpenTelemetryTracerFactory::INFECTION_TELEMETRY => 'true',
        ]);

        $this->expectExceptionObject(
            new InvalidArgumentException(
                'Unsupported OpenTelemetry OTLP gRPC protocol configured via OTEL_EXPORTER_OTLP_PROTOCOL="grpc" because the grpc PHP extension is not loaded.',
            ),
        );

        (new OpenTelemetryTracerFactory(false))->create();
    }

    public function test_it_allows_grpc_protocol_configuration_when_the_otlp_exporter_is_not_used(): void
    {
        self::setEnvVariables([
            Variables::OTEL_TRACES_EXPORTER => 'console',
            Variables::OTEL_EXPORTER_OTLP_PROTOCOL => 'grpc',
            OpenTelemetryTracerFactory::INFECTION_TELEMETRY => 'true',
        ]);

        $tracer = (new OpenTelemetryTracerFactory(false))->create();

        $tracer?->shutdown();

        $this->assertInstanceOf(OpenTelemetryTracer::class, $tracer);
    }

    public function test_it_sets_the_default_service_name_when_creating_a_tracer(): void
    {
        self::setEnvVariables([
            Variables::OTEL_TRACES_EXPORTER => 'console',
            OpenTelemetryTracerFactory::INFECTION_TELEMETRY => 'true',
        ]);

        $tracer = (new OpenTelemetryTracerFactory())->create();

        $tracer?->shutdown();

        $this->assertSame('infection', getenv(Variables::OTEL_SERVICE_NAME));
        $this->assertSame('infection', $_SERVER[Variables::OTEL_SERVICE_NAME]);
        $this->assertSame('infection', $_ENV[Variables::OTEL_SERVICE_NAME]);
    }

    public function test_it_sets_the_default_traces_exporter_when_creating_a_tracer(): void
    {
        self::setEnvVariables([
            OpenTelemetryTracerFactory::INFECTION_TELEMETRY => 'true',
        ]);

        $tracer = (new OpenTelemetryTracerFactory())->create();

        $tracer?->shutdown();

        $this->assertSame('console', getenv(Variables::OTEL_TRACES_EXPORTER));
        $this->assertSame('console', $_SERVER[Variables::OTEL_TRACES_EXPORTER]);
        $this->assertSame('console', $_ENV[Variables::OTEL_TRACES_EXPORTER]);
    }

    public function test_it_keeps_the_existing_service_name_when_creating_a_tracer(): void
    {
        self::setEnvVariables([
            Variables::OTEL_TRACES_EXPORTER => 'console',
            Variables::OTEL_SERVICE_NAME => 'custom-service',
            OpenTelemetryTracerFactory::INFECTION_TELEMETRY => 'true',
        ]);

        $tracer = (new OpenTelemetryTracerFactory())->create();

        $tracer?->shutdown();

        $this->assertSame('custom-service', getenv(Variables::OTEL_SERVICE_NAME));
        $this->assertSame('custom-service', $_SERVER[Variables::OTEL_SERVICE_NAME]);
        $this->assertSame('custom-service', $_ENV[Variables::OTEL_SERVICE_NAME]);
    }

    public function test_the_meter_provider_factory_uses_a_noop_provider_when_the_sdk_is_disabled(): void
    {
        self::setEnvVariables([
            Variables::OTEL_SDK_DISABLED => 'true',
            Variables::OTEL_METRICS_EXPORTER => 'console',
        ]);

        $meterProvider = (new FailingMeterProviderFactory())->create();

        $this->assertInstanceOf(NoopMeterProvider::class, $meterProvider);
    }

    /**
     * @param array<string, string> $environmentVariables
     */
    #[DataProvider('invalidMetricsExporterProvider')]
    public function test_the_meter_provider_factory_rejects_invalid_metrics_exporter_configuration(
        array $environmentVariables,
    ): void {
        self::setEnvVariables($environmentVariables);

        $this->expectExceptionObject(
            new RuntimeException('The configured OpenTelemetry metrics exporter name must be a non-empty string.'),
        );

        (new FailingMeterProviderFactory())->create();
    }

    public static function invalidMetricsExporterProvider(): iterable
    {
        yield 'several exporters' => [
            [
                Variables::OTEL_METRICS_EXPORTER => 'console,otlp',
            ],
        ];
    }

    /**
     * @param class-string $expectedClass
     */
    #[DataProvider('exemplarFilterProvider')]
    public function test_the_meter_provider_factory_creates_configured_exemplar_filters(
        string $name,
        string $expectedClass,
    ): void {
        $method = new ReflectionMethod(
            FailingMeterProviderFactory::class,
            'createExemplarFilter',
        );

        $this->assertInstanceOf(
            $expectedClass,
            $method->invoke(
                new FailingMeterProviderFactory(),
                $name,
            ),
        );
    }

    public static function exemplarFilterProvider(): iterable
    {
        yield 'sampled trace' => ['with_sampled_trace', WithSampledTraceExemplarFilter::class];

        yield 'all' => ['all', AllExemplarFilter::class];

        yield 'none' => ['none', NoneExemplarFilter::class];
    }

    public function test_the_meter_provider_factory_rejects_unknown_exemplar_filters(): void
    {
        $method = new ReflectionMethod(FailingMeterProviderFactory::class, 'createExemplarFilter');

        $this->expectExceptionObject(
            new RuntimeException('Unknown exemplar filter: unknown'),
        );

        $method->invoke(new FailingMeterProviderFactory(), 'unknown');
    }

    /**
     * @param array<string, string> $environmentVariables
     */
    private static function setEnvVariables(array $environmentVariables): void
    {
        foreach ($environmentVariables as $name => $value) {
            putenv($name . '=' . $value);
            $_SERVER[$name] = $value;
            $_ENV[$name] = $value;
        }
    }

    private static function clearTelemetryEnvironmentVariables(): void
    {
        foreach (self::getTelemetryEnvironmentVariableNames() as $name) {
            putenv($name);
            unset($_SERVER[$name], $_ENV[$name]);
        }
    }

    /**
     * @return string[]
     */
    private static function getTelemetryEnvironmentVariableNames(): array
    {
        $names = array_filter(
            array_keys(getenv()),
            static fn (string $name): bool => str_starts_with($name, 'OTEL_'),
        );
        $names[] = OpenTelemetryTracerFactory::INFECTION_TELEMETRY;

        return $names;
    }
}
