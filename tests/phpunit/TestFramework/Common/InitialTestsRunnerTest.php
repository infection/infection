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

namespace Infection\Tests\TestFramework\Common;

use Closure;
use function extension_loaded;
use Infection\AbstractTestFramework\InvalidVersion;
use Infection\Event\Events\ArtefactCollection\InitialTestExecution\InitialTestCaseWasCompleted;
use Infection\Event\Events\ArtefactCollection\InitialTestExecution\InitialTestSuiteWasFinished;
use Infection\Event\Events\ArtefactCollection\InitialTestExecution\InitialTestSuiteWasStarted;
use Infection\Process\SymfonyProcessShellCommandRunner;
use Infection\TestFramework\Common\InitialTestsRunner;
use Infection\TestFramework\Common\Throwable\InitialTestsFailed;
use Infection\TestFramework\Contracts\CompletedProcess;
use Infection\TestFramework\Contracts\ShellCommandRunner;
use Infection\TestFramework\Contracts\TestFramework;
use Infection\TestFramework\Contracts\TestFrameworkLogger;
use Infection\TestFramework\Logger\EventDispatchingTestFrameworkLogger;
use Infection\Tests\Fixtures\Event\EventDispatcherCollector;
use Infection\Tests\TestFramework\Contracts\CompletedProcessBuilder;
use Infection\Tests\TestingUtility\PHPUnit\ExpectsThrowables;
use Infection\Tests\TestingUtility\Process\TestPhpExecutableFinder;
use const PHP_SAPI;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Exception\RuntimeException;
use Symfony\Component\Process\InputStream;
use Symfony\Component\Process\Process;

#[CoversClass(InitialTestsRunner::class)]
final class InitialTestsRunnerTest extends TestCase
{
    use ExpectsThrowables;

    private ShellCommandRunner&MockObject $shellCommandRunner;

    private EventDispatcherCollector $eventDispatcher;

    private InitialTestsRunner $runner;

    private TestFramework&MockObject $testFramework;

    protected function setUp(): void
    {
        $this->shellCommandRunner = $this->createMock(ShellCommandRunner::class);
        $this->eventDispatcher = new EventDispatcherCollector();
        $testFrameworkLogger = new EventDispatchingTestFrameworkLogger($this->eventDispatcher);
        $this->runner = new InitialTestsRunner($this->shellCommandRunner, $testFrameworkLogger);
        $this->testFramework = $this->createMock(TestFramework::class);
    }

    #[DataProvider('initialRunProvider')]
    public function test_it_runs_the_command_and_reports_progress(
        ?string $version,
        string $expectedVersion,
        bool $skipCoverage,
        bool $useDefaultCoverage = false,
    ): void {
        $this->testFramework
            ->expects($this->once())
            ->method('getName')
            ->willReturn('PHPUnit')
        ;
        $versionExpectation = $this->testFramework->expects($this->once())->method('getVersion');

        if ($version === null) {
            $versionExpectation->willThrowException(new InvalidVersion('Invalid version output.'));
        } else {
            $versionExpectation->willReturn($version);
        }

        $command = ['php', '-d', 'memory_limit=-1', 'phpunit', '--configuration=some config.xml'];
        $executedCommand = ['/usr/bin/php', '-d', 'memory_limit=-1', 'phpunit', '--configuration=some config.xml'];
        $commandLine = (new Process($executedCommand))->getCommandLine();
        $expected = CompletedProcessBuilder::withMinimalTestData()
            ->withCommand($executedCommand)
            ->withStdout('pingpong')
            ->withStderr('warning')
            ->build()
        ;

        $expectedEnv = $skipCoverage || extension_loaded('pcov') || PHP_SAPI === 'phpdbg'
            ? []
            : ['XDEBUG_MODE' => 'coverage'];

        $this->shellCommandRunner
            ->expects($this->once())
            ->method('run')
            ->with($command, $this->isInstanceOf(Closure::class), null, $expectedEnv, null, null, null, !$skipCoverage)
            ->willReturnCallback(function (array $_command, Closure $callback) use ($expectedVersion, $expected): CompletedProcess {
                $this->assertEquals(
                    [new InitialTestSuiteWasStarted('PHPUnit', $expectedVersion)],
                    $this->eventDispatcher->getEvents(),
                );

                $callback(Process::OUT, 'ping');
                $callback(Process::ERR, 'warning');
                $callback(Process::OUT, 'pong');

                return $expected;
            })
        ;

        $actual = $useDefaultCoverage
            ? $this->runner->run($this->testFramework, $command)
            : $this->runner->run($this->testFramework, $command, $skipCoverage);

        $this->assertSame($expected, $actual);
        $this->assertEquals(
            [
                new InitialTestSuiteWasStarted('PHPUnit', $expectedVersion),
                new InitialTestCaseWasCompleted('PHPUnit'),
                new InitialTestCaseWasCompleted('PHPUnit'),
                new InitialTestCaseWasCompleted('PHPUnit'),
                new InitialTestSuiteWasFinished('PHPUnit', $commandLine, 'pingpong'),
            ],
            $this->eventDispatcher->getEvents(),
        );
    }

