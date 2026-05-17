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

use Infection\FileSystem\InMemoryFileSystem;
use Infection\Process\ShellCommandLineExecutor;
use Infection\StaticAnalysis\StaticAnalysisToolTypes;
use Infection\Telemetry\RunTelemetryAttributesProvider;
use Infection\Tests\Configuration\ConfigurationBuilder;
use Infection\Tests\EnvVariableManipulation\BacksUpEnvironmentVariables;
use PHPUnit\Framework\Attributes\BackupGlobals;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use function Safe\putenv;
use Symfony\Component\Process\Exception\ExceptionInterface as ProcessException;

#[BackupGlobals(true)]
#[CoversClass(RunTelemetryAttributesProvider::class)]
final class RunTelemetryAttributesProviderTest extends TestCase
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

    public function test_it_provides_run_identity_attributes_from_the_configuration(): void
    {
        $fileSystem = new InMemoryFileSystem();
        $fileSystem->dumpFile('/var/www/project/composer.json', '{"name": "acme/package"}');

        $configuration = ConfigurationBuilder::withMinimalTestData()
            ->withProjectDirectory('/var/www/project')
            ->withConfigPathname('/var/www/project/config/infection.json5')
            ->withThreadCount(8)
            ->withSkipInitialTests(true)
            ->withStaticAnalysisTool(StaticAnalysisToolTypes::PHPSTAN)
            ->build();

        $attributes = (new RunTelemetryAttributesProvider(
            $configuration,
            $fileSystem,
            self::successfulShellExecutor('0123456789abcdef'),
        ))->provide();

        $this->assertSame('acme/package', $attributes['infection.project.name']);
        $this->assertSame('/var/www/project', $attributes['infection.project.dir']);
        $this->assertSame('config/infection.json5', $attributes['infection.config.path']);
        $this->assertSame('source', $attributes['infection.distribution']);
        $this->assertSame(8, $attributes['infection.thread.count']);
        $this->assertTrue($attributes['infection.initial_tests.skipped']);
        $this->assertFalse($attributes['infection.initial_static_analysis.skipped']);
        $this->assertSame('0123456789abcdef', $attributes['infection.git.sha']);
        $this->assertIsString($attributes['infection.version']);
        $this->assertNotSame('', $attributes['infection.version']);
    }

    public function test_it_prefers_the_project_name_environment_variable(): void
    {
        putenv(RunTelemetryAttributesProvider::INFECTION_PROJECT_NAME . '=custom-project');

        $attributes = (new RunTelemetryAttributesProvider(
            ConfigurationBuilder::withMinimalTestData()
                ->withProjectDirectory('/var/www/project')
                ->build(),
            new InMemoryFileSystem(),
            self::failingShellExecutor(),
        ))->provide();

        $this->assertSame('custom-project', $attributes['infection.project.name']);
        $this->assertFalse(isset($attributes['infection.git.sha']));
    }

    public function test_it_falls_back_to_the_project_directory_basename(): void
    {
        $attributes = (new RunTelemetryAttributesProvider(
            ConfigurationBuilder::withMinimalTestData()
                ->withProjectDirectory('/var/www/project')
                ->build(),
            new InMemoryFileSystem(),
            self::failingShellExecutor(),
        ))->provide();

        $this->assertSame('project', $attributes['infection.project.name']);
        $this->assertTrue($attributes['infection.initial_static_analysis.skipped']);
    }

    private static function successfulShellExecutor(string $output): ShellCommandLineExecutor
    {
        return new class($output) extends ShellCommandLineExecutor {
            public function __construct(
                private readonly string $output,
            ) {
            }

            public function execute(array $command): string
            {
                return $this->output;
            }
        };
    }

    private static function failingShellExecutor(): ShellCommandLineExecutor
    {
        return new class extends ShellCommandLineExecutor {
            public function execute(array $command): string
            {
                throw new class extends RuntimeException implements ProcessException {
                };
            }
        };
    }
}
