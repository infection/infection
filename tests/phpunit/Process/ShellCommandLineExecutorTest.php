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

use Infection\Process\CompletedProcess;
use Infection\Process\ShellCommandLineExecutor;
use function basename;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use function Safe\mkdir;
use function Safe\rmdir;
use Symfony\Component\Process\Exception\ProcessFailedException;
use function sys_get_temp_dir;
use function uniqid;

#[CoversClass(CompletedProcess::class)]
#[CoversClass(ShellCommandLineExecutor::class)]
#[Group('integration')]
final class ShellCommandLineExecutorTest extends TestCase
{
    private ShellCommandLineExecutor $executor;

    protected function setUp(): void
    {
        $this->executor = new ShellCommandLineExecutor();
    }

    /**
     * @param string[] $command
     */
    #[DataProvider('commandProvider')]
    public function test_it_executes_command_and_returns_trimmed_output(
        array $command,
        string $expectedOutput,
    ): void {
        $process = $this->executor->execute($command);

        $this->assertSame($expectedOutput, $process->stdout);
    }

    public static function commandProvider(): iterable
    {
        yield 'simple output' => [
            ['echo', 'test output'],
            'test output',
        ];

        yield 'output with leading whitespace' => [
            ['echo', '  whitespace'],
            'whitespace',
        ];

        yield 'output with trailing whitespace' => [
            ['echo', 'whitespace  '],
            'whitespace',
        ];

        yield 'output with both leading and trailing whitespace' => [
            ['echo', '  whitespace  '],
            'whitespace',
        ];

        yield 'empty output' => [
            ['php', '-r', ''],
            '',
        ];
    }

    public function test_it_does_not_include_stderr_in_output(): void
    {
        $process = $this->executor->execute([
            'php',
            '-r',
            'fwrite(STDOUT, "stdout content"); fwrite(STDERR, "stderr content");',
        ]);

        $this->assertSame('stdout content', $process->stdout);
        $this->assertSame('stderr content', $process->stderr);
        $this->assertSame(0, $process->exitCode);
        $this->assertTrue($process->isSuccessful());
    }

    public function test_it_returns_the_executed_command(): void
    {
        $command = ['php', '-r', 'echo "done";'];

        $process = $this->executor->execute($command);

        $this->assertSame($command, $process->command);
    }

    public function test_it_throws_exception_on_command_failure(): void
    {
        $this->expectException(ProcessFailedException::class);
        $this->expectExceptionMessageMatches('/stdout output.*stderr output/s');

        $this->executor->execute([
            'php',
            '-r',
            'fwrite(STDOUT, "stdout output"); fwrite(STDERR, "stderr output"); exit(1);',
        ]);
    }

    public function test_it_runs_command_failure_without_throwing(): void
    {
        $process = $this->executor->run([
            'php',
            '-r',
            'fwrite(STDOUT, "stdout output"); fwrite(STDERR, "stderr output"); exit(123);',
        ]);

        $this->assertSame(123, $process->exitCode);
        $this->assertSame('stdout output', $process->stdout);
        $this->assertSame('stderr output', $process->stderr);
        $this->assertFalse($process->isSuccessful());
    }

    public function test_it_does_not_provide_interactive_input(): void
    {
        $process = $this->executor->execute([
            'php',
            '-r',
            'echo fgets(STDIN) ?: "no input";',
        ]);

        $this->assertSame('no input', $process->stdout);
    }

    public function test_it_provides_process_options(): void
    {
        $cwd = sys_get_temp_dir() . '/infection-shell-command-line-executor-' . uniqid();
        mkdir($cwd);

        try {
            $process = $this->executor->execute(
                ['php', '-r', 'echo getenv("INFECTION_TEST_VALUE") . "|" . trim(fgets(STDIN)) . "|" . basename(getcwd());'],
                cwd: $cwd,
                env: ['INFECTION_TEST_VALUE' => 'env-value'],
                input: "input-value\n",
                timeout: null,
            );
        } finally {
            rmdir($cwd);
        }

        $this->assertSame('env-value|input-value|' . basename($cwd), $process->stdout);
    }

    public function test_it_invokes_the_callback(): void
    {
        $output = '';

        $this->executor->execute(
            ['php', '-r', 'echo "chunk";'],
            static function (string $type, string $buffer) use (&$output): void {
                $output .= $type . ':' . $buffer;
            },
        );

        $this->assertSame('out:chunk', $output);
    }
}
