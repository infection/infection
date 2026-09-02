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

namespace Infection\Tests\Process;

use Infection\Process\SymfonyProcessShellCommandRunner;
use Infection\Tests\TestFramework\Contracts\CompletedProcessBuilder;
use const PHP_BINARY;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\WithEnvironmentVariable;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;

#[CoversClass(SymfonyProcessShellCommandRunner::class)]
#[Group('integration')]
final class SymfonyProcessShellCommandRunnerTest extends TestCase
{
    private SymfonyProcessShellCommandRunner $runner;

    protected function setUp(): void
    {
        $this->runner = new SymfonyProcessShellCommandRunner();
    }

    /**
     * @param string[] $command
     */
    #[DataProvider('commandProvider')]
    public function test_it_executes_command_and_returns_trimmed_output(
        array $command,
        string $expectedOutput,
    ): void {
        $output = $this->runner->mustRun($command);

        $this->assertSame($expectedOutput, $output);
    }

    public static function commandProvider(): iterable
    {
        yield 'simple output' => [
            [PHP_BINARY, '-r', 'echo "test output";'],
            'test output',
        ];

        yield 'output with leading whitespace' => [
            [PHP_BINARY, '-r', 'echo "  whitespace";'],
            'whitespace',
        ];

        yield 'output with trailing whitespace' => [
            [PHP_BINARY, '-r', 'echo "whitespace  ";'],
            'whitespace',
        ];

        yield 'output with both leading and trailing whitespace' => [
            [PHP_BINARY, '-r', 'echo "  whitespace  ";'],
            'whitespace',
        ];

        yield 'empty output' => [
            [PHP_BINARY, '-r', ''],
            '',
        ];
    }

    public function test_it_does_not_include_stderr_in_output(): void
    {
        $output = $this->runner->mustRun([
            'php',
            '-r',
            'fwrite(STDOUT, "stdout content"); fwrite(STDERR, "stderr content");',
        ]);

        $this->assertSame('stdout content', $output);
    }

    public function test_it_preserves_an_explicit_shell_verbosity(): void
    {
        $output = $this->runner->mustRun(
            [PHP_BINARY, '-r', 'echo getenv("SHELL_VERBOSITY");'],
            env: ['SHELL_VERBOSITY' => '2'],
        );

        $this->assertSame('2', $output);
    }

    #[WithEnvironmentVariable('SHELL_VERBOSITY', '2')]
    public function test_it_disables_shell_verbosity_by_default(): void
    {
        $output = $this->runner->mustRun([
            PHP_BINARY,
            '-r',
            'echo getenv("SHELL_VERBOSITY");',
        ]);

        $this->assertSame('0', $output);
    }

    public function test_it_throws_exception_on_command_failure(): void
    {
        $this->expectException(ProcessFailedException::class);
        $this->expectExceptionMessageMatches('/stdout output.*stderr output/s');

        $this->runner->mustRun([
            'php',
            '-r',
            'fwrite(STDOUT, "stdout output"); fwrite(STDERR, "stderr output"); exit(1);',
        ]);
    }

    public function test_it_does_not_provide_interactive_input(): void
    {
        $output = $this->runner->mustRun([
            'php',
            '-r',
            'echo fgets(STDIN) ?: "no input";',
        ]);

        $this->assertSame('no input', $output);
    }

    public function test_it_runs_a_command_with_the_given_execution_context(): void
    {
        $callbackOutput = '';
        $callback = static function (string $type, string $buffer) use (&$callbackOutput): void {
            if ($type === Process::OUT) {
                $callbackOutput .= $buffer;
            }
        };

        $output = $this->runner->mustRun(
            [
                'php',
                '-r',
                'echo getcwd(), "|", getenv("INFECTION_TEST_ENV"), "|", stream_get_contents(STDIN);',
            ],
            callback: $callback,
            cwd: __DIR__,
            env: ['INFECTION_TEST_ENV' => 'environment'],
            input: 'input',
        );

        $expected = __DIR__ . '|environment|input';

        $this->assertSame($expected, $output);
        $this->assertSame($expected, $callbackOutput);
    }

