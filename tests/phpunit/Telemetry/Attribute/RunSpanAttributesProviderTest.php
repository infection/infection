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

namespace Infection\Tests\Telemetry\Attribute;

use Infection\Framework\InfectionVersion;
use Infection\Process\ShellCommandLineExecutor;
use Infection\StaticAnalysis\StaticAnalysisToolTypes;
use Infection\Telemetry\Attribute\RunSpanAttributesProvider;
use Infection\Tests\Configuration\ConfigurationBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Process\Exception\ExceptionInterface as ProcessException;

#[CoversClass(RunSpanAttributesProvider::class)]
final class RunSpanAttributesProviderTest extends TestCase
{
    public function test_it_provides_run_identity_attributes_from_the_configuration(): void
    {
        $configuration = ConfigurationBuilder::withMinimalTestData()
            ->withProjectDirectory('/var/www/project')
            ->withProjectName('acme/package')
            ->withConfigPathname('/var/www/project/config/infection.json5')
            ->withThreadCount(8)
            ->withSkipInitialTests(true)
            ->withStaticAnalysisTool(StaticAnalysisToolTypes::PHPSTAN)
            ->build();

        $infectionVersionMock = $this->createMock(InfectionVersion::class);
        $infectionVersionMock
            ->method('prettyVersion')
            ->willReturn('1.2.3');

        $provider = new RunSpanAttributesProvider(
            $configuration,
            $infectionVersionMock,
            self::successfulShellExecutor('0123456789abcdef'),
        );

        $expected = [
            'infection.project.name' => 'acme/package',
            'infection.project.dir' => '/var/www/project',
            'infection.config.path' => 'config/infection.json5',
            'infection.version' => '1.2.3',
            'infection.distribution' => 'source',
            'infection.thread.count' => 8,
            'infection.initial_tests.skipped' => true,
            'infection.initial_static_analysis.skipped' => false,
            'infection.git.sha' => '0123456789abcdef',
        ];

        $actual = $provider->provide();

        $this->assertSame($expected, $actual);
    }

    public function test_it_omits_the_git_sha_when_the_project_is_not_a_git_checkout(): void
    {
        $infectionVersionMock = $this->createMock(InfectionVersion::class);
        $infectionVersionMock
            ->method('prettyVersion')
            ->willReturn('1.2.3');

        $attributes = (new RunSpanAttributesProvider(
            ConfigurationBuilder::withMinimalTestData()
                ->withProjectDirectory('/var/www/project')
                ->withProjectName('project')
                ->build(),
            $infectionVersionMock,
            self::failingShellExecutor(),
        ))->provide();

        $this->assertSame('project', $attributes['infection.project.name']);
        $this->assertFalse(isset($attributes['infection.git.sha']));
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
