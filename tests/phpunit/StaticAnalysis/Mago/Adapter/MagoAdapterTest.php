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

namespace Infection\Tests\StaticAnalysis\Mago\Adapter;

use Infection\StaticAnalysis\Mago\Adapter\MagoAdapter;
use Infection\StaticAnalysis\Mago\Mutant\MagoMutantExecutionResultFactory;
use Infection\StaticAnalysis\Mago\Process\MagoMutantProcessFactory;
use Infection\TestFramework\CommandLineBuilder;
use Infection\TestFramework\VersionParser;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use function sprintf;

#[Group('integration')]
#[CoversClass(MagoAdapter::class)]
final class MagoAdapterTest extends TestCase
{
    private MagoAdapter $adapter;

    private CommandLineBuilder&MockObject $commandLineBuilder;

    protected function setUp(): void
    {
        $this->commandLineBuilder = $this->createMock(CommandLineBuilder::class);

        $this->adapter = new MagoAdapter(
            $this->createStub(MagoMutantExecutionResultFactory::class),
            '/path/to/mago-config-path',
            '/path/to/mago',
            $this->commandLineBuilder,
            new VersionParser(),
            31.0,
            [],
            '9.0',
        );
    }

    public function test_it_has_a_name(): void
    {
        $this->assertSame('Mago', $this->adapter->getName());
    }

    public function test_it_builds_initial_run_command_line(): void
    {
        $this->commandLineBuilder
            ->expects($this->once())
            ->method('build')
            ->with('/path/to/mago', [], ['--config=/path/to/mago-config-path', 'analyze'])
            ->willReturn(['/path/to/mago', '--config=/path/to/mago-config-path', 'analyze'])
        ;

        $this->assertSame([
            '/path/to/mago',
            '--config=/path/to/mago-config-path',
            'analyze',
        ], $this->adapter->getInitialRunCommandLine());
    }

    public function test_it_builds_initial_run_command_line_with_single_option(): void
    {
        $adapter = new MagoAdapter(
            $this->createStub(MagoMutantExecutionResultFactory::class),
            '/path/to/mago-config-path',
            '/path/to/mago',
            $this->commandLineBuilder,
            new VersionParser(),
            31.0,
            ['--sort'],
            '9.0',
        );

        $this->commandLineBuilder
            ->expects($this->once())
            ->method('build')
            ->with('/path/to/mago', [], [
                '--config=/path/to/mago-config-path',
                'analyze',
                '--sort',
            ])
            ->willReturn(['/path/to/mago', '--config=/path/to/mago-config-path', '--sort'])
        ;

        $this->assertSame([
            '/path/to/mago',
            '--config=/path/to/mago-config-path',
            '--sort',
        ], $adapter->getInitialRunCommandLine());
    }

    public function test_it_builds_initial_run_command_line_with_multiple_options(): void
    {
        $adapter = new MagoAdapter(
            $this->createStub(MagoMutantExecutionResultFactory::class),
            '/path/to/mago-config-path',
            '/path/to/mago',
            $this->commandLineBuilder,
            new VersionParser(),
            31.0,
            ['--no-progress'],
            '9.0',
        );

        $this->commandLineBuilder
            ->expects($this->once())
            ->method('build')
            ->with('/path/to/mago', [], [
                '--config=/path/to/mago-config-path',
                'analyze',
                '--no-progress',
            ])
            ->willReturn(['/path/to/mago', '--config=/path/to/mago-config-path', 'analyze', '--no-progress'])
        ;

        $this->assertSame([
            '/path/to/mago',
            '--config=/path/to/mago-config-path',
            'analyze',
            '--no-progress',
        ], $adapter->getInitialRunCommandLine());
    }

    public function test_it_builds_initial_run_command_line_with_complex_options(): void
    {
        $adapter = new MagoAdapter(
            $this->createStub(MagoMutantExecutionResultFactory::class),
            '/path/to/mago-config-path',
            '/path/to/mago',
            $this->commandLineBuilder,
            new VersionParser(),
            31.0,
            ['--no-stubs', '--baseline /path/to/baseline.toml'],
            '9.0',
        );

        $this->commandLineBuilder
            ->expects($this->once())
            ->method('build')
            ->with('/path/to/mago', [], [
                '--config=/path/to/mago-config-path',
                'analyze',
                '--no-stubs',
                '--baseline /path/to/baseline.toml',
            ])
            ->willReturn(['/path/to/mago', '--config=/path/to/mago-config-path', 'analyze', '--no-stubs', '--baseline /path/to/baseline.toml'])
        ;

        $this->assertSame([
            '/path/to/mago',
            '--config=/path/to/mago-config-path',
            'analyze',
            '--no-stubs',
            '--baseline /path/to/baseline.toml',
        ], $adapter->getInitialRunCommandLine());
    }

    public function test_it_returns_version(): void
    {
        $this->assertSame('9.0', $this->adapter->getVersion());
    }

    public function test_it_creates_mutant_process_creator(): void
    {
        $this->assertInstanceOf(
            MagoMutantProcessFactory::class,
            $this->adapter->createMutantProcessFactory(),
        );
    }

    #[DataProvider('provideValidVersions')]
    public function test_it_accepts_valid_versions(string $version): void
    {
        $this->expectNotToPerformAssertions();

        $adapter = new MagoAdapter(
            $this->createStub(MagoMutantExecutionResultFactory::class),
            '/path/to/mago-config-path',
            '/path/to/mago',
            $this->commandLineBuilder,
            new VersionParser(),
            31.0,
            [],
            $version,
        );

        // This should not throw an exception
        $adapter->assertMinimumVersionSatisfied();
    }

    #[DataProvider('provideInvalidVersions')]
    public function test_it_rejects_invalid_versions(string $version): void
    {
        $adapter = new MagoAdapter(
            $this->createStub(MagoMutantExecutionResultFactory::class),
            '/path/to/mago-config-path',
            '/path/to/mago',
            $this->commandLineBuilder,
            new VersionParser(),
            31.0,
            [],
            $version,
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(sprintf(
            'Infection requires Mago version >=1.23.0, but "%s" is installed.',
            $version,
        ));

        $adapter->assertMinimumVersionSatisfied();
    }

    public static function provideValidVersions(): iterable
    {
        yield 'major version 1 with valid minor' => ['1.23.0'];

        yield 'major version 1 with valid patch' => ['1.23.1'];

        yield 'major version 2' => ['2.0.0'];
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideInvalidVersions(): iterable
    {
        yield 'major version 1 with too low minor' => ['1.19.0'];

        yield 'major version 1 with too low patch' => ['1.12.26'];

        yield 'major version 1 with too low minor and patch' => ['1.11.0'];

        yield 'major version 0' => ['0.12.0'];

        yield 'dev version 1.0.x' => ['1.0.x-dev@cfa0299'];
    }
}
