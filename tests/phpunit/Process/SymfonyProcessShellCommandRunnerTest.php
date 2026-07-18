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
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
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
        $output = $this->runner->mustRun([
            'php',
            '-r',
            'fwrite(STDOUT, "stdout content"); fwrite(STDERR, "stderr content");',
        ]);

        $this->assertSame('stdout content', $output);
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

    public function test_it_runs_a_successful_command(): void
    {
        $command = [
            'php',
            '-r',
            'fwrite(STDOUT, "  stdout content  "); fwrite(STDERR, "  stderr content  ");',
        ];

        $result = $this->runner->run($command);

        $expected = CompletedProcessBuilder::withMinimalTestData()
            ->withCommand($command)
            ->withStdout('stdout content')
            ->withStderr('stderr content')
            ->build();

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
            ->build();

        $this->assertEquals($expected, $result);
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
}