    #[DataProvider('failureProvider')]
    public function test_it_reports_failed_initial_runs(
        string $testFrameworkType,
        ?string $recommendations,
    ): void {
        $command = ['phpunit'];
        $executedCommand = ['/usr/bin/php', '/path with spaces/phpunit'];
        $commandLine = "'/usr/bin/php' '/path with spaces/phpunit'";
        $process = CompletedProcessBuilder::withMinimalTestData()
            ->withCommand($executedCommand)
            ->withExitCode(3)
            ->withStdout('failed tests')
            ->withStderr('diagnostic')
            ->build()
        ;
        $this->testFramework->expects($this->once())->method('getName')->willReturn('Framework');
        $this->shellCommandRunner
            ->expects($this->once())
            ->method('run')
            ->with($command, $this->isInstanceOf(Closure::class), null, [], null, null, null, false)
            ->willReturn($process)
        ;
        $logger = $this->createMock(TestFrameworkLogger::class);
        $logger
            ->expects($this->once())
            ->method('initialRunFinished')
            ->with('Framework', $executedCommand, 'failed tests')
        ;
        $runner = new InitialTestsRunner($this->shellCommandRunner, $logger);
        $getFailureRecommendations = $recommendations === null
            ? null
            : function (string $actualCommandLine) use ($commandLine, $recommendations): string {
                $this->assertSame($commandLine, $actualCommandLine);

                return $recommendations;
            };
        $expectedMessage = <<<MESSAGE
            Project $testFrameworkType must be in a passing state before running Infection.
            $recommendations
            Framework reported an exit code of 3.
            Refer to the Framework's output below:
            STDOUT:
            failed tests
            STDERR:
            diagnostic
            MESSAGE;

        $this->expectException(InitialTestsFailed::class);
        $this->expectExceptionMessage($expectedMessage);

        $runner->run(
            $this->testFramework,
            $command,
            testFrameworkType: $testFrameworkType,
            buildFailureRecommendations: $getFailureRecommendations,
        );
    }

    public function test_it_reports_process_errors_as_initial_test_failures(): void
    {
        $command = ['phpunit'];
        $failure = new RuntimeException('The process could not be started.', 17);
        $this->testFramework->expects($this->once())->method('getName')->willReturn('PHPUnit');
        $this->testFramework->expects($this->once())->method('getVersion')->willReturn('6.5.4');
        $this->shellCommandRunner
            ->expects($this->once())
            ->method('run')
            ->with($command, $this->isInstanceOf(Closure::class), null, [], null, null, null, false)
            ->willThrowException($failure)
        ;

        $exception = $this->expectToThrow(
            fn () => $this->runner->run($this->testFramework, $command),
        );

        $this->assertInstanceOf(InitialTestsFailed::class, $exception);
        $this->assertSame('The process could not be started.', $exception->getMessage());
        $this->assertSame(17, $exception->getCode());
        $this->assertSame($failure, $exception->getPrevious());
        $this->assertEquals(
            [new InitialTestSuiteWasStarted('PHPUnit', '6.5.4')],
            $this->eventDispatcher->getEvents(),
        );
    }