    public function test_it_streams_stdout_and_stderr_to_the_callback(): void
    {
        $output = [
            Process::OUT => '',
            Process::ERR => '',
        ];
        $callback = static function (string $type, string $buffer) use (&$output): void {
            $output[$type] .= $buffer;
        };

        $this->runner->mustRun(
            [
                'php',
                '-r',
                'fwrite(STDOUT, "stdout content"); fwrite(STDERR, "stderr content");',
            ],
            $callback,
        );

        $expected = [
            Process::OUT => 'stdout content',
            Process::ERR => 'stderr content',
        ];

        $this->assertSame($expected, $output);
    }

    public function test_it_runs_a_successful_command(): void
    {
        $command = [
            'php',
            '-r',
            'fwrite(STDOUT, "  stdout content  "); fwrite(STDERR, "  stderr content  ");',
        ];

        $expected = CompletedProcessBuilder::withMinimalTestData()
            ->withCommand($command)
            ->withStdout('stdout content')
            ->withStderr('stderr content')
            ->build()
        ;

        $actual = $this->runner->run($command);

        $this->assertEquals($expected, $actual);
    }

    public function test_it_returns_a_process_run_with_the_given_execution_context(): void
    {
        $command = [
            'php',
            '-r',
            'echo getcwd(), "|", getenv("INFECTION_TEST_ENV"), "|", stream_get_contents(STDIN);',
        ];

        $result = $this->runner->run(
            $command,
            cwd: __DIR__,
            env: ['INFECTION_TEST_ENV' => 'environment'],
            input: 'input',
        );

        $expected = CompletedProcessBuilder::withMinimalTestData()
            ->withCommand($command)
            ->withStdout(__DIR__ . '|environment|input')
            ->build()
        ;

        $this->assertEquals($expected, $result);
    }

    public function test_it_returns_an_unsuccessful_command_without_throwing(): void
    {
        $command = [
            'php',
            '-r',
            'fwrite(STDOUT, "stdout content"); fwrite(STDERR, "stderr content"); exit(7);',
        ];

        $result = $this->runner->run($command);

        $expected = CompletedProcessBuilder::withMinimalTestData()
            ->withCommand($command)
            ->withExitCode(7)
            ->withStdout('stdout content')
            ->withStderr('stderr content')
            ->build()
        ;

        $this->assertEquals($expected, $result);
    }

    public function test_it_returns_empty_trimmed_output(): void
    {
        $command = [PHP_BINARY, '-r', 'echo "  ";'];

        $expected = CompletedProcessBuilder::withMinimalTestData()
            ->withCommand($command)
            ->build()
        ;

        $this->assertEquals($expected, $this->runner->run($command));
    }

    public function test_it_streams_output_to_the_callback(): void
    {
        $output = [
            Process::OUT => '',
            Process::ERR => '',
        ];

        $command = [
            'php',
            '-r',
            'fwrite(STDOUT, "stdout content"); fwrite(STDERR, "stderr content");',
        ];
        $callback = static function (string $type, string $buffer) use (&$output): void {
            $output[$type] .= $buffer;
        };

        $expected = [
            Process::OUT => 'stdout content',
            Process::ERR => 'stderr content',
        ];

        $this->runner->run(
            $command,
            $callback,
        );

        $this->assertSame($expected, $output);
    }

    public function test_it_uses_the_given_timeout(): void
    {
        $this->expectException(ProcessTimedOutException::class);

        $this->runner->run(
            ['php', '-r', 'sleep(1);'],
            timeout: 0.01,
        );
    }

    public function test_it_uses_the_given_idle_timeout(): void
    {
        $this->expectException(ProcessTimedOutException::class);

        $this->runner->run(
            ['php', '-r', 'echo "started"; sleep(1);'],
            timeout: null,
            idleTimeout: 0.01,
        );
    }

    /**
     * @param array{timeout?: ?float, idleTimeout?: ?float} $arguments
     */
    #[DataProvider('mustRunTimeoutProvider')]
    public function test_it_uses_the_given_timeout_when_the_command_must_run(array $arguments): void
    {
        $this->expectException(ProcessTimedOutException::class);

        $this->runner->mustRun(
            ['php', '-r', 'echo "started"; sleep(1);'],
            ...$arguments,
        );
    }

    public static function mustRunTimeoutProvider(): iterable
    {
        yield 'timeout' => [
            ['timeout' => 0.01],
        ];

        yield 'idle timeout' => [
            [
                'timeout' => null,
                'idleTimeout' => 0.01,
            ],
        ];
    }
}
