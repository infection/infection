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

namespace Infection\Tests\TestFramework\PHPStan\Adapter;

use Infection\AbstractTestFramework\InvalidVersion;
use Infection\Process\MutantProcess;
use Infection\TestFramework\Common\CommandLineBuilder;
use Infection\TestFramework\Common\InitialTestsRunner;
use Infection\TestFramework\Common\Process\SymfonyProcessFactory;
use Infection\TestFramework\Common\Throwable\InitialTestsFailed;
use Infection\TestFramework\Common\VersionParser;
use Infection\TestFramework\Contracts\InitialRunResults;
use Infection\TestFramework\Contracts\Mutant\MutantProcessDetectionStatusResolver\MutantProcessDetectionStatusResolver;
use Infection\TestFramework\Contracts\ShellCommandRunner;
use Infection\TestFramework\Contracts\TestFramework;
use Infection\TestFramework\Contracts\Throwable\RequirementChecksFailed;
use Infection\TestFramework\PHPStan\Adapter\PHPStanAdapter;
use Infection\TestFramework\PHPStan\Logger\PHPStanLogger;
use Infection\TestFramework\PHPStan\Mutant\PHPStanMutantProcessFactory;
use Infection\Tests\Mutant\MutantBuilder;
use Infection\Tests\TestFramework\Contracts\CompletedProcessBuilder;
use Infection\Tests\TestingUtility\PHPUnit\ExpectsThrowables;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use function sprintf;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Exception\RuntimeException as SymfonyProcessRuntimeException;

#[AllowMockObjectsWithoutExpectations]
#[CoversClass(PHPStanAdapter::class)]
final class PHPStanAdapterTest extends TestCase
{
    use ExpectsThrowables;

    private PHPStanAdapter $adapter;

    private PHPStanMutantProcessFactory $mutantProcessFactory;

    private CommandLineBuilder&MockObject $commandLineBuilder;

    private ShellCommandRunner $shellCommandRunner;

    private InitialTestsRunner&MockObject $initialTestsRunner;

    protected function setUp(): void
    {
        $this->commandLineBuilder = $this->createMock(CommandLineBuilder::class);
        $this->shellCommandRunner = $this->createStub(ShellCommandRunner::class);
        $this->initialTestsRunner = $this->createMock(InitialTestsRunner::class);

        $this->mutantProcessFactory = new PHPStanMutantProcessFactory(
            'PHPStan',
            new SymfonyProcessFactory(),
            $this->createStub(Filesystem::class),
            $this->createStub(MutantProcessDetectionStatusResolver::class),
            '/path/to/phpstan-config-path',
            '/path/to/phpstan',
            $this->commandLineBuilder,
            31.0,
            '/tmp',
            [],
        );

        $this->adapter = new PHPStanAdapter(
            'PHPStan',
            $this->mutantProcessFactory,
            '/path/to/phpstan-config-path',
            '/path/to/phpstan',
            $this->commandLineBuilder,
            new VersionParser('PHPStan'),
            [],
            $this->shellCommandRunner,
            $this->initialTestsRunner,
            $this->createStub(PHPStanLogger::class),
            '9.0',
        );
    }

    public function test_it_has_a_name(): void
    {
        $this->assertSame('PHPStan', $this->adapter->getName());
    }

    public function test_it_builds_initial_run_command_line(): void
    {
        $this->commandLineBuilder
            ->expects($this->once())
            ->method('build')
            ->with('/path/to/phpstan', ['-d memory_limit=-1'], ['--configuration=/path/to/phpstan-config-path'])
            ->willReturn(['/usr/bin/php', '/path/to/phpstan', '--configuration=/path/to/phpstan-config-path'])
        ;

        $process = CompletedProcessBuilder::withMinimalTestData()->withStdout('Analysis passed.')->build();
        $result = new InitialRunResults('Analysis passed.', null);
        $this->initialTestsRunner
            ->expects($this->once())
            ->method('run')
            ->with(
                $this->adapter,
                [
                    '/usr/bin/php',
                    '/path/to/phpstan',
                    '--configuration=/path/to/phpstan-config-path',
                ],
                true,
                'static analysis',
            )
            ->willReturn($process)
        ;

        $this->assertEquals($result, $this->adapter->executeInitialRun());
    }