    public function test_it_does_not_request_failure_recommendations_for_successful_runs(): void
    {
        $this->testFramework->expects($this->once())->method('getName')->willReturn('Framework');
        $process = CompletedProcessBuilder::withMinimalTestData()->build();
        $this->shellCommandRunner->expects($this->once())->method('run')->willReturn($process);

        $actual = $this->runner->run(
            $this->testFramework,
            ['phpunit'],
            buildFailureRecommendations: fn (): never => $this->fail('A successful initial run does not need failure recommendations.'),
        );

        $this->assertSame($process, $actual);
    }

    public static function failureProvider(): iterable
    {
        yield 'test failure without recommendations' => ['tests', null];

        yield 'test failure with recommendations' => ['tests', 'Check the test configuration.'];

        yield 'static-analysis failure' => ['static analysis', null];
    }

    #[DataProvider('exitCodeProvider')]
    #[Group('integration')]
    public function test_it_continues_after_stderr_output_and_reports_the_exit_code(int $exitCode): void
    {
        $this->testFramework->expects($this->once())->method('getName')->willReturn('PHPUnit');
        // Wait for the progress notification before continuing after stderr output.
        $input = new InputStream();
        $command = [
            TestPhpExecutableFinder::find(),
            '-r',
            <<<PHP
                fwrite(STDERR, 'diagnostic');
                fgets(STDIN);
                echo 'continued';
                exit($exitCode);
                PHP,
        ];
        $shellCommandRunner = new SymfonyProcessShellCommandRunner();
        $this->shellCommandRunner
            ->expects($this->once())
            ->method('run')
            ->willReturnCallback(
                /**
                 * @param string[] $command
                 * @param array<string, string> $env
                 */
                static fn (array $command, Closure $callback, ?string $cwd, array $env): CompletedProcess => $shellCommandRunner->run(
                    command: $command,
                    callback: $callback,
                    cwd: $cwd,
                    env: $env,
                    input: $input,
                    timeout: 2.0,
                    useOriginalPhpConfiguration: true,
                ),
            )
        ;

        $logger = $this->createMock(TestFrameworkLogger::class);
        $logger
            ->expects($this->atLeastOnce())
            ->method('initialRunProgressed')
            ->with('PHPUnit')
            ->willReturnCallback(static function () use ($input): void {
                if (!$input->isClosed()) {
                    $input->write("continue\n");
                    $input->close();
                }
            })
        ;
        $logger
            ->expects($this->once())
            ->method('initialRunFinished')
            ->with('PHPUnit', $command, 'continued')
        ;
        $runner = new InitialTestsRunner($this->shellCommandRunner, $logger);

        if ($exitCode !== 0) {
            $this->expectException(InitialTestsFailed::class);
            $expectedMessage = <<<MESSAGE
                Project tests must be in a passing state before running Infection.

                PHPUnit reported an exit code of $exitCode.
                Refer to the PHPUnit's output below:
                STDOUT:
                continued
                STDERR:
                diagnostic
                MESSAGE;
            $this->expectExceptionMessage($expectedMessage);
        }

        $actual = $runner->run($this->testFramework, $command, false);

        $this->assertSame($command, $actual->command);
        $this->assertSame($exitCode, $actual->exitCode);
        $this->assertSame('continued', $actual->stdout);
        $this->assertSame('diagnostic', $actual->stderr);
    }

    public static function exitCodeProvider(): iterable
    {
        yield 'successful command with stderr output' => [0];

        yield 'failed command with stderr output' => [1];
    }

    public static function initialRunProvider(): iterable
    {
        yield 'coverage with a known version' => ['6.5.4', '6.5.4', false];

        yield 'coverage with an unknown version' => [null, 'unknown', false];

        yield 'coverage skipped by default' => ['6.5.4', '6.5.4', true, true];

        yield 'coverage skipped' => ['6.5.4', '6.5.4', true];
    }
}
