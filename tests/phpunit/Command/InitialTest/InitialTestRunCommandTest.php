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

namespace Infection\Tests\Command\InitialTest;

use Infection\AbstractTestFramework\TestFrameworkAdapter;
use Infection\Command\InitialTest\InitialTestRunCommand;
use Infection\Console\Application;
use Infection\Container\Container;
use Infection\Framework\Str;
use Infection\Git\Git;
use Infection\Process\Runner\InitialTestsFailed;
use Infection\Process\Runner\InitialTestsRunner;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use function Safe\chdir;
use function Safe\getcwd;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Process\Process;

#[Group('integration')]
#[CoversClass(InitialTestRunCommand::class)]
final class InitialTestRunCommandTest extends TestCase
{
    private const FIXTURES_DIR = __DIR__ . '/Fixtures';

    private string $cwd = '';

    protected function setUp(): void
    {
        $this->cwd = getcwd();
        chdir(self::FIXTURES_DIR);
    }

    protected function tearDown(): void
    {
        chdir($this->cwd);
    }

    /**
     * @param array<string, string> $arguments
     * @param string[] $expectedInitialTestsPhpOptions
     */
    #[DataProvider('successfulCommandExecutionProvider')]
    public function test_it_executes_the_initial_tests(
        array $arguments,
        bool $successfulInitialTests,
        string $expectedTestFrameworkExtraOptions,
        array $expectedInitialTestsPhpOptions,
        bool $expectedSkipCoverage,
        string $expectedStdout,
        string $expectedStderr,
        string $expectedDisplay,
    ): void {
        $tester = $this->createCommandTester(
            $successfulInitialTests,
            $expectedTestFrameworkExtraOptions,
            $expectedInitialTestsPhpOptions,
            $expectedSkipCoverage,
        );

        [
            $actualStdout,
            $actualStderr,
            $actualDisplay,
        ] = $this->executeCommand($tester, $arguments);

        $this->assertSame($expectedStdout, $actualStdout);
        $this->assertSame($expectedStderr, $actualStderr);
        $this->assertSame($expectedDisplay, $actualDisplay);
    }

    public static function successfulCommandExecutionProvider(): iterable
    {
        yield 'default parameters with successful tests' => [
            'arguments' => [],
            'successfulInitialTests' => true,
            'expectedTestFrameworkExtraOptions' => '',
            'expectedInitialTestsPhpOptions' => [''],
            'expectedSkipCoverage' => false,
            'expectedStdout' => <<<STDOUT
                Command executed:


                 [OK] Initial test run successfully executed.


                STDOUT,
            'expectedStderr' => <<<STDERR

                STDERR,
            'expectedDisplay' => <<<DISPLAY
                Command executed:


                 [OK] Initial test run successfully executed.


                DISPLAY,
        ];
    }

    /**
     * @param array<string, string> $arguments
     * @param string[] $expectedInitialTestsPhpOptions
     */
    #[DataProvider('failingCommandExecutionProvider')]
    public function test_it_executes_the_initial_tests_with_failing_tests(
        array $arguments,
        bool $successfulInitialTests,
        string $expectedTestFrameworkExtraOptions,
        array $expectedInitialTestsPhpOptions,
        bool $expectedSkipCoverage,
        InitialTestsFailed $expected,
    ): void {
        $tester = $this->createCommandTester(
            $successfulInitialTests,
            $expectedTestFrameworkExtraOptions,
            $expectedInitialTestsPhpOptions,
            $expectedSkipCoverage,
        );

        $this->expectExceptionObject($expected);

        $tester->execute(
            $arguments,
            [
                'verbosity' => OutputInterface::VERBOSITY_VERBOSE,
                'capture_stderr_separately' => true,
            ],
        );
    }

    public static function failingCommandExecutionProvider(): iterable
    {
        yield 'default parameters with failing tests' => [
            'arguments' => [],
            'successfulInitialTests' => false,
            'expectedTestFrameworkExtraOptions' => '',
            'expectedInitialTestsPhpOptions' => [''],
            'expectedSkipCoverage' => false,
            'expected' => new InitialTestsFailed(
                <<<'MESSAGE'
                    Project tests must be in a passing state before running Infection.

                    DemoTestFramework reported an exit code of 123.
                    Refer to the DemoTestFramework's output below:
                    STDOUT:
                    <processOutput>
                    STDERR:
                    <processErrorOutput>
                    MESSAGE,
            ),
        ];
    }

    /**
     * @param string[] $expectedInitialTestsPhpOptions
     */
    private function createCommandTester(
        bool $successfulInitialTests,
        string $expectedTestFrameworkExtraOptions,
        array $expectedInitialTestsPhpOptions,
        bool $expectedSkipCoverage,
    ): CommandTester {
        $gitMock = $this->createMock(Git::class);
        $gitMock
            ->method('getBaseReference')
            ->willReturn('<refinedGitReference>');

        $testFrameworkAdapterMock = $this->createMock(TestFrameworkAdapter::class);
        $testFrameworkAdapterMock
            ->method('getName')
            ->willReturn('DemoTestFramework');

        $initialTestsProcessMock = $this->createMock(Process::class);
        $initialTestsProcessMock
            ->method('getCommandLine')
            ->willReturn('test-framework initialConfig');
        $initialTestsProcessMock
            ->method('isSuccessful')
            ->willReturn($successfulInitialTests);
        $initialTestsProcessMock
            ->method('getExitCode')
            ->willReturn(123);
        $initialTestsProcessMock
            ->method('getOutput')
            ->willReturn('<processOutput>');
        $initialTestsProcessMock
            ->method('getErrorOutput')
            ->willReturn('<processErrorOutput>');

        $initialTestsRunnerMock = $this->createMock(InitialTestsRunner::class);
        $initialTestsRunnerMock
            ->method('run')
            ->with(
                $expectedTestFrameworkExtraOptions,
                $expectedInitialTestsPhpOptions,
                $expectedSkipCoverage,
            )
            ->willReturn($initialTestsProcessMock);

        $container = Container::create();
        // Cannot use cloneWithService here: https://github.com/sanmai/di-container/issues/53
        $container->set(Git::class, static fn () => $gitMock);
        $container->set(TestFrameworkAdapter::class, static fn () => $testFrameworkAdapterMock);
        $container->set(InitialTestsRunner::class, static fn () => $initialTestsRunnerMock);

        $application = new Application($container);

        $command = new InitialTestRunCommand();
        $command->setApplication($application);

        return new CommandTester($command);
    }

    /**
     * @param array<string, string> $arguments
     *
     * @return array{string, string, string}
     */
    private function executeCommand(
        CommandTester $commandTester,
        array $arguments,
    ): array {
        $commandTester->execute(
            $arguments,
            [
                'verbosity' => OutputInterface::VERBOSITY_VERBOSE,
                'capture_stderr_separately' => true,
            ],
        );

        $stdout = Str::rTrimLines($commandTester->getDisplay(normalize: true));
        $stderr = Str::rTrimLines($commandTester->getErrorOutput(normalize: true));

        $commandTester->execute(
            $arguments,
            [
                'verbosity' => OutputInterface::VERBOSITY_VERBOSE,
            ],
        );

        $commandTester->assertCommandIsSuccessful();
        $display = Str::rTrimLines($commandTester->getDisplay(normalize: true));

        return [
            $stdout,
            $stderr,
            $display,
        ];
    }
}