    public function test_it_builds_initial_run_command_line_with_single_option(): void
    {
        $adapter = new PHPStanAdapter(
            'PHPStan',
            $this->mutantProcessFactory,
            '/path/to/phpstan-config-path',
            '/path/to/phpstan',
            $this->commandLineBuilder,
            new VersionParser('PHPStan'),
            ['--memory-limit=1G'],
            $this->shellCommandRunner,
            $this->initialTestsRunner,
            $this->createStub(PHPStanLogger::class),
            '9.0',
        );

        $this->commandLineBuilder
            ->expects($this->once())
            ->method('build')
            ->with('/path/to/phpstan', ['-d memory_limit=-1'], [
                '--configuration=/path/to/phpstan-config-path',
                '--memory-limit=1G',
            ])
            ->willReturn(['/usr/bin/php', '/path/to/phpstan', '--configuration=/path/to/phpstan-config-path', '--memory-limit=1G'])
        ;

        $process = CompletedProcessBuilder::withMinimalTestData()->withStdout('Analysis passed.')->build();
        $result = new InitialRunResults('Analysis passed.', null);
        $this->initialTestsRunner
            ->expects($this->once())
            ->method('run')
            ->with(
                $adapter,
                [
                    '/usr/bin/php',
                    '/path/to/phpstan',
                    '--configuration=/path/to/phpstan-config-path',
                    '--memory-limit=1G',
                ],
                true,
                'static analysis',
            )
            ->willReturn($process)
        ;

        $this->assertEquals($result, $adapter->executeInitialRun());
    }

    public function test_it_builds_initial_run_command_line_with_multiple_options(): void
    {
        $adapter = new PHPStanAdapter(
            'PHPStan',
            $this->mutantProcessFactory,
            '/path/to/phpstan-config-path',
            '/path/to/phpstan',
            $this->commandLineBuilder,
            new VersionParser('PHPStan'),
            ['--memory-limit=-1', '--no-progress'],
            $this->shellCommandRunner,
            $this->initialTestsRunner,
            $this->createStub(PHPStanLogger::class),
            '9.0',
        );

        $this->commandLineBuilder
            ->expects($this->once())
            ->method('build')
            ->with('/path/to/phpstan', ['-d memory_limit=-1'], [
                '--configuration=/path/to/phpstan-config-path',
                '--memory-limit=-1',
                '--no-progress',
            ])
            ->willReturn(['/usr/bin/php', '/path/to/phpstan', '--configuration=/path/to/phpstan-config-path', '--memory-limit=-1', '--no-progress'])
        ;

        $process = CompletedProcessBuilder::withMinimalTestData()->withStdout('Analysis passed.')->build();
        $result = new InitialRunResults('Analysis passed.', null);
        $this->initialTestsRunner
            ->expects($this->once())
            ->method('run')
            ->with(
                $adapter,
                [
                    '/usr/bin/php',
                    '/path/to/phpstan',
                    '--configuration=/path/to/phpstan-config-path',
                    '--memory-limit=-1',
                    '--no-progress',
                ],
                true,
                'static analysis',
            )
            ->willReturn($process)
        ;

        $this->assertEquals($result, $adapter->executeInitialRun());
    }

    public function test_it_builds_initial_run_command_line_with_complex_options(): void
    {
        $adapter = new PHPStanAdapter(
            'PHPStan',
            $this->mutantProcessFactory,
            '/path/to/phpstan-config-path',
            '/path/to/phpstan',
            $this->commandLineBuilder,
            new VersionParser('PHPStan'),
            ['--memory-limit=2G', '--level=max', '--no-progress'],
            $this->shellCommandRunner,
            $this->initialTestsRunner,
            $this->createStub(PHPStanLogger::class),
            '9.0',
        );

        $this->commandLineBuilder
            ->expects($this->once())
            ->method('build')
            ->with('/path/to/phpstan', ['-d memory_limit=-1'], [
                '--configuration=/path/to/phpstan-config-path',
                '--memory-limit=2G',
                '--level=max',
                '--no-progress',
            ])
            ->willReturn(['/usr/bin/php', '/path/to/phpstan', '--configuration=/path/to/phpstan-config-path', '--memory-limit=2G', '--level=max', '--no-progress'])
        ;

        $process = CompletedProcessBuilder::withMinimalTestData()->withStdout('Analysis passed.')->build();
        $result = new InitialRunResults('Analysis passed.', null);
        $this->initialTestsRunner
            ->expects($this->once())
            ->method('run')
            ->with(
                $adapter,
                [
                    '/usr/bin/php',
                    '/path/to/phpstan',
                    '--configuration=/path/to/phpstan-config-path',
                    '--memory-limit=2G',
                    '--level=max',
                    '--no-progress',
                ],
                true,
                'static analysis',
            )
            ->willReturn($process)
        ;

        $this->assertEquals($result, $adapter->executeInitialRun());
    }

    public function test_it_returns_version(): void
    {
        $this->assertSame('9.0', $this->adapter->getVersion());
    }

