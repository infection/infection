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

namespace Infection\Tests\Command\Debug;

use Infection\Command\Debug\TelemetryConfigCommand;
use Infection\Console\Application;
use Infection\Container\Container;
use Infection\Telemetry\OpenTelemetryTracer;
use Infection\Tests\EnvVariableManipulation\BacksUpEnvironmentVariables;
use OpenTelemetry\SDK\Common\Configuration\Variables;
use PHPUnit\Framework\Attributes\BackupGlobals;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use function Safe\putenv;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\VarDumper\VarDumper;

#[BackupGlobals(true)]
#[Group('integration')]
#[CoversClass(TelemetryConfigCommand::class)]
final class TelemetryConfigCommandTest extends TestCase
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

    public function test_it_dumps_the_configured_open_telemetry_tracer_service(): void
    {
        $this->setEnv(Variables::OTEL_TRACES_EXPORTER, 'console');

        $dumpedValue = null;
        $dumpedLabel = null;

        $previousHandler = VarDumper::setHandler(
            static function (mixed $value, ?string $label) use (&$dumpedValue, &$dumpedLabel): void {
                $dumpedValue = $value;
                $dumpedLabel = $label;
            },
        );

        $tester = $this->createCommandTester();

        try {
            $tester->execute([]);
        } finally {
            VarDumper::setHandler($previousHandler);
        }

        $tester->assertCommandIsSuccessful();

        $this->assertInstanceOf(OpenTelemetryTracer::class, $dumpedValue);
        $this->assertSame(OpenTelemetryTracer::class, $dumpedLabel);
    }

    private function createCommandTester(): CommandTester
    {
        $application = new Application(Container::create());

        $command = new TelemetryConfigCommand();
        $command->setApplication($application);

        return new CommandTester($command);
    }

    private function setEnv(string $name, string $value): void
    {
        putenv($name . '=' . $value);
        $_SERVER[$name] = $value;
        $_ENV[$name] = $value;
    }
}
