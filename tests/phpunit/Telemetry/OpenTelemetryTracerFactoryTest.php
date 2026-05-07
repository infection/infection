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

use Exception;
use function getenv;
use Infection\Telemetry\OpenTelemetryTracer;
use Infection\Telemetry\OpenTelemetryTracerFactory;
use Infection\Tests\EnvVariableManipulation\BacksUpEnvironmentVariables;
use InvalidArgumentException;
use OpenTelemetry\SDK\Common\Configuration\Variables;
use PHPUnit\Framework\Attributes\BackupGlobals;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use function Safe\putenv;

#[BackupGlobals(true)]
#[Group('integration')]
#[CoversClass(OpenTelemetryTracerFactory::class)]
final class OpenTelemetryTracerFactoryTest extends TestCase
{
    use BacksUpEnvironmentVariables;

    protected function setUp(): void
    {
        $this->backupEnvironmentVariables();
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
        $this->setEnvVariables($environmentVariables);
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

        yield 'traces exporter disabled' => [
            [
                Variables::OTEL_TRACES_EXPORTER => 'none',
            ],
            $expectNoTracer,
        ];

        yield 'traces exporter disabled case-insensitively' => [
            [
                Variables::OTEL_TRACES_EXPORTER => 'NONE',
            ],
            $expectNoTracer,
        ];

        yield 'console traces exporter' => [
            [
                Variables::OTEL_TRACES_EXPORTER => 'console',
            ],
            $expectTracer,
        ];

        yield 'console traces exporter with OpenTelemetry PHP autoload disabled' => [
            [
                Variables::OTEL_TRACES_EXPORTER => 'console',
                Variables::OTEL_PHP_AUTOLOAD_ENABLED => 'false',
            ],
            $expectTracer,
        ];

        yield 'unsupported traces exporter' => [
            [
                Variables::OTEL_TRACES_EXPORTER => 'otlp',
            ],
            new InvalidArgumentException(
                'Unsupported OpenTelemetry exporter configured via OTEL_TRACES_EXPORTER="otlp". Supported values: console, none.',
            ),
        ];

        yield 'unsupported metrics exporter' => [
            [
                Variables::OTEL_METRICS_EXPORTER => 'console',
            ],
            new InvalidArgumentException(
                'Unsupported OpenTelemetry exporter configured via OTEL_METRICS_EXPORTER="console". Supported values: none.',
            ),
        ];

        yield 'unsupported logs exporter' => [
            [
                Variables::OTEL_LOGS_EXPORTER => 'console',
            ],
            new InvalidArgumentException(
                'Unsupported OpenTelemetry exporter configured via OTEL_LOGS_EXPORTER="console". Supported values: none.',
            ),
        ];

        yield 'OpenTelemetry PHP autoload enabled' => [
            [
                Variables::OTEL_PHP_AUTOLOAD_ENABLED => 'true',
            ],
            new InvalidArgumentException(
                'Unsupported OpenTelemetry autoload configured via OTEL_PHP_AUTOLOAD_ENABLED="true". Supported values: false.',
            ),
        ];

        yield 'SDK disabled' => [
            [
                Variables::OTEL_TRACES_EXPORTER => 'console',
                Variables::OTEL_SDK_DISABLED => 'true',
            ],
            $expectNoTracer,
        ];
    }

    public function test_it_sets_the_default_service_name_when_creating_a_tracer(): void
    {
        $this->setEnvVariables([
            Variables::OTEL_TRACES_EXPORTER => 'console',
        ]);

        $tracer = (new OpenTelemetryTracerFactory())->create();

        $tracer?->shutdown();

        $this->assertSame('infection', getenv(Variables::OTEL_SERVICE_NAME));
        $this->assertSame('infection', $_SERVER[Variables::OTEL_SERVICE_NAME]);
        $this->assertSame('infection', $_ENV[Variables::OTEL_SERVICE_NAME]);
    }

    public function test_it_keeps_the_existing_service_name_when_creating_a_tracer(): void
    {
        $this->setEnvVariables([
            Variables::OTEL_TRACES_EXPORTER => 'console',
            Variables::OTEL_SERVICE_NAME => 'custom-service',
        ]);

        $tracer = (new OpenTelemetryTracerFactory())->create();

        $tracer?->shutdown();

        $this->assertSame('custom-service', getenv(Variables::OTEL_SERVICE_NAME));
        $this->assertSame('custom-service', $_SERVER[Variables::OTEL_SERVICE_NAME]);
        $this->assertSame('custom-service', $_ENV[Variables::OTEL_SERVICE_NAME]);
    }

    /**
     * @param array<string, string> $environmentVariables
     */
    private function setEnvVariables(array $environmentVariables): void
    {
        foreach ($environmentVariables as $name => $value) {
            putenv($name . '=' . $value);
            $_SERVER[$name] = $value;
            $_ENV[$name] = $value;
        }
    }
}