    public function test_it_retrieves_version_with_the_shell_command_line_executor(): void
    {
        $shellCommandRunner = $this->createMock(ShellCommandRunner::class);

        $this->commandLineBuilder
            ->expects($this->once())
            ->method('build')
            ->with('/path/to/phpstan', ['-d memory_limit=-1'], ['--version'])
            ->willReturn(['/usr/bin/php', '/path/to/phpstan', '--version'])
        ;

        $shellCommandRunner
            ->expects($this->once())
            ->method('mustRun')
            ->with(['/usr/bin/php', '/path/to/phpstan', '--version'])
            ->willReturn('PHPStan 2.1.17')
        ;

        $adapter = new PHPStanAdapter(
            'PHPStan',
            $this->mutantProcessFactory,
            '/path/to/phpstan-config-path',
            '/path/to/phpstan',
            $this->commandLineBuilder,
            new VersionParser('PHPStan'),
            [],
            $shellCommandRunner,
            $this->initialTestsRunner,
            $this->createStub(PHPStanLogger::class),
        );

        $this->assertSame('2.1.17', $adapter->getVersion());
        $this->assertSame('2.1.17', $adapter->getVersion());
    }

    public function test_it_memoizes_an_unknown_version_when_the_version_command_fails(): void
    {
        $exception = new SymfonyProcessRuntimeException('Could not launch the version command.');
        $shellCommandRunner = $this->createMock(ShellCommandRunner::class);
        $shellCommandRunner
            ->expects($this->once())
            ->method('mustRun')
            ->willThrowException($exception)
        ;
        $logger = $this->createMock(PHPStanLogger::class);
        $logger
            ->expects($this->once())
            ->method('logVersionDetectionFailure')
            ->with($exception)
        ;

        $adapter = new PHPStanAdapter(
            'PHPStan',
            $this->mutantProcessFactory,
            '/path/to/config',
            '/path/to/executable',
            $this->commandLineBuilder,
            new VersionParser('PHPStan'),
            [],
            $shellCommandRunner,
            $this->initialTestsRunner,
            $logger,
        );

        $this->assertSame(TestFramework::UNKNOWN_VERSION, $adapter->getVersion());
        $this->assertSame(TestFramework::UNKNOWN_VERSION, $adapter->getVersion());

        $adapter->checkRequirements();
    }

    public function test_it_rejects_unrecognizable_version_output(): void
    {
        $shellCommandRunner = $this->createStub(ShellCommandRunner::class);
        $shellCommandRunner
            ->method('mustRun')
            ->willReturn('unrecognizable output')
        ;
        $logger = $this->createMock(PHPStanLogger::class);
        $logger
            ->expects($this->never())
            ->method('logVersionDetectionFailure')
        ;

        $adapter = new PHPStanAdapter(
            'PHPStan',
            $this->mutantProcessFactory,
            '/path/to/config',
            '/path/to/executable',
            $this->commandLineBuilder,
            new VersionParser('PHPStan'),
            [],
            $shellCommandRunner,
            $this->initialTestsRunner,
            $logger,
        );

        $this->expectException(InvalidVersion::class);
        $this->expectExceptionMessage('Could not recognise the test framework version for PHPStan for the value "unrecognizable output".');

        $adapter->getVersion();
    }

    public function test_it_reports_version_detection_failure_as_a_requirement_failure(): void
    {
        $shellCommandRunner = $this->createStub(ShellCommandRunner::class);
        $shellCommandRunner
            ->method('mustRun')
            ->willReturn('unrecognizable output')
        ;
        $logger = $this->createMock(PHPStanLogger::class);
        $logger
            ->expects($this->never())
            ->method('logVersionDetectionFailure')
        ;

        $adapter = new PHPStanAdapter(
            'PHPStan',
            $this->mutantProcessFactory,
            '/path/to/config',
            '/path/to/executable',
            $this->commandLineBuilder,
            new VersionParser('PHPStan'),
            [],
            $shellCommandRunner,
            $this->initialTestsRunner,
            $logger,
        );

        $exception = $this->expectToThrow(
            static fn () => $adapter->checkRequirements(),
        );

        $this->assertInstanceOf(RequirementChecksFailed::class, $exception);
        $this->assertSame(
            'Could not recognise the test framework version for PHPStan for the value "unrecognizable output".',
            $exception->getMessage(),
        );

        $previous = $exception->getPrevious();

        $this->assertInstanceOf(InvalidVersion::class, $previous);
        $this->assertSame($exception->getMessage(), $previous->getMessage());
    }

