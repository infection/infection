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

namespace Infection\Tests\Process\Runner;

use Closure;
use function extension_loaded;
use Infection\AbstractTestFramework\InvalidVersion;
use Infection\AbstractTestFramework\TestFrameworkAdapter;
use Infection\Event\Events\ArtefactCollection\InitialTestExecution\InitialTestCaseWasCompleted;
use Infection\Event\Events\ArtefactCollection\InitialTestExecution\InitialTestSuiteWasFinished;
use Infection\Event\Events\ArtefactCollection\InitialTestExecution\InitialTestSuiteWasStarted;
use Infection\Process\Runner\InitialTestsRunner;
use Infection\Process\SymfonyProcessShellCommandRunner;
use Infection\TestFramework\Contracts\CompletedProcess;
use Infection\TestFramework\Contracts\ShellCommandRunner;
use Infection\TestFramework\Contracts\TestFrameworkLogger;
use Infection\TestFramework\Logger\EventDispatchingTestFrameworkLogger;
use Infection\Tests\Fixtures\Event\EventDispatcherCollector;
use Infection\Tests\TestFramework\Contracts\CompletedProcessBuilder;
use Infection\Tests\TestingUtility\Process\TestPhpExecutableFinder;
use const PHP_SAPI;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\InputStream;
use Symfony\Component\Process\Process;

#[CoversClass(InitialTestsRunner::class)]
final class InitialTestsRunnerTest extends TestCase
{
    private ShellCommandRunner&MockObject $shellCommandRunner;

    private EventDispatcherCollector $eventDispatcher;

    private InitialTestsRunner $runner;

    private EventDispatchingTestFrameworkLogger $testFrameworkLogger;

    private TestFrameworkAdapter&MockObject $testFrameworkAdapter;

    protected function setUp(): void
    {
        $this->shellCommandRunner = $this->createMock(ShellCommandRunner::class);
        $this->eventDispatcher = new EventDispatcherCollector();
        $this->testFrameworkLogger = new EventDispatchingTestFrameworkLogger($this->eventDispatcher);
        $this->runner = new InitialTestsRunner($this->shellCommandRunner);
        $this->testFrameworkAdapter = $this->createMock(TestFrameworkAdapter::class);
    }

    #[DataProvider('initialRunProvider')]
    public function test_it_runs_the_command_and_reports_progress(
        ?string $version,
        string $expectedVersion,
        bool $skipCoverage,
        int $exitCode,
    ): void {
        $this->testFrameworkAdapter
            ->expects($this->once())
            ->method('getName')
            ->willReturn('PHPUnit')
        ;
        $versionExpectation = $this->testFrameworkAdapter->expects($this->once())->method('getVersion');

        if ($version === null) {
            $versionExpectation->willThrowException(new InvalidVersion('Invalid version output.'));
        } else {
            $versionExpectation->willReturn($version);
        }

        $testFrameworkExtraOptions = '--stop-on-failure';
        $phpExtraOptions = ['-d', 'memory_limit=-1'];
        $command = ['php', '-d', 'memory_limit=-1', 'phpunit', '--configuration=some config.xml'];
        $commandLine = (new Process($command))->getCommandLine();
        $expected = CompletedProcessBuilder::withMinimalTestData()
            ->withCommand($command)
            ->withExitCode($exitCode)
            ->withStdout('pingpong')
            ->withStderr('warning')
            ->build()
        ;

        $this->testFrameworkAdapter
            ->expects($this->once())
            ->method('getInitialTestRunCommandLine')
            ->with($testFrameworkExtraOptions, $phpExtraOptions, $skipCoverage)
            ->willReturn($command)
        ;

        $expectedEnv = $skipCoverage || extension_loaded('pcov') || PHP_SAPI === 'phpdbg'
            ? []
            : ['XDEBUG_MODE' => 'coverage'];

        $this->shellCommandRunner
            ->expects($this->once())
            ->method('run')
            ->with($command, $this->isInstanceOf(Closure::class), null, $expectedEnv, null, null, null, !$skipCoverage)
            ->willReturnCallback(function (array $_command, Closure $callback) use ($commandLine, $expectedVersion, $expected): CompletedProcess {
                $this->assertEquals(
                    [new InitialTestSuiteWasStarted($commandLine, 'PHPUnit', $expectedVersion)],
                    $this->eventDispatcher->getEvents(),
                );

                $callback(Process::OUT, 'ping');
                $callback(Process::ERR, 'warning');
                $callback(Process::OUT, 'pong');

                return $expected;
            })
        ;

        $actual = $this->runner->run(
            $this->testFrameworkAdapter,
            $testFrameworkExtraOptions,
            $phpExtraOptions,
            $skipCoverage,
            $this->testFrameworkLogger,
        );

        $this->assertSame($expected, $actual);
        $this->assertEquals(
            [
                new InitialTestSuiteWasStarted($commandLine, 'PHPUnit', $expectedVersion),
                new InitialTestCaseWasCompleted('PHPUnit'),
                new InitialTestCaseWasCompleted('PHPUnit'),
                new InitialTestCaseWasCompleted('PHPUnit'),
                new InitialTestSuiteWasFinished('PHPUnit', 'pingpong'),
            ],
            $this->eventDispatcher->getEvents(),
        );
    }

    #[DataProvider('exitCodeProvider')]
    #[Group('integration')]
    public function test_it_continues_after_stderr_output_and_preserves_the_exit_code(int $exitCode): void
    {
        $this->testFrameworkAdapter->method('getName')->willReturn('PHPUnit');
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
        $this->testFrameworkAdapter
            ->expects($this->once())
            ->method('getInitialTestRunCommandLine')
            ->willReturn($command)
        ;

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
            ->with('PHPUnit', 'continued')
        ;
        $actual = $this->runner->run($this->testFrameworkAdapter, '', [], false, $logger);

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
        yield 'coverage with a known version' => ['6.5.4', '6.5.4', false, 0];

        yield 'coverage with an unknown version' => [null, 'unknown', false, 0];

        yield 'coverage skipped' => ['6.5.4', '6.5.4', true, 0];

        yield 'unsuccessful command' => ['6.5.4', '6.5.4', false, 3];
    }
}