    public function test_it_creates_a_mutant_process_without_follow_ups(): void
    {
        $mutant = MutantBuilder::withMinimalTestData()->build();
        $this->commandLineBuilder
            ->expects($this->once())
            ->method('build')
            ->with('/path/to/phpstan', ['-d memory_limit=-1'], $this->isArray())
            ->willReturn(['/path/to/phpstan', 'analyze'])
        ;

        $container = $this->adapter->test($mutant);
        $mutantProcess = $container->getCurrent();
        $this->assertInstanceOf(MutantProcess::class, $mutantProcess);
        $process = $mutantProcess->getProcess();

        $this->assertSame($mutant, $mutantProcess->getMutant());
        $this->assertSame("'/path/to/phpstan' 'analyze'", $process->getCommandLine());
        $this->assertSame(31.0, $process->getTimeout());
        $this->assertFalse($process->isStarted());
    }

    public function test_it_has_no_junit_report(): void
    {
        $this->assertFalse($this->adapter->hasJUnitReport());
    }

    public function test_it_preserves_initial_run_failures(): void
    {
        $process = CompletedProcessBuilder::withMinimalTestData()
            ->withExitCode(3)
            ->build()
        ;
        $failure = InitialTestsFailed::fromCompletedProcess($process, 'PHPStan', 'static analysis', '');
        $this->commandLineBuilder
            ->method('build')
            ->willReturn(['/path/to/phpstan'])
        ;
        $this->initialTestsRunner
            ->expects($this->once())
            ->method('run')
            ->with(
                $this->adapter,
                ['/path/to/phpstan'],
                true,
                'static analysis',
            )
            ->willThrowException($failure)
        ;

        $this->expectExceptionObject($failure);

        $this->adapter->executeInitialRun();
    }

    #[DataProvider('provideValidVersions')]
    public function test_it_accepts_valid_versions(string $version): void
    {
        $this->expectNotToPerformAssertions();

        $adapter = new PHPStanAdapter(
            'PHPStan',
            $this->mutantProcessFactory,
            '/path/to/phpstan-config-path',
            '/path/to/phpstan',
            $this->commandLineBuilder,
            new VersionParser('PHPStan'),
            ['--memory-limit=-1'],
            $this->shellCommandRunner,
            $this->initialTestsRunner,
            $this->createStub(PHPStanLogger::class),
            $version,
        );

        // This should not throw an exception
        $adapter->checkRequirements();
    }

    #[DataProvider('provideInvalidVersions')]
    public function test_it_rejects_invalid_versions(string $version): void
    {
        $adapter = new PHPStanAdapter(
            'PHPStan',
            $this->mutantProcessFactory,
            '/path/to/phpstan-config-path',
            '/path/to/phpstan',
            $this->commandLineBuilder,
            new VersionParser('PHPStan'),
            [],
            $this->shellCommandRunner,
            $this->initialTestsRunner,
            $this->createStub(PHPStanLogger::class),
            $version,
        );

        $this->expectException(RequirementChecksFailed::class);
        $this->expectExceptionMessage(sprintf(
            'Infection requires PHPStan version >=1.12.27 or >=2.1.17, but "%s" is installed.',
            $version,
        ));

        $adapter->checkRequirements();
    }

    public static function provideValidVersions(): iterable
    {
        yield 'major version 3' => ['3.0.0'];

        yield 'major version 2 with valid patch' => ['2.1.17'];

        yield 'major version 2 with higher minor' => ['2.2.0'];

        yield 'major version 1 with valid patch' => ['1.12.27'];

        yield 'major version 1 with valid patch 2' => ['1.12.28'];

        yield 'major version 1 with higher minor' => ['1.13.0'];

        yield 'dev version 1.12.x' => ['1.12.x-dev@asgar3'];

        yield 'dev version 1.13.x' => ['1.13.x-dev@cfa0299'];

        yield 'dev version 2.1.x' => ['2.1.x-dev@cfa0299'];

        yield 'dev version 2.2.x' => ['2.2.x-dev@cfa0299'];

        yield 'PHPStan-src dev' => ['dev-648dbd911cef28707338fe5c25875d50e7875391@648dbd9'];
    }

    public static function provideInvalidVersions(): iterable
    {
        yield 'major version 2 with too low minor' => ['2.0.17'];

        yield 'major version 2 with too low patch' => ['2.1.1'];

        yield 'major version 2 with too low minor and patch' => ['2.0.0'];

        yield 'major version 1 with too low patch' => ['1.12.26'];

        yield 'major version 1 with too low minor and patch' => ['1.11.0'];

        yield 'major version 0' => ['0.12.0'];

        yield 'dev version 1.0.x' => ['1.0.x-dev@cfa0299'];

        yield 'dev version 2.0.x' => ['2.0.x-dev@cfa0299'];
    }
}
